<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يعرّف حالات الطلب الممكنة في دورة تنفيذ الخدمة.
 */
class RequestStatus extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function requests()
    {
        return $this->hasMany(ServiceRequest::class, 'status_id');
    }
}
