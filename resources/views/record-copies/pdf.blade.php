<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <title>{{ $documentNumber }}</title>
    </head>
    <body>
        <h1>Klinik Pratama Sehat Bersama</h1>
        <h2>Salinan Rekam Medis Terkontrol</h2>
        <p>{{ $watermark }}</p>
        <hr>
        <p><strong>Nomor dokumen:</strong> {{ $documentNumber }}</p>
        <p><strong>Pasien:</strong> {{ $copyRequest->patient->full_name }} ({{ $copyRequest->patient->medical_record_number }})</p>
        <p><strong>Periode:</strong> {{ $copyRequest->requested_period_start->format('d-m-Y') }} s.d. {{ $copyRequest->requested_period_end->format('d-m-Y') }}</p>
        <p><strong>Tujuan:</strong> {{ $copyRequest->purpose }}</p>
        <p><strong>Ruang lingkup:</strong> {{ $copyRequest->requested_scope }}</p>
        <hr>

        @forelse ($entries as $entry)
            <section>
                <h3>{{ $entry->clinical_time->format('d-m-Y H:i:s') }} WIB · {{ $entry->entry_type }}</h3>
                <p>{{ $entry->content_json['text'] }}</p>
                @foreach ($entry->diagnoses as $diagnosis)
                    <p>Diagnosis: {{ $diagnosis->diagnosis_code }} · {{ $diagnosis->diagnosis_name }}</p>
                @endforeach
                @if ($entry->correction_reason)
                    <p>Addendum/koreksi: {{ $entry->correction_reason }}</p>
                @endif
            </section>
            <hr>
        @empty
            <p>Tidak ada catatan klinis final pada ruang lingkup yang disetujui.</p>
        @endforelse

        <p>Dokumen dibuat pada {{ now()->format('d-m-Y H:i:s') }} WIB.</p>
    </body>
</html>
