<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceAccountingEvent extends Model
{
    use HasFactory;

    protected $table = 'purchase_invoice_accounting_events';

    protected $guarded = ['id'];

    public function accountingEvent()
    {
        return $this->belongsTo(AccountingEvent::class, 'accounting_event_id', 'id');
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id', 'id');
    }
}
