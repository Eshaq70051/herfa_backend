<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public ?int $pendingRoleId = null;

    protected static function booted(): void
    {
        static::saved(function (User $user) {
            if ($user->pendingRoleId && $user->id) {
                $user->roles()->syncWithoutDetaching([$user->pendingRoleId]);
                $user->pendingRoleId = null;
            }
        });
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['name', 'phone', 'email', 'avatar', 'role', 'current_mode', 'wallet_balance', 'rating', 'total_reviews', 'is_online'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    #[Scope]
    protected function clientAccounts(Builder $query): void
    {
        $query->where(function (Builder $q) {
            $q->whereHas('roles', function (Builder $rq) {
                $rq->whereIn('code', ['customer', 'client']);
            })->orWhereDoesntHave('craftsmanProfile');
        });
    }

    #[Scope]
    protected function craftsmen(Builder $query): void
    {
        $query->where(function (Builder $q) {
            $q->whereHas('roles', function (Builder $rq) {
                $rq->where('code', 'craftsman');
            })->orWhereHas('craftsmanProfile');
        });
    }

    // Accessors and Mutators
    public function getNameAttribute(): string
    {
        return $this->attributes['full_name'] ?? '';
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['full_name'] = $value;
    }

    public function getPhoneAttribute(): string
    {
        return $this->attributes['phone_encrypted'] ?? '';
    }

    public static function hashIdentifier(string $value): string
    {
        return hash('sha256', trim($value));
    }

    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone_encrypted'] = $value;
        $this->attributes['phone_hash'] = $value ? hash('sha256', (string) $value) : null;
    }

    public function getEmailAttribute(): ?string
    {
        return $this->attributes['email_encrypted'] ?? null;
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email_encrypted'] = $value;
        $this->attributes['email_hash'] = $value ? hash('sha256', strtolower((string) $value)) : null;
    }

    public function getRoleAttribute(): string
    {
        if (isset($this->attributes['role'])) {
            return $this->attributes['role'];
        }

        $firstRole = $this->relationLoaded('roles') ? $this->roles->first() : $this->roles()->first();
        if ($firstRole) {
            $code = $firstRole->code;
            if ($code === 'customer') {
                return 'client';
            }
            if ($code === 'super_admin') {
                return 'admin';
            }

            return $code;
        }

        return 'client';
    }

    public function setRoleAttribute($value): void
    {
        $roleCode = match ($value) {
            'client' => 'customer',
            'admin' => 'admin',
            'super_admin' => 'super_admin',
            'craftsman' => 'craftsman',
            'accountant' => 'accountant',
            default => $value,
        };

        $role = Role::firstOrCreate(['code' => $roleCode], [
            'name' => ucfirst($roleCode),
            'is_system' => false,
            'is_active' => true,
        ]);

        if ($this->exists && ! empty($this->id)) {
            $this->roles()->syncWithoutDetaching([$role->id]);
        } else {
            $this->pendingRoleId = $role->id;
        }
    }

    public function getCurrentModeAttribute(): string
    {
        if (! empty($this->attributes['current_mode'])) {
            return $this->attributes['current_mode'];
        }

        $role = $this->getRoleAttribute();
        if ($role === 'craftsman') {
            return 'craftsman';
        }

        return 'client';
    }

    public function setCurrentModeAttribute($value): void
    {
        $this->attributes['current_mode'] = in_array($value, ['client', 'craftsman'], true) ? $value : 'client';
    }

    public function getAvatarAttribute(): ?string
    {
        return $this->attributes['profile_image'] ?? null;
    }

    public function setAvatarAttribute($value): void
    {
        $this->attributes['profile_image'] = $value;
    }

    public function getWalletBalanceAttribute(): float
    {
        $sumTransactions = $this->transactions()->where('payment_status', 'paid')->sum('amount');
        if ($sumTransactions > 0) {
            return (float) $sumTransactions;
        }

        return 0.00;
    }

    public function getRatingAttribute(): float
    {
        if (isset($this->attributes['rating'])) {
            return (float) $this->attributes['rating'];
        }

        return 5.0;
    }

    public function getTotalReviewsAttribute(): int
    {
        if (isset($this->attributes['total_reviews'])) {
            return (int) $this->attributes['total_reviews'];
        }

        return 0;
    }

    public function getIsOnlineAttribute(): bool
    {
        return (bool) ($this->craftsmanProfile?->availability_status ?? false);
    }

    // Role helpers
    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;
        $userRoleCodes = $this->roles->pluck('code')->toArray();

        foreach ($roles as $r) {
            if (in_array($r, $userRoleCodes, true)) {
                return true;
            }
            if ($r === 'admin' && in_array('super_admin', $userRoleCodes, true)) {
                return true;
            }
            if ($r === 'client' && in_array('customer', $userRoleCodes, true)) {
                return true;
            }
            if ($r === 'customer' && in_array('client', $userRoleCodes, true)) {
                return true;
            }
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(['super_admin', 'admin']);
    }

    public function isCraftsman(): bool
    {
        return $this->hasRole('craftsman') || $this->craftsmanProfile !== null;
    }

    public function isClient(): bool
    {
        return $this->hasRole(['customer', 'client']);
    }

    // Relationships
    public function craftsmanProfile()
    {
        return $this->hasOne(CraftsmanProfile::class, 'user_id');
    }

    public function employeeProfile()
    {
        return $this->hasOne(EmployeeProfile::class, 'user_id');
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

    public function documents()
    {
        return $this->hasManyThrough(CraftsmanDocument::class, CraftsmanProfile::class, 'user_id', 'craftsman_profile_id');
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

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function craftsmanServices()
    {
        return $this->belongsToMany(Service::class, 'craftsman_services', 'craftsman_id', 'service_id')
            ->withPivot(['experience_years', 'approval_status', 'is_active']);
    }

    public function specialties()
    {
        return $this->belongsToMany(CraftsmanSpecialty::class, 'craftsman_specialty_changes', 'craftsman_id', 'specialty_id');
    }
}
