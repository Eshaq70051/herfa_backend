<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يمثل محادثة المستخدم مع المساعد الذكي.
 */
class AiConversation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(AiMessage::class, 'conversation_id');
    }

    public function logs()
    {
        return $this->hasMany(AiUsageLog::class, 'conversation_id');
    }
}
