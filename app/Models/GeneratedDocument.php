<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GeneratedDocument extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'document_type', 'patient_id', 'source_request_id', 'storage_key', 'document_number',
        'checksum', 'watermark_text', 'generated_by', 'generated_at', 'expires_at',
    ];
}
