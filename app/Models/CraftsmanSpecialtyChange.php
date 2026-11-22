<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftsmanSpecialtyChange extends Model
{
    use HasFactory;

    protected $table = 'craftsman_specialty_changes';

    protected $guarded = ['id'];

    public function craftsman()
    {
        return $this->belongsTo(CraftsmanProfile::class, 'craftsman_id', 'id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }

    public function specialty()
    {
        return $this->belongsTo(CraftsmanSpecialty::class, 'specialty_id', 'id');
    }
}
