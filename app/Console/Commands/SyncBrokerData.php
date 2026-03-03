<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inversion;
use App\Models\Operacion;
use App\Models\PortfolioHistory;
use Carbon\Carbon;
use App\Services\IolService;
use Filament\Notifications\Notification;
use App\Models\User;

/**
 * Synchronizes local database with IOL's remote state, including
 * transaction history, current portfolio holdings, and daily equity metrics.
 */
class SyncBrokerData extends Command
{
    protected $signature = 'broker:sync';
    protected $description = 'Fetches history and active portfolio from IOL API.';

    /**
     * Execute the console command.
     *
     * @param IolService $iolService
     * @return void
     */
    public function handle(IolService $iolService): void
    {
        $this->syncHistorial($iolService);
        $this->syncPortafolio($iolService);
        $this->recordPortfolioHistory($iolService);
    }

    /**
     * Synchronizes immutable transaction history.
     */
    private function syncHistorial(IolService $iolService): void
    {
        try {
            $movimientos = $iolService->getHistorialOperaciones();
            
            foreach ($movimientos as $mov) {
                Operacion::updateOrCreate(
                    ['iol_id' => $mov['numero']],
                    [
                        'simbolo' => $mov['simbolo'],
                        'tipo' => $mov['tipo'],
                        'cantidad' => $mov['cantidad'],
                        'precio_unitario' => $mov['precio'],
                        'fecha_ejecucion' => Carbon::parse($mov['fechaOrden']),
                        'estado' => $mov['estado'],
                    ]
                );
            }
        } catch (\Exception $e) {
            $this->error("History sync failed: " . $e->getMessage());
        }
    }

    /**
     * Reconciles current active holdings and dispatches settlement notifications.
     */
    private function syncPortafolio(IolService $iolService): void
    {
        $usuario = User::first(); 

        try {
            $portafolioReal = $iolService->getPortafolio(); 
            $simbolosEnPortafolio = collect($portafolioReal)->pluck('titulo.simbolo')->toArray();

            Inversion::whereNotIn('activo', $simbolosEnPortafolio)->update(['cantidad' => 0]);

            foreach ($portafolioReal as $item) {
                $simbolo = $item['titulo']['simbolo'];
                $cantidadNueva = $item['cantidad'];
                
                $inversionAnterior = Inversion::where('activo', $simbolo)->first();
                $cantidadAnterior = $inversionAnterior ? $inversionAnterior->cantidad : 0;

                Inversion::updateOrCreate(
                    ['activo' => $simbolo],
                    [
                        'cantidad' => $cantidadNueva,
                        'precio_compra' => $item['ppc'], 
                        'precio_actual' => $item['ultimoPrecio'],
                        'moneda' => (isset($item['titulo']['moneda']) && $item['titulo']['moneda'] === 'peso_Argentino') ? 'ARS' : 'USD',
                        'fecha_operacion' => now(),
                    ]
                );
                
                if ($cantidadAnterior == 0 && $cantidadNueva > 0) {
                    if ($usuario) {
                        Notification::make()
                            ->title('¡Activo Liquidado!')
                            ->body("Ya tenés disponible {$cantidadNueva} nominales de {$simbolo} en tu cuenta.")
                            ->success()
                            ->icon('heroicon-o-check-circle')
                            ->sendToDatabase($usuario);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("Portfolio sync failed: " . $e->getMessage());
        }
    }

    /**
     * Snapshots total equity for historical chart rendering.
     */
    private function recordPortfolioHistory(IolService $iolService): void
    {
        $totalCartera = Inversion::all()->sum(fn($i) => $i->cantidad * $i->precio_actual) + $iolService->getSaldo();

        PortfolioHistory::updateOrCreate(
            ['fecha' => now()->format('Y-m-d')],
            ['total_ars' => $totalCartera]
        );
    }
}