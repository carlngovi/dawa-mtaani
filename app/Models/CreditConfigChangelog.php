<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditConfigChangelog extends Model
{
    public $timestamps = false;

    protected $table = 'credit_config_changelog';

    protected $fillable = [
        'entity_type', 'entity_id', 'field_name',
        'old_value', 'new_value', 'changed_by', 'changed_at', 'reason',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
