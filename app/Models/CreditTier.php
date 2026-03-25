<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditTier extends Model
{
    protected $fillable = [
        'ulid', 'tranche_id', 'name', 'product_scope_description',
        'unlock_threshold_pct', 'allocation_pct',
        'approval_required', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'unlock_threshold_pct' => 'decimal:2',
        'allocation_pct'       => 'decimal:2',
        'approval_required'    => 'boolean',
        'is_active'            => 'boolean',
    ];

    public function tranche()
    {
        return $this->belongsTo(CreditTranche::class, 'tranche_id');
    }
}
