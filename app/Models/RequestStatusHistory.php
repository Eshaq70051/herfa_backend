<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يسجل تغيّرات حالة الطلب وتتابعها الزمني.
 */
class RequestStatusHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'request_status_history';

    protected $guarded = ['id'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function oldStatus()
    {
        return $this->belongsTo(RequestStatus::class, 'old_status_id');
    }

    public function newStatus()
    {
        return $this->belongsTo(RequestStatus::class, 'new_status_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
