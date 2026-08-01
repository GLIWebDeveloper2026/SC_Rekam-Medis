<?php

namespace App\Actions\Documents;

use App\Models\ClinicalEntry;
use App\Models\GeneratedDocument;
use App\Models\MedicalRecordCopyRequest;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GenerateMedicalRecordCopy
{
    public function execute(MedicalRecordCopyRequest $copyRequest, User $user): GeneratedDocument
    {
        if ($copyRequest->status !== 'approved' || $copyRequest->identity_verified_by === null || $copyRequest->approved_by === null) {
            throw ValidationException::withMessages(['status' => 'Salinan hanya dapat dibuat setelah verifikasi identitas dan persetujuan.']);
        }

        return DB::transaction(function () use ($copyRequest, $user): GeneratedDocument {
            $copyRequest->load('patient');
            $entries = ClinicalEntry::query()
                ->with('diagnoses')
                ->where('patient_id', $copyRequest->patient_id)
                ->whereBetween('clinical_time', [
                    $copyRequest->requested_period_start->startOfDay(),
                    $copyRequest->requested_period_end->endOfDay(),
                ])
                ->oldest('clinical_time')
                ->get();
            $documentNumber = 'SRM-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
            $watermark = 'SALINAN TERKONTROL · '.$documentNumber.' · '.$copyRequest->purpose;
            $html = view('record-copies.pdf', [
                'copyRequest' => $copyRequest,
                'entries' => $entries,
                'documentNumber' => $documentNumber,
                'watermark' => $watermark,
            ])->render();

            $options = new Options;
            $options->set('isRemoteEnabled', false);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4');
            $dompdf->render();
            $pdf = $dompdf->output();
            $storageKey = 'medical-record-copies/'.now()->format('Y/m').'/'.$documentNumber.'.pdf';
            Storage::disk('private')->put($storageKey, $pdf);

            $document = GeneratedDocument::query()->create([
                'document_type' => 'medical_record_copy',
                'patient_id' => $copyRequest->patient_id,
                'source_request_id' => $copyRequest->id,
                'storage_key' => $storageKey,
                'document_number' => $documentNumber,
                'checksum' => hash('sha256', $pdf),
                'watermark_text' => $watermark,
                'generated_by' => $user->id,
                'generated_at' => now(),
                'expires_at' => now()->addDays(30),
            ]);
            $copyRequest->update(['status' => 'prepared']);

            DB::table('document_access_events')->insert([
                'id' => (string) Str::uuid(),
                'document_id' => $document->id,
                'event_type' => 'generated',
                'performed_by' => $user->id,
                'recipient' => null,
                'reason' => $copyRequest->purpose,
                'created_at' => now(),
            ]);

            return $document;
        }, attempts: 5);
    }
}
