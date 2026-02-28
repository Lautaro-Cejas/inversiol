<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioHistory extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['fecha', 'total_ars'];
}
