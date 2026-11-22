<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteNegotiation extends Model
{
    use HasFactory;

    protected $table = 'quote_negotiations';

    protected $guarded = ['id'];

    public function quote()
    {
        return $this->belongsTo(ServiceRequestQuote::class, 'quote_id', 'id');
    }

    public function reviewedByUser()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id', 'id');
    }
}
