<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يمثل عنوان مستخدم، ويجهز بياناته وعلاقاته بالموقع الجغرافي.
 */
class Address extends Model
{
    use HasFactory;

    protected $table = 'addresses_user';

    protected $guarded = ['id'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_default' => 'boolean',
    ];

    protected $appends = ['title', 'address_line', 'city'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function neighborhood()
    {
        return $this->belongsTo(Neighborhood::class);
    }

    public function getTitleAttribute()
    {
        return $this->attributes['label'] ?? 'المنزل';
    }

    public function getAddressLineAttribute()
    {
        return $this->attributes['address_details'] ?? '';
    }

    public function getCityAttribute()
    {
        return $this->governorate?->name ?? 'صنعاء';
    }
}
