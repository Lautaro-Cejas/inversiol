<?php

namespace App\Filament\Widgets;

use App\Models\Activo;
use App\Services\IolService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class PreciosEnVivo extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    /**
     * Get the data for the widget. This method is called every time the widget is rendered or polled. It should return an array of Stat objects.
     */
    protected function getStats(): array
    {
        $iolService = app(IolService::class);
        $activos = Activo::monitoreados()->pluck('simbolo')->toArray();
        $stats = [];

        foreach ($activos as $simbolo) {    
            $data = Cache::remember("data_live_{$simbolo}", 60, function () use ($iolService, $simbolo) {
                return $iolService->getFullCotizacion($simbolo); 
            });

            if ($data) {
                $precio = $data['ultimoPrecio'] ?? 0;
                $variacion = $data['variacion'] ?? 0; 

                $isPositive = $variacion >= 0;

                $stats[] = Stat::make(strtoupper($simbolo), "$ " . number_format($precio, 2, ',', '.'))
                    ->description(number_format($variacion, 2, ',', '.') . "% respecto al cierre")
                    ->descriptionIcon($isPositive ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                    ->color($isPositive ? 'success' : 'danger');
            }

        }

        return $stats;
    }
}