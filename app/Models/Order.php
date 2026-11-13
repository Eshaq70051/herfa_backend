<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يوفر واجهة متوافقة للطلبات المخزنة في جدول طلبات الخدمات (service_requests).
 */
class Order extends Model
{
    use HasFactory;

    protected $table = 'service_requests';

    protected $guarded = ['id'];

    protected $appends = [
        'voice_note_url',
        'order_number',
        'client_id',
        'address_text',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'preferred_date' => 'date',
        'scheduled_at' => 'datetime',
        'warranty_until' => 'datetime',
        'is_urgent' => 'boolean',
        'inspection_fee' => 'float',
        'labor_fee' => 'float',
        'materials_fee' => 'float',
        'discount' => 'float',
        'total_amount' => 'float',
        'platform_commission' => 'float',
        'craftsman_earnings' => 'float',
    ];

    /**
     * توليد رقم طلب فريد بصيغة متسلسلة.
     */
    public static function generateOrderNumber(): string
    {
        return 'HRF-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    // Accessors & Mutators for seamless dual-compatibility
    public function getOrderNumberAttribute(): string
    {
        return $this->attributes['request_number'] ?? ('HRF-' . $this->id);
    }

    public function setOrderNumberAttribute($value): void
    {
        $this->attributes['request_number'] = $value;
    }

    public function getClientIdAttribute(): ?int
    {
        return $this->attributes['customer_id'] ?? null;
    }

    public function setClientIdAttribute($value): void
    {
        $this->attributes['customer_id'] = $value;
    }

    public function getAddressTextAttribute(): string
    {
        return $this->attributes['address_details'] ?? '';
    }

    public function setAddressTextAttribute($value): void
    {
        $this->attributes['address_details'] = $value;
    }

    public function getVoiceNoteUrlAttribute(): ?string
    {
        $vn = $this->attributes['voice_note'] ?? null;
        if (empty($vn)) {
            return null;
        }
        if (str_starts_with($vn, 'http')) {
            return $vn;
        }

        return asset($vn);
    }

    public function getStatusAttribute(): string
    {
        // إذا كان حقل status النصي موجوداً في الجدول مباشرة يتم استخدامه أولاً
        $directStatus = $this->attributes['status'] ?? null;
        if ($directStatus && !is_numeric($directStatus)) {
            return $directStatus;
        }

        // الرجوع إلى علاقة جدول حالات الطلبات كاحتياطي
        $name = $this->requestStatus?->name ?? 'pending';
        if ($name === 'pending') {
            return 'pending_craftsman';
        }
        return $name;
    }

    public function setStatusAttribute($value): void
    {
        if (is_numeric($value)) {
            $this->attributes['status_id'] = (int) $value;
        } else {
            $this->attributes['status'] = $value;
        }
    }

    // Relationships
    public function client()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function craftsman()
    {
        return $this->belongsTo(User::class, 'craftsman_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function requestStatus()
    {
        return $this->belongsTo(RequestStatus::class, 'status_id');
    }

    public function assignments()
    {
        return $this->hasMany(RequestAssignment::class, 'request_id');
    }

    public function currentAssignment()
    {
        return $this->hasOne(RequestAssignment::class, 'request_id')->latestOfMany();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'request_id');
    }

    public function attachments()
    {
        return $this->hasMany(RequestAttachment::class, 'request_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'request_id');
    }

    public function review()
    {
        return $this->hasOne(Review::class, 'request_id');
    }

    public function messages()
    {
        return $this->hasMany(OrderMessage::class, 'order_id');
    }

    public function offers()
    {
        return $this->hasMany(OrderOffer::class, 'order_id');
    }
}
