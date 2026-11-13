<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يسجل حركة مالية مرتبطة بالمستخدم أو بطلب خدمة.
 */
class Transaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected $appends = ['receipt_image_url'];

    public function getReceiptImageUrlAttribute()
    {
        if ($this->receipt_image) {
            return str_starts_with($this->receipt_image, 'http') ? $this->receipt_image : asset($this->receipt_image);
        }

        return null;
    }

    public function request()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
