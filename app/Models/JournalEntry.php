<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $table = 'journal_entries';

    protected $guarded = ['id'];

    public function accountingPeriod()
    {
        return $this->belongsTo(AccountingPeriod::class, 'accounting_period_id', 'id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id', 'id');
    }

    public function postedByUser()
    {
        return $this->belongsTo(User::class, 'posted_by_user_id', 'id');
    }

    public function reversalOfEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'reversal_of_entry_id', 'id');
    }

    public function accountingEventJournalEntries()
    {
        return $this->hasMany(AccountingEventJournalEntry::class, 'journal_entry_id', 'id');
    }

    public function journalEntryLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id', 'id');
    }
}
