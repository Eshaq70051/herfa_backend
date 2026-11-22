<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftsmanPayoutAccountingEvent extends Model
{
    use HasFactory;

    protected $table = 'craftsman_payout_accounting_events';

    protected $guarded = ['id'];

    public function accountingEvent()
    {
        return $this->belongsTo(AccountingEvent::class, 'accounting_event_id', 'id');
    }

    public function craftsmanPayout()
    {
        return $this->belongsTo(CraftsmanPayout::class, 'craftsman_payout_id', 'id');
    }
}
