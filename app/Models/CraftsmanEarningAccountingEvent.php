<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftsmanEarningAccountingEvent extends Model
{
    use HasFactory;

    protected $table = 'craftsman_earning_accounting_events';

    protected $guarded = ['id'];

    public function accountingEvent()
    {
        return $this->belongsTo(AccountingEvent::class, 'accounting_event_id', 'id');
    }

    public function craftsmanEarning()
    {
        return $this->belongsTo(CraftsmanEarning::class, 'craftsman_earning_id', 'id');
    }
}
