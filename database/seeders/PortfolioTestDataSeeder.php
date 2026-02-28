<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Operacion;
use App\Models\Inversion;
use App\Models\PortfolioHistory;
use Carbon\Carbon;

class PortfolioTestDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpiamos datos previos para no duplicar
        Operacion::truncate();
        Inversion::truncate();
        PortfolioHistory::truncate();

        // 2. Creamos Operaciones de prueba (Historial inmutable)
        $ops = [
            ['simbolo' => 'AAPL', 'cantidad' => 10, 'precio' => 1500, 'fecha' => now()->subDays(10), 'estado' => 'Terminada'],
            ['simbolo' => 'AAPL', 'cantidad' => 5, 'precio' => 1700, 'fecha' => now()->subDays(5), 'estado' => 'Terminada'],
            ['simbolo' => 'ALUA', 'cantidad' => 100, 'precio' => 900, 'fecha' => now()->subDays(8), 'estado' => 'Terminada'],
            ['simbolo' => 'KO', 'cantidad' => 20, 'precio' => 12000, 'fecha' => now()->subDays(12), 'estado' => 'Terminada'],
            // Simulamos tu compra de SPY que se ejecutó pero la plata está comprometida (Pendiente de liquidación)
            ['simbolo' => 'SPY', 'cantidad' => 1, 'precio' => 50650, 'fecha' => now(), 'estado' => 'Pendiente'],
        ];

        foreach ($ops as $index => $op) {
            Operacion::create([
                'iol_id' => 2000 + $index,
                'simbolo' => $op['simbolo'],
                'tipo' => 'Compra',
                'cantidad' => $op['cantidad'],
                'precio_unitario' => $op['precio'],
                'fecha_ejecucion' => $op['fecha'],
                'estado' => $op['estado'], // <- Nuevo campo agregado
            ]);
        }

        // 3. Creamos las Inversiones consolidadas (Estado actual)
        Inversion::create([
            'activo' => 'AAPL',
            'cantidad' => 15,
            'precio_compra' => 1566.66, 
            'precio_actual' => 1850.00, // Ganancia del ~18% para testear el bot de Take Profit
            'fecha_operacion' => now()->subDays(5),
            'moneda' => 'ARS', // <- Nuevo campo
        ]);

        Inversion::create([
            'activo' => 'ALUA',
            'cantidad' => 100,
            'precio_compra' => 900.00,
            'precio_actual' => 830.00, // Pérdida del ~7% para testear el bot de Stop Loss
            'fecha_operacion' => now()->subDays(8),
            'moneda' => 'ARS', // <- Nuevo campo
        ]);

        // Simulamos el SPY esperando liquidación (Cantidad 0)
        Inversion::create([
            'activo' => 'SPY',
            'cantidad' => 0, // <- Esto activará el badge de "En Liquidación"
            'precio_compra' => 50650.00,
            'precio_actual' => 51125.00,
            'fecha_operacion' => now(),
            'moneda' => 'ARS', // <- Nuevo campo
        ]);

        // 4. Generamos Historial para el Gráfico (Evolución de 15 días)
        $capitalInicial = 250000;
        for ($i = 15; $i >= 0; $i--) {
            $variacion = rand(-5000, 15000);
            $capitalInicial += $variacion;

            PortfolioHistory::create([
                'fecha' => Carbon::now()->subDays($i)->format('Y-m-d'),
                'total_ars' => $capitalInicial,
            ]);
        }
    }
}