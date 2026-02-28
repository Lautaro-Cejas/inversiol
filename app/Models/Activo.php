<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activo extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'simbolo',
        'nombre',
        'tipo',
        'moneda',
    ];
}
