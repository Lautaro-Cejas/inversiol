<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Oportunidad;
use App\Services\IolService;
use Filament\Notifications\Notification;
use App\Models\User;

/**
 * Scans the market for pending limit-buy opportunities and executes
 * them if the target price is reached and funds are available.
 */
class CazarOportunidades extends Command
{
    protected $signature = 'broker:cazar';
    protected $description = 'Monitors pending opportunities and triggers buy orders when conditions are met.';

    /**
     * Execute the console command.
     *
     * @param IolService $iolService
     * @return void
     */
    public function handle(IolService $iolService): void
    {
        $oportunidades = Oportunidad::where('estado', 'Pendiente')
            ->where('is_active', true)
            ->get();

        if ($oportunidades->isEmpty()) {
            return;
        }

        $usuario = User::first();
        $saldoDisponible = $iolService->getSaldo();

        foreach ($oportunidades as $oportunidad) {
            $precioActual = $iolService->getCotizacion($oportunidad->simbolo);

            if ($precioActual <= 0) {
                $this->error("Failed to retrieve quote for {$oportunidad->simbolo}. Skipping...");
                continue;
            }

            if ($precioActual <= $oportunidad->precio_gatillo) {
                $costoEstimado = $precioActual * $oportunidad->cantidad;

                if ($costoEstimado > $saldoDisponible) {
                    $this->error("Insufficient funds for {$oportunidad->simbolo}. Required: $ {$costoEstimado}, Available: $ {$saldoDisponible}.");

                    if ($usuario) {
                        Notification::make()
                            ->title('Oportunidad pausada: Sin fondos')
                            ->body("El {$oportunidad->simbolo} tocó tu precio ($ {$precioActual}), pero tu saldo líquido es de $ {$saldoDisponible} ARS.")
                            ->warning()
                            ->sendToDatabase($usuario);
                    }
                    continue;
                }

                $respuesta = $iolService->comprarActivo(
                    $oportunidad->simbolo,
                    $oportunidad->cantidad,
                    $precioActual
                );

                if ($respuesta && isset($respuesta['numeroOperacion'])) {
                    $oportunidad->update(['estado' => 'Ejecutada']);
                    $saldoDisponible -= $costoEstimado;
                    
                    $this->info("Order filled successfully. Target: {$oportunidad->simbolo}");

                    if ($usuario) {
                        Notification::make()
                            ->title('¡Oportunidad Cazada!')
                            ->body("Se compraron {$oportunidad->cantidad}x {$oportunidad->simbolo} a $ {$precioActual}.")
                            ->success()
                            ->sendToDatabase($usuario);
                    }
                } else {
                    $this->error("Broker rejected the order. Check logs for details.");
                }
            }
        }
    }
}