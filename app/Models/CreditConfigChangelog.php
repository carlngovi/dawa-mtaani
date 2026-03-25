<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditConfigChangelog extends Model
{
    protected $table = 'credit_config_changelog';

    const UPDATED_AT = null;

    protected $fillable = [
        'changed_by', 'model_type', 'model_id',
        'field_name', 'value_before', 'value_after',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
