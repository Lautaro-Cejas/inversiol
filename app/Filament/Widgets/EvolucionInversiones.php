<?php

namespace App\Filament\Widgets;

use App\Models\Inversion;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EvolucionInversiones extends ChartWidget
{
    protected static ?string $heading = 'Crecimiento de Cartera';
    protected int | string | array $columnSpan = '2';
    protected static ?int $sort = 3;

    /**
     * Get the data for the chart.
     *
     * @return array
     */
    protected function getData(): array
    {
        $data = Inversion::select(
            DB::raw('SUM(precio_compra) as total'),
            DB::raw("DATE_FORMAT(fecha_operacion, '%M') as mes")
        )
        ->groupBy('mes')
        ->orderBy('fecha_operacion')
        ->pluck('total', 'mes');

        return [
            'datasets' => [
                [
                    'label' => 'Inversión Acumulada ($)',
                    'data' => $data->values()->toArray(),
                    'fill' => 'start',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}