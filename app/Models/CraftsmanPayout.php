<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftsmanPayout extends Model
{
    use HasFactory;

    protected $table = 'craftsman_payouts';

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

    public function earning()
    {
        return $this->belongsTo(CraftsmanEarning::class, 'earning_id', 'id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }

    public function processedByUser()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id', 'id');
    }

    public function craftsmanPayoutAccountingEvents()
    {
        return $this->hasMany(CraftsmanPayoutAccountingEvent::class, 'craftsman_payout_id', 'id');
    }
}
