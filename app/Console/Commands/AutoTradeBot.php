<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inversion;
use App\Services\IolService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Notifications\AlertaEstancamiento;
use App\Models\User;

/**
 * Automates the execution of Take Profit and Stop Loss strategies.
 * Evaluates currently held assets against predefined performance thresholds.
 */
class AutoTradeBot extends Command
{
    protected $signature = 'broker:trade {--execute : Executes real market orders in IOL}';
    protected $description = 'Evaluates portfolio and executes automatic trades based on TP/SL rules.';

    /**
     * Execute the console command.
     *
     * @param IolService $iolService
     * @return void
     */
    public function handle(IolService $iolService): void
    {
        $this->info("Initiating market analysis...");
        
        $cartera = Inversion::where('cantidad', '>', 0)->get();

        foreach ($cartera as $activo) {
            $precioActual = $iolService->getCotizacion($activo->activo);
            
            if ($precioActual <= 0) {
                continue;
            }

            $rendimiento = (($precioActual - $activo->precio_compra) / $activo->precio_compra) * 100;
            $this->line("Analyzing {$activo->activo}: Current yield " . number_format($rendimiento, 2) . "%");

            $diasEstancado = Carbon::parse($activo->fecha_operacion)->diffInDays(now());

            if ($diasEstancado >= 15 && $rendimiento > -1.5 && $rendimiento < 1.5) {
                
                $cacheKey = "alerta_estancada_{$activo->id}";

                if (!Cache::has($cacheKey)) {
                    $usuario = User::first(); 
                    if ($usuario) {
                        $usuario->notify(new AlertaEstancamiento($activo, $diasEstancado, $rendimiento));
                    }

                    Cache::put($cacheKey, true, now()->addHours(24));
                }
            }

            // Define target thresholds for risk management
            $takeProfit = (float) $activo->take_profit_porcentaje; 
            $stopLoss = (float) $activo->stop_loss_porcentaje;

            if ($rendimiento >= $takeProfit || $rendimiento <= $stopLoss) {
                $motivo = $rendimiento >= $takeProfit ? 'TAKE PROFIT 🚀' : 'STOP LOSS ⚠️';
                $this->warn("Trade condition met for {$activo->activo} [{$motivo}]");

                if ($this->option('execute')) {
                    $this->info("Executing market order...");
                    $iolService->venderActivo($activo->activo, $activo->cantidad, $precioActual, 't0');
                } else {
                    $this->info("Simulation Mode: System would have sold {$activo->cantidad} {$activo->activo} at $ {$precioActual}.");
                }
            }
        }
        
        $this->info("Market analysis completed.");
    }
}