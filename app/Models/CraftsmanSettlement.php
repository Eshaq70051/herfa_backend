<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftsmanSettlement extends Model
{
    use HasFactory;

    protected $table = 'craftsman_settlements';

    protected $guarded = ['id'];

    public function collection()
    {
        return $this->belongsTo(CraftsmanCollection::class, 'collection_id', 'id');
    }

    public function earning()
    {
        return $this->belongsTo(CraftsmanEarning::class, 'earning_id', 'id');
    }

    public function reviewedByUser()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id', 'id');
    }

    public function craftsmanRemittanceAllocations()
    {
        return $this->hasMany(CraftsmanRemittanceAllocation::class, 'settlement_id', 'id');
    }
}
