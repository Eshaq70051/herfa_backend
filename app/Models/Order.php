<?php

namespace App\Models;

/**
 * فئة تمثل طلب الخدمة ServiceRequest وتوفر توافقاً تاماً مع تطبيق الهاتف (Flutter) ولوحة التحكم.
 */
class Order extends ServiceRequest
{
    protected $table = 'service_requests';

    protected $guarded = ['id'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'inspection_fee' => 'float',
        'labor_fee' => 'float',
        'materials_fee' => 'float',
        'discount' => 'float',
        'total_amount' => 'float',
        'platform_commission' => 'float',
        'craftsman_earnings' => 'float',
        'is_urgent' => 'boolean',
        'warranty_until' => 'datetime',
        'preferred_date' => 'date',
    ];

    protected $appends = [
        'order_number',
        'client_id',
        'address_text',
        'scheduled_at',
        'status',
        'voice_note_url',
        'voice_note',
        'completion_image_url',
    ];

    public static function generateOrderNumber(): string
    {
        return 'HRF-'.date('Ymd').'-'.rand(1000, 9999);
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

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function statusRelation()
    {
        return $this->belongsTo(RequestStatus::class, 'status_id');
    }

    public function status()
    {
        return $this->belongsTo(RequestStatus::class, 'status_id');
    }

    public function attachments()
    {
        return $this->hasMany(RequestAttachment::class, 'request_id');
    }

    public function review()
    {
        return $this->hasOne(Review::class, 'request_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'request_id');
    }

    public function assignments()
    {
        return $this->hasMany(RequestAssignment::class, 'request_id');
    }

    public function currentAssignment()
    {
        return $this->hasOne(RequestAssignment::class, 'request_id')->latestOfMany();
    }

    public function details()
    {
        return $this->hasMany(RequestDetail::class, 'request_id');
    }

    // Accessors & Mutators
    public function getOrderNumberAttribute(): string
    {
        return $this->attributes['request_number'] ?? '';
    }

    public function setOrderNumberAttribute($value): void
    {
        $this->attributes['request_number'] = $value;
    }

    public function getClientIdAttribute(): int
    {
        return (int) ($this->attributes['customer_id'] ?? 0);
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

    public function getScheduledAtAttribute(): ?string
    {
        if (! empty($this->preferred_date)) {
            $date = is_string($this->preferred_date) ? $this->preferred_date : $this->preferred_date->format('Y-m-d');
            $time = $this->preferred_time ? (is_string($this->preferred_time) ? $this->preferred_time : $this->preferred_time->format('H:i:s')) : '09:00:00';

            return $date.' '.$time;
        }

        return null;
    }

    public function getStatusAttribute(): string
    {
        $code = null;
        if ($this->relationLoaded('statusRelation') && $this->statusRelation) {
            $code = $this->statusRelation->code;
        } elseif ($this->relationLoaded('status') && $this->status) {
            $code = $this->status->code;
        }

        if ($code) {
            return match ($code) {
                'pending' => 'pending_craftsman',
                default => $code,
            };
        }

        return match ($this->status_id) {
            1 => 'pending_craftsman',
            2 => 'assigned',
            3 => 'accepted',
            4 => 'in_progress',
            5 => 'completed',
            6 => 'cancelled',
            7 => 'on_the_way',
            8 => 'arrived',
            9 => 'paid',
            default => 'pending_craftsman',
        };
    }

    public function setStatusAttribute($value): void
    {
        $code = match ($value) {
            'pending_craftsman' => 'pending',
            default => $value,
        };

        $status = RequestStatus::where('code', $code)->first();
        if ($status) {
            $this->attributes['status_id'] = $status->id;
        }
    }

    public function getVoiceNoteUrlAttribute(): ?string
    {
        $voice = $this->attributes['voice_note'] ?? null;
        if (! empty($voice)) {
            return str_starts_with($voice, 'http') ? $voice : url($voice);
        }

        if ($this->relationLoaded('attachments')) {
            $voiceAtt = $this->attachments->first(fn ($a) => in_array(strtolower($a->file_type ?? ''), ['audio', 'voice']));
            if ($voiceAtt) {
                $path = $voiceAtt->file_path;

                return str_starts_with($path, 'http') ? $path : url($path);
            }
        } else {
            $voiceAtt = $this->attachments()->whereIn('file_type', ['audio', 'voice'])->first();
            if ($voiceAtt) {
                $path = $voiceAtt->file_path;

                return str_starts_with($path, 'http') ? $path : url($path);
            }
        }

        return null;
    }

    public function getVoiceNoteAttribute(): ?string
    {
        return $this->getVoiceNoteUrlAttribute();
    }

    public function getCompletionImageUrlAttribute(): ?string
    {
        if (empty($this->completion_image)) {
            return null;
        }

        if (str_starts_with($this->completion_image, 'http')) {
            return $this->completion_image;
        }

        return url($this->completion_image);
    }
}
