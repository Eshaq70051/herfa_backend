<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * يحفظ شهادة أو مستندًا مهنيًا خاصًا بصانع.
 */
class CraftsmanCertificate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function craftsman()
    {
        return $this->belongsTo(User::class, 'craftsman_id');
    }
}
