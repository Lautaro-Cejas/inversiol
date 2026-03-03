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
        'es_recurrente',
        'disponible_desde',
        'mejora_porcentaje',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected function casts(): array
    {
        return [
            'es_recurrente' => 'boolean',
            'disponible_desde' => 'datetime',
        ];
    }
}