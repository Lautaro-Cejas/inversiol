<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activo;

class ActivoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activos = [
            ['simbolo' => 'SPY', 'nombre' => 'SPDR S&P 500 ETF Trust', 'monitorear' => true],
            ['simbolo' => 'NVDA', 'nombre' => 'NVIDIA Corporation', 'monitorear' => true],
            ['simbolo' => 'MSTR', 'nombre' => 'MicroStrategy Incorporated', 'monitorear' => true],
            ['simbolo' => 'TSLA', 'nombre' => 'Tesla, Inc.', 'monitorear' => true],
        ];

        foreach ($activos as $activo) {
            Activo::updateOrCreate(['simbolo' => $activo['simbolo']], $activo);
        }
    }
}
