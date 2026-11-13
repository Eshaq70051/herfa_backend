<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يمثل طلب عميل لتنفيذ خدمة، ويربطه بالخدمة والعنوان والحالة والتكاليف.
 */
class ServiceRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'preferred_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function address()
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    public function status()
    {
        return $this->belongsTo(RequestStatus::class, 'status_id');
    }

    public function cancellationReason()
    {
        return $this->belongsTo(CancellationReason::class, 'cancellation_reason_id');
    }

    public function details()
    {
        return $this->hasMany(RequestDetail::class, 'request_id');
    }

    public function assignments()
    {
        return $this->hasMany(RequestAssignment::class, 'request_id');
    }

    public function currentAssignment()
    {
        return $this->hasOne(RequestAssignment::class, 'request_id')->latestOfMany();
    }

    public function history()
    {
        return $this->hasMany(RequestStatusHistory::class, 'request_id');
    }

    public function attachments()
    {
        return $this->hasMany(RequestAttachment::class, 'request_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'request_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'request_id');
    }

    public function fees()
    {
        return $this->hasMany(RequestFee::class, 'request_id');
    }
}
