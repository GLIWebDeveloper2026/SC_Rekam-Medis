<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasUuids;

    protected $fillable = ['code', 'generic_name', 'brand_name', 'dosage_form', 'strength', 'unit', 'is_compound_component', 'status', 'minimum_stock'];

    /** @return HasMany<MedicineBatch, $this> */
    public function batches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class);
    }
}
