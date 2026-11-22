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

    protected $table = 'profiles_craftsman';

    protected $guarded = ['id'];

    protected $casts = [
        'availability_status' => 'boolean',
        'age' => 'integer',
        'experience_years' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected $appends = [
        'verification_status',
        'is_verified',
        'years_of_experience',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(CraftsmanDocument::class, 'craftsman_profile_id');
    }

    public function getYearsOfExperienceAttribute(): int
    {
        return (int) ($this->attributes['experience_years'] ?? 3);
    }

    public function getVerificationStatusAttribute(): string
    {
        return $this->attributes['approval_status'] ?? 'pending';
    }

    public function getIsVerifiedAttribute(): bool
    {
        return ($this->attributes['approval_status'] ?? '') === 'approved';
    }
}
