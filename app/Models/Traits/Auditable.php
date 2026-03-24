<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Throwable;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            self::writeAuditLog('created', $model, null, $model->toArray());
        });

        static::updated(function ($model) {
            self::writeAuditLog('updated', $model, $model->getOriginal(), $model->getChanges());
        });

        static::deleted(function ($model) {
            self::writeAuditLog('deleted', $model, $model->toArray(), null);
        });
    }

    private static function writeAuditLog(
        string $action,
        $model,
        ?array $before,
        ?array $after
    ): void {
        try {
            $sensitiveKeys = ['password', 'remember_token', 'api_key', 'secret', 'token'];

            if ($before) {
                $before = array_diff_key($before, array_flip($sensitiveKeys));
            }

            if ($after) {
                $after = array_diff_key($after, array_flip($sensitiveKeys));
            }

            DB::table('audit_logs')->insert([
                'facility_id' => $model->getAttribute('facility_id'),
                'user_id' => Auth::id(),
                'action' => $action,
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'payload_before' => $before ? json_encode($before) : null,
                'payload_after' => $after ? json_encode($after) : null,
                'ip_address' => Request::ip() ?? '0.0.0.0',
                'user_agent' => Request::userAgent(),
                'created_at' => now('UTC'),
            ]);
        } catch (Throwable $e) {
            Log::error('Auditable: failed to write audit log', [
                'model' => get_class($model),
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
