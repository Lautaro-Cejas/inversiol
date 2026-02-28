<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Activo extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'simbolo',
        'cantidad_total', 
        'precio_actual',   
        'monitorear',     
    ];

    /**
     * Scope for activos that are being monitored.
     */
    public function scopeMonitoreados(Builder $query): void
    {
        $query->where('monitorear', true);
    }
}