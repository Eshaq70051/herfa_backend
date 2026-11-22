<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftsmanRemittance extends Model
{
    use HasFactory;

    protected $table = 'craftsman_remittances';

    protected $guarded = ['id'];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function craftsmanUser()
    {
        return $this->belongsTo(User::class, 'craftsman_user_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }

    public function receivedByUser()
    {
        return $this->belongsTo(User::class, 'received_by_user_id', 'id');
    }

    public function craftsmanRemittanceAccountingEvents()
    {
        return $this->hasMany(CraftsmanRemittanceAccountingEvent::class, 'craftsman_remittance_id', 'id');
    }

    public function craftsmanRemittanceAllocations()
    {
        return $this->hasMany(CraftsmanRemittanceAllocation::class, 'remittance_id', 'id');
    }
}
