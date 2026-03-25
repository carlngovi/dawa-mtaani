<?php
namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditTranche extends Model
{
    use Auditable;

    protected $fillable = [
        'ulid', 'name', 'entry_amount', 'ceiling_amount', 'is_fixed',
        'approval_pathway', 'product_restriction_scope', 'is_active',
        'effective_from', 'created_by',
    ];

    protected $casts = [
        'product_restriction_scope' => 'array',
        'is_fixed'    => 'boolean',
        'is_active'   => 'boolean',
        'entry_amount'   => 'decimal:2',
        'ceiling_amount' => 'decimal:2',
        'effective_from' => 'date',
    ];

    public function parties(): HasMany
    {
        return $this->hasMany(CreditTrancheParty::class, 'tranche_id');
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(CreditTier::class, 'tranche_id')->orderBy('sort_order');
    }

    public function activeTiers(): HasMany
    {
        return $this->tiers()->where('is_active', true);
    }

    public function activeParties(): HasMany
    {
        return $this->parties()->where('is_active', true);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(FacilityTrancheBalance::class, 'tranche_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CreditEvent::class, 'tranche_id');
    }
}
