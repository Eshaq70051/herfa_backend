<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPaymentAccountingEvent extends Model
{
    use HasFactory;

    protected $table = 'supplier_payment_accounting_events';

    protected $guarded = ['id'];

    public function accountingEvent()
    {
        return $this->belongsTo(AccountingEvent::class, 'accounting_event_id', 'id');
    }

    public function supplierPayment()
    {
        return $this->belongsTo(SupplierPayment::class, 'supplier_payment_id', 'id');
    }
}
