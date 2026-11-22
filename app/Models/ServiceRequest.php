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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getShortNumberAttribute(): string
    {
        if (empty($this->request_number)) {
            return '#'.$this->id;
        }
        $parts = explode('-', $this->request_number);
        $last = end($parts);
        if (! empty($last)) {
            return '#'.$last;
        }

        return '#'.$this->id;
    }

    public function getCleanNumberAttribute(): string
    {
        return ltrim($this->short_number, '#');
    }

    public function getMapUrlAttribute(): string
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }

        if (! empty($this->address_details)) {
            return 'https://www.google.com/maps/search/?api=1&query='.urlencode($this->address_details.' اليمن');
        }

        return 'https://www.google.com/maps';
    }

    public function getPaymentMethodNameAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'نقداً عند اكتمال الخدمة (كاش)',
            'kuraimi' => 'بنك الكريمي إكسبرس (Kuraimi)',
            'jawwali' => 'محفظة جوالي (Jawwali)',
            'jeeb' => 'محفظة جيب (Jeeb)',
            'onecash' => 'ون كاش (OneCash)',
            'wallet' => 'محفظة حِرفة الإلكترونية',
            'card' => 'بطاقة بنكية / فيزا',
            default => $this->payment_method ?: 'نقداً عند اكتمال الخدمة',
        };
    }

    public function getVoiceNoteUrlAttribute(): ?string
    {
        $voice = $this->voice_note ?? null;
        if (! empty($voice)) {
            return str_starts_with($voice, 'http') ? $voice : url($voice);
        }

        $voiceAtt = $this->relationLoaded('attachments')
            ? $this->attachments->first(fn ($a) => in_array(strtolower($a->file_type ?? ''), ['audio', 'voice']))
            : $this->attachments()->whereIn('file_type', ['audio', 'voice'])->first();

        if ($voiceAtt) {
            $path = $voiceAtt->file_path;

            return str_starts_with($path, 'http') ? $path : url($path);
        }

        return null;
    }

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
