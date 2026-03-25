<?php
namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTrancheParty extends Model
{
    use Auditable;

    protected $fillable = [
        'tranche_id', 'party_name', 'party_type', 'party_contact',
        'banking_party_binding', 'risk_percentage', 'return_percentage', 'is_active',
    ];

    protected $casts = [
        'risk_percentage'   => 'decimal:2',
        'return_percentage' => 'decimal:2',
        'is_active'         => 'boolean',
    ];

    protected $hidden = ['party_contact'];

    public function tranche(): BelongsTo
    {
        return $this->belongsTo(CreditTranche::class, 'tranche_id');
    }
}
