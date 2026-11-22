<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingEventJournalEntry extends Model
{
    use HasFactory;

    protected $table = 'accounting_event_journal_entries';

    protected $guarded = ['id'];

    public $timestamps = false;

    public function accountingEvent()
    {
        return $this->belongsTo(AccountingEvent::class, 'accounting_event_id', 'id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id', 'id');
    }
}
