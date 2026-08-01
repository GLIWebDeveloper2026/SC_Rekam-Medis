<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Dispensing extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = ['prescription_id', 'patient_id', 'dispensed_by', 'recipient_name', 'status', 'dispensed_at', 'integrity_hash'];
}
