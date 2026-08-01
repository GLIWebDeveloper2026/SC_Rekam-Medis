<?php

namespace App\Http\Controllers;

use App\Models\ClinicalDraft;
use App\Models\Encounter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicalWorkspaceController extends Controller
{
    public function __invoke(Request $request): View
    {
        $encounters = Encounter::query()
            ->with(['visit.patient'])
            ->when(! $request->user()->hasRole('owner'), fn ($query) => $query->where('responsible_provider_id', $request->user()->id))
            ->whereIn('status', ['planned', 'active'])
            ->latest('started_at')
            ->get();
        $drafts = ClinicalDraft::query()->where('author_id', $request->user()->id)->where('status', 'active')->get()->keyBy('encounter_id');

        return view('clinical.workspace', compact('encounters', 'drafts'));
    }
}
