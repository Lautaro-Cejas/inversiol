<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inversion extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'activo', 
        'cantidad', 
        'precio_compra', 
        'precio_actual', 
        'moneda', 
        'fecha_operacion',
        'take_profit_porcentaje', 
        'stop_loss_porcentaje',   
    ];
}