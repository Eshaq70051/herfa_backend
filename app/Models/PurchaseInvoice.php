<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    use HasFactory;

    protected $table = 'purchase_invoices';

    protected $guarded = ['id'];

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function postedByUser()
    {
        return $this->belongsTo(User::class, 'posted_by_user_id', 'id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function purchaseInvoiceAccountingEvents()
    {
        return $this->hasMany(PurchaseInvoiceAccountingEvent::class, 'purchase_invoice_id', 'id');
    }

    public function purchaseInvoiceItems()
    {
        return $this->hasMany(PurchaseInvoiceItem::class, 'purchase_invoice_id', 'id');
    }

    public function supplierPaymentAllocations()
    {
        return $this->hasMany(SupplierPaymentAllocation::class, 'purchase_invoice_id', 'id');
    }

    public function supplierReturns()
    {
        return $this->hasMany(SupplierReturn::class, 'purchase_invoice_id', 'id');
    }
}
