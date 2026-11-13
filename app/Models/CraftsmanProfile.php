<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يخزن بيانات الصانع المهنية وموقعه وحالة التحقق والتوفر.
 */
class CraftsmanProfile extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'availability_status' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'commission_rate' => 'decimal:2',
    ];

    protected $appends = ['verification_status', 'is_verified'];

    public function getVerificationStatusAttribute(): string
    {
        return $this->attributes['approval_status'] ?? 'pending';
    }

    public function getIsVerifiedAttribute(): bool
    {
        return ($this->attributes['approval_status'] ?? '') === 'approved' &&
               ($this->attributes['identity_status'] ?? '') === 'verified';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
