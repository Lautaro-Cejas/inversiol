<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Http\Responses\Auth\LoginResponse;

class CustomLogin extends BaseLogin
{
    /**
     * Override the authenticate method to include rate limiting.
     */
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(3); 
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'data.email' => __("You have made too many attempts. Please try again in {$exception->secondsUntilAvailable} seconds."),
            ]);
        }

        return parent::authenticate();
    }
}