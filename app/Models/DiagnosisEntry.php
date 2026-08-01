<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DiagnosisEntry extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = ['clinical_entry_id', 'diagnosis_code', 'diagnosis_name', 'diagnosis_type', 'is_primary'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }
}
