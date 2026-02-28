<?php

namespace App\Filament\Resources\OportunidadResource\Pages;

use App\Filament\Resources\OportunidadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOportunidads extends ListRecords
{
    protected static string $resource = OportunidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
