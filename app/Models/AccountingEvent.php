<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingEvent extends Model
{
    use HasFactory;

    protected $table = 'accounting_events';

    protected $guarded = ['id'];

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id', 'id');
    }

    public function accountingEventJournalEntries()
    {
        return $this->hasMany(AccountingEventJournalEntry::class, 'accounting_event_id', 'id');
    }

    public function craftsmanCollectionAccountingEvents()
    {
        return $this->hasMany(CraftsmanCollectionAccountingEvent::class, 'accounting_event_id', 'id');
    }

    public function craftsmanEarningAccountingEvents()
    {
        return $this->hasMany(CraftsmanEarningAccountingEvent::class, 'accounting_event_id', 'id');
    }

    public function craftsmanPayoutAccountingEvents()
    {
        return $this->hasMany(CraftsmanPayoutAccountingEvent::class, 'accounting_event_id', 'id');
    }

    public function craftsmanRemittanceAccountingEvents()
    {
        return $this->hasMany(CraftsmanRemittanceAccountingEvent::class, 'accounting_event_id', 'id');
    }

    public function purchaseInvoiceAccountingEvents()
    {
        return $this->hasMany(PurchaseInvoiceAccountingEvent::class, 'accounting_event_id', 'id');
    }

    public function serviceRequestMaterialUsageAccountingEvents()
    {
        return $this->hasMany(ServiceRequestMaterialUsageAccountingEvent::class, 'accounting_event_id', 'id');
    }

    public function supplierPaymentAccountingEvents()
    {
        return $this->hasMany(SupplierPaymentAccountingEvent::class, 'accounting_event_id', 'id');
    }

    public function supplierReturnAccountingEvents()
    {
        return $this->hasMany(SupplierReturnAccountingEvent::class, 'accounting_event_id', 'id');
    }

    public function transactionAccountingEvents()
    {
        return $this->hasMany(TransactionAccountingEvent::class, 'accounting_event_id', 'id');
    }
}
