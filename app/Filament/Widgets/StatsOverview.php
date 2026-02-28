<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Inversion;
use App\Services\IolService;
use Illuminate\Support\Facades\Http;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    /**
     * Get the data for the widget.
     *
     * @return array
     */
    protected function getStats(): array
    {
        try {
            $responseMep = Http::timeout(3)->get('https://dolarapi.com/v1/dolares/bolsa');
            $cotizacionMep = $responseMep->successful() ? $responseMep->json()['venta'] : 0;
        } catch (\Exception $e) {
            $cotizacionMep = 0;
        }

        $iolService = app(IolService::class);
        $saldoDisponibleARS = $iolService->getSaldo();

        $inversiones = Inversion::all();

        $totalInvertidoARS = 0;
        $valorActualARS = 0;
        $activosARS = 0;

        foreach ($inversiones as $inv) {
            if ($inv->moneda === 'ARS') {
                $totalInvertidoARS += ($inv->cantidad * $inv->precio_compra);
                $valorActualARS += ($inv->cantidad * $inv->precio_actual);
                $activosARS++;
            }
        }

        $rendimientoARS = $valorActualARS - $totalInvertidoARS;
        $porcentajeARS = $totalInvertidoARS > 0 ? ($rendimientoARS / $totalInvertidoARS) * 100 : 0;

        $colorRendimiento = $rendimientoARS >= 0 ? 'success' : 'danger';
        $iconoRendimiento = $rendimientoARS >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';

        return [
            Stat::make('Dólar MEP', $cotizacionMep > 0 ? '$ ' . number_format($cotizacionMep, 2, ',', '.') : 'Sin datos')
                ->description('Cotización de mercado en vivo')
                ->color('warning')
                ->icon('heroicon-m-currency-dollar'),

            Stat::make('Valor Actual (ARS)', '$ ' . number_format($valorActualARS, 2, ',', '.'))
                ->description('Inversión inicial: $ ' . number_format($totalInvertidoARS, 2, ',', '.'))
                ->color('info'),

            Stat::make('Activos en Cartera', $activosARS)
                ->description('Actualizado en tiempo real')
                ->color('primary'),

            Stat::make('Rendimiento (ARS)', '$ ' . number_format($rendimientoARS, 2, ',', '.'))
                ->description(number_format($porcentajeARS, 2, ',', '.') . '% de rentabilidad')
                ->descriptionIcon($iconoRendimiento)
                ->color($colorRendimiento),

            Stat::make('Efectivo Disponible (ARS)', '$ ' . number_format($saldoDisponibleARS, 2, ',', '.'))
                ->description('Saldo líquido en IOL para operar')
                ->descriptionColor('gray') 
                ->color('primary')        
                ->icon('heroicon-m-banknotes'),
        ];
    }
}
