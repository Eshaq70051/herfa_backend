<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierReturn extends Model
{
    use HasFactory;

    protected $table = 'supplier_returns';

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

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function supplierReturnAccountingEvents()
    {
        return $this->hasMany(SupplierReturnAccountingEvent::class, 'supplier_return_id', 'id');
    }

    public function supplierReturnItems()
    {
        return $this->hasMany(SupplierReturnItem::class, 'supplier_return_id', 'id');
    }
}
