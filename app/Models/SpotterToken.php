<?php

/**
 * @deprecated-spotter
 * This file has been migrated to dawa-spotter/.
 * It remains here temporarily to preserve existing admin panel routes.
 * Remove after dawa-spotter is confirmed live.
 */

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class SpotterToken extends Model
{
    use HasUlids, Auditable;

    protected $table = 'spotter_tokens';

    protected $fillable = [
        'spotter_user_id',
        'token_hash',
        'refresh_token_hash',
        'device_fingerprint',
        'county',
        'ward',
        'sales_rep_name',
        'expires_at',
        'refresh_expires_at',
        'revoked_at',
        'last_used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'refresh_expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function spotter()
    {
        return $this->belongsTo(User::class, 'spotter_user_id');
    }

    public function scopeValid($query)
    {
        return $query->whereNull('revoked_at')->where('expires_at', '>', now());
    }

    public static function findByToken(string $rawToken): ?static
    {
        $hash = hash('sha256', $rawToken);

        return static::where('token_hash', $hash)->valid()->first();
    }

    public function isRefreshValid(): bool
    {
        return $this->refresh_expires_at !== null
            && $this->refresh_expires_at->isFuture()
            && $this->revoked_at === null;
    }
}
