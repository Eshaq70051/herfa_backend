<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftsmanServiceArea extends Model
{
    use HasFactory;

    protected $table = 'craftsman_service_areas';

    protected $guarded = ['id'];

    public function craftsmanProfile()
    {
        return $this->belongsTo(CraftsmanProfile::class, 'craftsman_profile_id', 'id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    public function neighborhood()
    {
        return $this->belongsTo(Neighborhood::class, 'neighborhood_id', 'id');
    }
}
