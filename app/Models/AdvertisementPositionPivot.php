<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisementPositionPivot extends Model
{
    use HasFactory;

    protected $table = 'advertisement_position_pivot';

    protected $guarded = ['id'];

    public $timestamps = false;

    public function advertisement()
    {
        return $this->belongsTo(Advertisement::class, 'advertisement_id', 'id');
    }

    public function position()
    {
        return $this->belongsTo(AdvertisementPosition::class, 'position_id', 'id');
    }
}
