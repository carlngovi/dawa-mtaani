<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpotterActivationCode extends Model
{
    use HasUlids, SoftDeletes, Auditable;

    protected $table = 'spotter_activation_codes';

    protected $fillable = [
        'spotter_user_id',
        'code',
        'consumed_at',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'consumed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function spotter()
    {
        return $this->belongsTo(User::class, 'spotter_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeUnused($query)
    {
        return $query->whereNull('consumed_at')->where('expires_at', '>', now());
    }

    public function isValid(): bool
    {
        return $this->consumed_at === null && $this->expires_at->isFuture();
    }
}
