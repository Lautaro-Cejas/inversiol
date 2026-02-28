<?php

namespace App\Filament\Resources\OportunidadResource\Pages;

use App\Filament\Resources\OportunidadResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOportunidad extends EditRecord
{
    protected static string $resource = OportunidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
