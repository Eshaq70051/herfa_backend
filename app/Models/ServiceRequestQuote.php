<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestQuote extends Model
{
    use HasFactory;

    protected $table = 'service_request_quotes';

    protected $guarded = ['id'];

    public function adjustedByUser()
    {
        return $this->belongsTo(User::class, 'adjusted_by_user_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function previousQuote()
    {
        return $this->belongsTo(ServiceRequestQuote::class, 'previous_quote_id', 'id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id', 'id');
    }

    public function quoteNegotiations()
    {
        return $this->hasMany(QuoteNegotiation::class, 'quote_id', 'id');
    }
}
