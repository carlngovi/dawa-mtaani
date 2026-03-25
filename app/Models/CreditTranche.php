<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditTranche extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ulid', 'name', 'entry_amount', 'ceiling_amount', 'is_fixed',
        'approval_pathway', 'product_restriction_scope',
        'effective_from', 'effective_to', 'is_active', 'created_by',
    ];

    protected $casts = [
        'entry_amount'               => 'decimal:2',
        'ceiling_amount'             => 'decimal:2',
        'is_fixed'                   => 'boolean',
        'is_active'                  => 'boolean',
        'product_restriction_scope'  => 'array',
        'effective_from'             => 'date',
        'effective_to'               => 'date',
    ];

    public function parties()
    {
        return $this->hasMany(CreditTrancheParty::class, 'tranche_id');
    }

    public function activeParties()
    {
        return $this->parties()->where('is_active', true);
    }

    public function tiers()
    {
        return $this->hasMany(CreditTier::class, 'tranche_id')->orderBy('sort_order');
    }

    public function activeTiers()
    {
        return $this->tiers()->where('is_active', true);
    }
}
