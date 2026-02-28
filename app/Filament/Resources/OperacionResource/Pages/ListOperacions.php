<?php

namespace App\Filament\Resources\OperacionResource\Pages;

use App\Filament\Resources\OperacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOperacions extends ListRecords
{
    protected static string $resource = OperacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
