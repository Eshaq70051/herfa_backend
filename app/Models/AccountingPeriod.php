<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingPeriod extends Model
{
    use HasFactory;

    protected $table = 'accounting_periods';

    protected $guarded = ['id'];

    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by_user_id', 'id');
    }

    public function lockedByUser()
    {
        return $this->belongsTo(User::class, 'locked_by_user_id', 'id');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'accounting_period_id', 'id');
    }
}
