<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class IolService
{
    protected string $baseUrl = 'https://api.invertironline.com';

    /**
     * Get an access token from IOL API. Caches the token until it expires. If the token is invalid, it will be refreshed automatically on the next request.
     */
    public function getToken(): string
    {
        if (Cache::has('iol_access_token')) {
            return Cache::get('iol_access_token');
        }

        $response = Http::asForm()->post("{$this->baseUrl}/token", [
            'username'   => env('IOL_USERNAME'),
            'password'   => env('IOL_PASSWORD'),
            'grant_type' => 'password',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $token = $data['access_token'];
            
            $expiresAt = now()->addSeconds($data['expires_in'] - 60);
            
            Cache::put('iol_access_token', $token, $expiresAt);
            Cache::put('iol_refresh_token', $data['refresh_token'], now()->addDays(7));

            Log::info('Nuevo token de IOL generado exitosamente.');
            return $token;
        }

        Log::error('Error al autenticar en IOL: ' . $response->body());
        throw new \Exception('No se pudo obtener el token de IOL. Revisá las credenciales.');
    }

    /**
     * Get the current portfolio of assets in IOL. Returns an array of assets with their details.
     */
    public function getPortafolio(): array
    {
        $response = $this->sendRequest('get', '/api/v2/portafolio/argentina');

        if ($response->successful()) {
            return $response->json()['activos'] ?? [];
        }

        throw new \Exception('No se pudo obtener el portafolio de IOL.');
    }

    /**
     * Get the historical operations (trades) from IOL. Returns an array of operations with their details.
     */
    public function getHistorialOperaciones(): array
    {
        $response = $this->sendRequest('get', '/api/v2/operaciones');

        if ($response->successful()) {
            return $response->json();
        }

        Log::error("Error IOL Historial: " . $response->status() . " - " . $response->body());
        throw new \Exception('No se pudo obtener el historial de operaciones de IOL.');
    }
    
    /**
     * Get the current cash balance in IOL. Returns a float with the available cash in ARS.
     */
    public function getSaldo(): float
    {
        $response = $this->sendRequest('get', '/api/v2/estadocuenta');

        if ($response->successful()) {
            $data = $response->json();
            
            if (isset($data['cuentas']) && is_array($data['cuentas'])) {
                foreach ($data['cuentas'] as $cuenta) {
                    if (isset($cuenta['moneda']) && strtolower($cuenta['moneda']) === 'peso_argentino') {
                        return (float) ($cuenta['disponible'] ?? 0.0);
                    }
                }
            } else {
                Log::warning('IOL API successful but missing cuentas key. Response: ' . $response->body());
            }
        }

        return 0.0;
    }

    /**
     * Get the current price of a specific asset in IOL. Returns a float with the last price or 0 if not found or error.
     */
    public function getCotizacion($simbolo): float
    {
        $response = $this->sendRequest('get', "/api/v2/bCBA/Titulos/{$simbolo}/Cotizacion");

        if ($response->successful()) {
            $data = $response->json();
            return (float) ($data['ultimoPrecio'] ?? 0.0);
        }

        Log::error("Error IOL en cotización individual de {$simbolo}: " . $response->body());
        return 0.0;
    }

    /**
     * Sends a sell order to IOL. Returns the API response or null in case of error.
     */
    public function venderActivo($simbolo, $cantidad, $precio, $plazo = 't0')
    {
        $token = $this->getToken();
        
        $response = Http::withToken($token)->post("{$this->baseUrl}/api/v2/Operar/Vender", [
            'mercado' => 'bCBA',
            'simbolo' => $simbolo,
            'cantidad' => $cantidad,
            'precio' => $precio,
            'plazo' => $plazo,     
            'validez' => 'hoy',     
        ]);

        if ($response->successful()) {
            Log::info("Orden de VENTA enviada: {$cantidad} {$simbolo} a $ {$precio}");
            return $response->json();
        }

        Log::error("Error al intentar vender {$simbolo}: " . $response->body());
        return null;
    }

    /**
     * Sends a buy order to IOL. Returns the API response or null in case of error.
     */
    private function sendRequest(string $method, string $endpoint, array $data = [])
    {
        $token = $this->getToken();
        $url = "{$this->baseUrl}{$endpoint}";

        $response = Http::withToken($token)->$method($url, $data);

        if ($response->status() === 401) {
            Log::warning("Token IOL revocado por el servidor. Forzando renovación...");
            
            Cache::forget('iol_access_token');
            $nuevoToken = $this->getToken();
            
            $response = Http::withToken($nuevoToken)->$method($url, $data);
        }

        return $response;
    }

    /**
     * Sends a buy order to IOL. Returns the API response or null in case of error.
     */
    public function comprarActivo($simbolo, $cantidad, $precio, $plazo = 't0')
    {
        $response = $this->sendRequest('post', '/api/v2/Operar/Comprar', [
            'mercado' => 'bCBA',
            'simbolo' => $simbolo,
            'cantidad' => $cantidad,
            'precio' => $precio,
            'plazo' => $plazo, 
            'validez' => 'hoy', 
        ]);

        if ($response->successful()) {
            Log::info("Orden de COMPRA enviada: {$cantidad} {$simbolo} a $ {$precio}");
            return $response->json();
        }

        Log::error("Error al intentar comprar {$simbolo}: " . $response->body());
        return null;
    }

    public function getFullCotizacion($simbolo): ?array
    {
        $response = $this->sendRequest('get', "/api/v2/bCBA/Titulos/{$simbolo}/Cotizacion");
        
        if ($response->successful()) {
            return $response->json();
        }

        Log::error("Error IOL en cotización completa de {$simbolo}: " . $response->body());
        return null;
    }
}