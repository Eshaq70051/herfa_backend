<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * يمثل حساب المستخدم وبيانات المصادقة وعلاقاته بالطلبات والمحفظة والخدمات.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['name', 'avatar'];

    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'wallet_balance' => 'decimal:2',
            'rating' => 'decimal:2',
            'is_online' => 'boolean',
        ];
    }

    // Accessors for backward compatibility
    public function getNameAttribute(): string
    {
        return $this->attributes['full_name'] ?? '';
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['full_name'] = $value;
    }

    public function getRoleAttribute(): string
    {
        $role = $this->attributes['role'] ?? 'customer';
        return ($role === 'customer') ? 'client' : $role;
    }

    public function setRoleAttribute($value): void
    {
        $this->attributes['role'] = ($value === 'client') ? 'customer' : $value;
    }

    public function getCurrentModeAttribute(): string
    {
        $mode = $this->attributes['current_mode'] ?? 'customer';
        return ($mode === 'customer') ? 'client' : $mode;
    }

    public function setCurrentModeAttribute($value): void
    {
        $this->attributes['current_mode'] = ($value === 'client') ? 'customer' : $value;
    }

    public function getAvatarAttribute(): ?string
    {
        return $this->attributes['profile_image'] ?? null;
    }

    public function setAvatarAttribute($value): void
    {
        $this->attributes['profile_image'] = $value;
    }

    // Relationships
    public function craftsmanProfile()
    {
        return $this->hasOne(CraftsmanProfile::class, 'user_id');
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class, 'user_id');
    }

    public function devices()
    {
        return $this->hasMany(UserDevice::class, 'user_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'customer_id');
    }

    public function assignedRequests()
    {
        return $this->hasMany(RequestAssignment::class, 'craftsman_id');
    }

    public function portfolio()
    {
        return $this->hasMany(CraftsmanPortfolio::class, 'craftsman_id');
    }

    public function certificates()
    {
        return $this->hasMany(CraftsmanCertificate::class, 'craftsman_id');
    }

    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'craftsman_id');
    }

    public function offers()
    {
        return $this->hasMany(Offer::class, 'craftsman_id');
    }

    public function aiConversations()
    {
        return $this->hasMany(AiConversation::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    /**
     * تخصصات الحرفي - يربط الحرفي بالفئات التي يعمل بها.
     */
    public function craftsmanCategories()
    {
        return $this->belongsToMany(Category::class, 'craftsman_specialties', 'craftsman_id', 'category_id');
    }
}
