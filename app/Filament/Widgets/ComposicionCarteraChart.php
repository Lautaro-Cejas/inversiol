<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Inversion;
use App\Services\IolService;

class ComposicionCarteraChart extends ChartWidget
{
    protected static ?string $heading = 'Composición de la Cartera';
    protected int | string | array $columnSpan = '1';
    protected static ?int $sort = 2;

    /**
     * Get the data for the chart.
     *
     * @return array
     */
    protected function getData(): array
    {
        $iolService = app(IolService::class);
        $efectivo = $iolService->getSaldo();

        $valorActivos = Inversion::where('cantidad', '>', 0)
            ->get()
            ->sum(fn($inv) => $inv->cantidad * $inv->precio_actual);

        return [
            'datasets' => [
                [
                    'label' => 'Capital (ARS)',
                    'data' => [$efectivo, $valorActivos],
                    'backgroundColor' => [
                        '#10b981', 
                        '#3b82f6',
                    ],
                ],
            ],
            'labels' => ['Efectivo (Liquidez)', 'Inversiones (Activos)'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}