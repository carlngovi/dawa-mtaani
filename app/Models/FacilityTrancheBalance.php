<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityTrancheBalance extends Model
{
    protected $fillable = [
        'facility_id', 'tranche_id', 'current_balance', 'entry_balance',
        'last_progression_at', 'last_repayment_at',
    ];

    protected $casts = [
        'current_balance'    => 'decimal:2',
        'entry_balance'      => 'decimal:2',
        'last_progression_at' => 'datetime',
        'last_repayment_at'   => 'datetime',
    ];

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function tranche(): BelongsTo
    {
        return $this->belongsTo(CreditTranche::class, 'tranche_id');
    }

    public function utilisationPct(): ?float
    {
        $ceiling = $this->tranche?->ceiling_amount;
        if (! $ceiling || $ceiling == 0) {
            return null;
        }
        return round(($this->current_balance / $ceiling) * 100, 2);
    }
}
