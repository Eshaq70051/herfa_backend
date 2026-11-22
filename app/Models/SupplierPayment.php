<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    use HasFactory;

    protected $table = 'supplier_payments';

    protected $guarded = ['id'];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function confirmedByUser()
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id', 'id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function supplierPaymentAccountingEvents()
    {
        return $this->hasMany(SupplierPaymentAccountingEvent::class, 'supplier_payment_id', 'id');
    }

    public function supplierPaymentAllocations()
    {
        return $this->hasMany(SupplierPaymentAllocation::class, 'supplier_payment_id', 'id');
    }
}
