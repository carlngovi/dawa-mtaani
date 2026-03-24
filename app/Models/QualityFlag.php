<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class QualityFlag extends Model
{
    use Auditable;

    protected $fillable = [
        'ulid', 'facility_id', 'product_id', 'batch_reference',
        'flag_type', 'notes', 'photo_path', 'status',
        'reviewed_by', 'review_notes',
        'supplier_notified_at', 'batch_alert_sent_at',
        'batch_alert_facility_count',
    ];

    protected $casts = [
        'supplier_notified_at' => 'datetime',
        'batch_alert_sent_at'  => 'datetime',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function batchAlerts()
    {
        return $this->hasMany(QualityFlagBatchAlert::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'OPEN');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'CONFIRMED');
    }

    public function isCounterfeit(): bool
    {
        return $this->flag_type === 'SUSPECTED_COUNTERFEIT';
    }
}
