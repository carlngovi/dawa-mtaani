<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditProgressionRule extends Model
{
    protected $fillable = [
        'label', 'max_days_to_qualify', 'progression_rate_pct',
        'is_suspension_trigger', 'sort_order',
    ];

    protected $casts = [
        'progression_rate_pct'  => 'decimal:2',
        'is_suspension_trigger' => 'boolean',
    ];
}
