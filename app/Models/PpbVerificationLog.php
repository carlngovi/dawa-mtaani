<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpbVerificationLog extends Model
{
    protected $fillable = [
        'facility_id', 'checked_at', 'licence_status_returned',
        'response_json', 'triggered_by',
    ];

    protected $casts = [
        'response_json' => 'array',
        'checked_at'    => 'datetime',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
