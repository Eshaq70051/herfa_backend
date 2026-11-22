<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPaymentAllocation extends Model
{
    use HasFactory;

    protected $table = 'supplier_payment_allocations';

    protected $guarded = ['id'];

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id', 'id');
    }

    public function supplierPayment()
    {
        return $this->belongsTo(SupplierPayment::class, 'supplier_payment_id', 'id');
    }
}
