<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditTrancheParty extends Model
{
    protected $fillable = [
        'tranche_id', 'party_name', 'party_type', 'banking_party_binding',
        'risk_percentage', 'return_percentage', 'is_active',
    ];

    protected $casts = [
        'risk_percentage'   => 'decimal:2',
        'return_percentage' => 'decimal:2',
        'is_active'         => 'boolean',
    ];

    public function tranche()
    {
        return $this->belongsTo(CreditTranche::class, 'tranche_id');
    }
}
