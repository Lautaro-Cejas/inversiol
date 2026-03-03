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
        'precio_maximo',
    ];

    /**
     * Get the dynamically calculated stop loss price based on the maximum price reached and the stop loss percentage.
     */
    public function getStopLossPriceAttribute(): float
    {
        $referencia = $this->precio_maximo ?? $this->precio_compra;
        
        $porcentaje_absoluto = abs((float) $this->stop_loss_porcentaje);
        
        return $referencia * (1 - ($porcentaje_absoluto / 100));
    }
}