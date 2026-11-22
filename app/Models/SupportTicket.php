<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يمثل تذكرة دعم يرسلها المستخدم وتتابعها الإدارة عبر الرسائل.
 */
class SupportTicket extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        $img = $this->attributes['image'] ?? null;
        if (empty($img)) {
            return null;
        }
        if (str_starts_with($img, 'http')) {
            return $img;
        }

        return asset($img);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function messages()
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id');
    }
}
