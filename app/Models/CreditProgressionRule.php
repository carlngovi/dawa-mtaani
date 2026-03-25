<?php
namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class CreditProgressionRule extends Model
{
    use Auditable;

    protected $fillable = [
        'label', 'max_days_to_qualify', 'progression_rate_pct',
        'is_suspension_trigger', 'sort_order',
    ];

    protected $casts = [
        'progression_rate_pct'  => 'decimal:2',
        'is_suspension_trigger' => 'boolean',
    ];
}
