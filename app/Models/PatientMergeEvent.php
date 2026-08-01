<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PatientMergeEvent extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'merge_case_id', 'canonical_patient_id', 'source_patient_id', 'event_type',
        'reason', 'performed_by', 'approved_by', 'created_at',
    ];
}
