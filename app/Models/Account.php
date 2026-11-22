<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $table = 'accounts';

    protected $guarded = ['id'];

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_account_id', 'id');
    }

    public function craftsmanPayouts()
    {
        return $this->hasMany(CraftsmanPayout::class, 'account_id', 'id');
    }

    public function craftsmanRemittances()
    {
        return $this->hasMany(CraftsmanRemittance::class, 'account_id', 'id');
    }

    public function journalEntryLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id', 'id');
    }

    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class, 'account_id', 'id');
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class, 'account_id', 'id');
    }
}
