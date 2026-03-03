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
 * Automates the execution of Take Profit and Trailing Stop Loss strategies.
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
            
            if ($precioActual <= 0) continue;

            if ($precioActual > ($activo->precio_maximo ?? $activo->precio_compra)) {
                $activo->update(['precio_maximo' => $precioActual]);
                $this->info("New maximum price for {$activo->activo}: $ " . number_format($precioActual, 2));
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

            $takeProfitThreshold = (float) $activo->take_profit_porcentaje; 
            $precioGatilloSL = $activo->stop_loss_price; 

            $cumpleTP = $rendimiento >= $takeProfitThreshold;
            $cumpleSL = $precioActual <= $precioGatilloSL; 

            if ($cumpleTP || $cumpleSL) {
                $motivo = $cumpleTP ? 'TAKE PROFIT 🚀' : 'TRAILING STOP LOSS ⚠️';
                $this->warn("Trade condition met for {$activo->activo} [{$motivo}]");

                if ($this->option('execute')) {
                    $this->info("Executing market order...");
                    $respuesta = $iolService->venderActivo($activo->activo, $activo->cantidad, $precioActual, 't0');
                    
                    if ($respuesta && isset($respuesta['numeroOperacion'])) {
                        $activo->update(['cantidad' => 0, 'precio_actual' => $precioActual]);
                        $this->info("Order filled successfully for {$activo->activo}.");
                    } else {
                        $this->error("Broker rejected the order. Check logs.");
                    }
                } else {
                    $this->info("Simulation Mode: System would have sold {$activo->cantidad} {$activo->activo} at $ {$precioActual}. (SL Trigger was: $ {$precioGatilloSL})");
                }
            }
        }
        
        $this->info("Market analysis completed.");
    }
}