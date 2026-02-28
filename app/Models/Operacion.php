<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operacion extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'operaciones';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'iol_id',
        'simbolo',
        'tipo',
        'cantidad',
        'precio_unitario',
        'fecha_ejecucion'
    ];
}