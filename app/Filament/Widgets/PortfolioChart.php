<?php

namespace App\Filament\Widgets;

use App\Models\PortfolioHistory;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class PortfolioChart extends ChartWidget
{
    protected static ?string $heading = 'Evolución de la Cartera';
    protected int | string | array $columnSpan = '1';
    protected static ?int $sort = 2;

    /**
     * Get the data for the chart.
     *
     * @return array
     */
    protected function getData(): array
    {
        $historico = PortfolioHistory::orderBy('fecha')->get();

        return [
            'datasets' => [
                [
                    'label' => 'Capital Total (ARS)',
                    'data' => $historico->pluck('total_ars')->toArray(),
                    'borderColor' => '#10b981',
                    'fill' => 'start',
                ],
            ],
            'labels' => $historico->pluck('fecha')->map(fn($f) => Carbon::parse($f)->format('d/m'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}