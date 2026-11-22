<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftsmanCollection extends Model
{
    use HasFactory;

    protected $table = 'craftsman_collections';

    protected $guarded = ['id'];

    public function craftsmanUser()
    {
        return $this->belongsTo(User::class, 'craftsman_user_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function customerUser()
    {
        return $this->belongsTo(User::class, 'customer_user_id', 'id');
    }

    public function exceptionApprovedByUser()
    {
        return $this->belongsTo(User::class, 'exception_approved_by_user_id', 'id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id', 'id');
    }

    public function verifiedByUser()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id', 'id');
    }

    public function craftsmanCollectionAccountingEvents()
    {
        return $this->hasMany(CraftsmanCollectionAccountingEvent::class, 'craftsman_collection_id', 'id');
    }

    public function craftsmanSettlements()
    {
        return $this->hasMany(CraftsmanSettlement::class, 'collection_id', 'id');
    }
}
