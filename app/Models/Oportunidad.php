<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oportunidad extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'simbolo',
        'precio_gatillo',
        'cantidad',
        'estado',
        'is_active',
    ];
}