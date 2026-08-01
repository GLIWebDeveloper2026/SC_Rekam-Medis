<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = ['medicine_batch_id', 'movement_type', 'quantity', 'reference_type', 'reference_id', 'performed_by', 'reason', 'created_at', 'integrity_hash'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Stock movement bersifat append-only.'));
        static::deleting(fn () => throw new \LogicException('Stock movement bersifat append-only.'));
    }
}
