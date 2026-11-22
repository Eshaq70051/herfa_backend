<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftsmanRemittanceAllocation extends Model
{
    use HasFactory;

    protected $table = 'craftsman_remittance_allocations';

    protected $guarded = ['id'];

    public function remittance()
    {
        return $this->belongsTo(CraftsmanRemittance::class, 'remittance_id', 'id');
    }

    public function settlement()
    {
        return $this->belongsTo(CraftsmanSettlement::class, 'settlement_id', 'id');
    }
}
