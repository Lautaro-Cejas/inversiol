<?php

namespace App\Filament\Resources\InversionResource\Pages;

use App\Filament\Resources\InversionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInversion extends EditRecord
{
    protected static string $resource = InversionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
