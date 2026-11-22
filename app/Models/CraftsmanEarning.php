<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftsmanEarning extends Model
{
    use HasFactory;

    protected $table = 'craftsman_earnings';

    protected $guarded = ['id'];

    public function craftsmanUser()
    {
        return $this->belongsTo(User::class, 'craftsman_user_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function reviewedByUser()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id', 'id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id', 'id');
    }

    public function craftsmanEarningAccountingEvents()
    {
        return $this->hasMany(CraftsmanEarningAccountingEvent::class, 'craftsman_earning_id', 'id');
    }

    public function craftsmanPayouts()
    {
        return $this->hasMany(CraftsmanPayout::class, 'earning_id', 'id');
    }

    public function craftsmanSettlements()
    {
        return $this->hasMany(CraftsmanSettlement::class, 'earning_id', 'id');
    }
}
