<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KenyaWard extends Model
{
    protected $fillable = ['ward_code', 'ward_name', 'kenya_constituency_id', 'kenya_county_id'];

    public function constituency(): BelongsTo
    {
        return $this->belongsTo(KenyaConstituency::class, 'kenya_constituency_id');
    }

    public function county(): BelongsTo
    {
        return $this->belongsTo(KenyaCounty::class, 'kenya_county_id');
    }
}
