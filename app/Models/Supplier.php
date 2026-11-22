<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'suppliers';

    protected $guarded = ['id'];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function defaultCurrency()
    {
        return $this->belongsTo(Currency::class, 'default_currency_id', 'id');
    }

    public function journalEntryLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'supplier_id', 'id');
    }

    public function purchaseInvoices()
    {
        return $this->hasMany(PurchaseInvoice::class, 'supplier_id', 'id');
    }

    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class, 'supplier_id', 'id');
    }

    public function supplierReturns()
    {
        return $this->hasMany(SupplierReturn::class, 'supplier_id', 'id');
    }
}
