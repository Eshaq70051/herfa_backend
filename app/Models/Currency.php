<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currencies';

    protected $guarded = ['id'];

    public function craftsmanCollections()
    {
        return $this->hasMany(CraftsmanCollection::class, 'currency_id', 'id');
    }

    public function craftsmanEarnings()
    {
        return $this->hasMany(CraftsmanEarning::class, 'currency_id', 'id');
    }

    public function craftsmanPayouts()
    {
        return $this->hasMany(CraftsmanPayout::class, 'currency_id', 'id');
    }

    public function craftsmanRemittances()
    {
        return $this->hasMany(CraftsmanRemittance::class, 'currency_id', 'id');
    }

    public function exchangeRates()
    {
        return $this->hasMany(ExchangeRate::class, 'from_currency_id', 'id');
    }

    public function toCurrencyExchangeRates()
    {
        return $this->hasMany(ExchangeRate::class, 'to_currency_id', 'id');
    }

    public function journalEntryLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'currency_id', 'id');
    }

    public function purchaseInvoices()
    {
        return $this->hasMany(PurchaseInvoice::class, 'currency_id', 'id');
    }

    public function requestFees()
    {
        return $this->hasMany(RequestFee::class, 'currency_id', 'id');
    }

    public function servicePrices()
    {
        return $this->hasMany(ServicePrice::class, 'currency_id', 'id');
    }

    public function serviceRequestMaterialUsages()
    {
        return $this->hasMany(ServiceRequestMaterialUsage::class, 'currency_id', 'id');
    }

    public function serviceRequestQuotes()
    {
        return $this->hasMany(ServiceRequestQuote::class, 'currency_id', 'id');
    }

    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class, 'currency_id', 'id');
    }

    public function supplierReturns()
    {
        return $this->hasMany(SupplierReturn::class, 'currency_id', 'id');
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class, 'default_currency_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'currency_id', 'id');
    }
}
