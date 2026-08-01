<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineBatch extends Model
{
    use HasUuids;

    protected $fillable = ['medicine_id', 'batch_number', 'expiry_date', 'received_quantity', 'status'];

    /** @return BelongsTo<Medicine, $this> */
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    /** @return HasMany<StockMovement, $this> */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function availableStock(): float
    {
        return (float) $this->stockMovements()->sum('quantity');
    }

    protected function casts(): array
    {
        return ['expiry_date' => 'date', 'received_quantity' => 'decimal:3'];
    }
}
