<?php
namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditTier extends Model
{
    use Auditable;

    protected $fillable = [
        'ulid', 'tranche_id', 'name', 'product_scope_description',
        'product_scope_filter', 'unlock_threshold_pct', 'allocation_pct',
        'approval_required', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'product_scope_filter'  => 'array',
        'unlock_threshold_pct'  => 'decimal:2',
        'allocation_pct'        => 'decimal:2',
        'approval_required'     => 'boolean',
        'is_active'             => 'boolean',
    ];

    public function tranche(): BelongsTo
    {
        return $this->belongsTo(CreditTranche::class, 'tranche_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CreditEvent::class, 'tier_id');
    }
}
