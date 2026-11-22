<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CraftsmanPortfolioImage extends Model
{
    use HasFactory;

    protected $table = 'craftsman_portfolio_images';

    protected $guarded = ['id'];

    public function portfolio()
    {
        return $this->belongsTo(CraftsmanPortfolio::class, 'portfolio_id', 'id');
    }
}
