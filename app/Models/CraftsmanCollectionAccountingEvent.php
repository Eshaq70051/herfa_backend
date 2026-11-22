<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftsmanCollectionAccountingEvent extends Model
{
    use HasFactory;

    protected $table = 'craftsman_collection_accounting_events';

    protected $guarded = ['id'];

    public function craftsmanCollection()
    {
        return $this->belongsTo(CraftsmanCollection::class, 'craftsman_collection_id', 'id');
    }

    public function accountingEvent()
    {
        return $this->belongsTo(AccountingEvent::class, 'accounting_event_id', 'id');
    }
}
