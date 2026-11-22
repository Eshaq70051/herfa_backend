<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionAccountingEvent extends Model
{
    use HasFactory;

    protected $table = 'transaction_accounting_events';

    protected $guarded = ['id'];

    public function accountingEvent()
    {
        return $this->belongsTo(AccountingEvent::class, 'accounting_event_id', 'id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }
}
