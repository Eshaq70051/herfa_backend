<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftsmanDocument extends Model
{
    use HasFactory;

    protected $table = 'craftsman_documents';

    protected $guarded = ['id'];

    public function craftsmanProfile()
    {
        return $this->belongsTo(CraftsmanProfile::class, 'craftsman_profile_id', 'id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }

    public function craftsmanCertificates()
    {
        return $this->hasMany(CraftsmanCertificate::class, 'craftsman_document_id', 'id');
    }
}
