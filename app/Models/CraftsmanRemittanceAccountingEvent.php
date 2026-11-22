<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftsmanRemittanceAccountingEvent extends Model
{
    use HasFactory;

    protected $table = 'craftsman_remittance_accounting_events';

    protected $guarded = ['id'];

    public function accountingEvent()
    {
        return $this->belongsTo(AccountingEvent::class, 'accounting_event_id', 'id');
    }

    public function craftsmanRemittance()
    {
        return $this->belongsTo(CraftsmanRemittance::class, 'craftsman_remittance_id', 'id');
    }
}
