<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperacionResource\Pages;
use App\Models\Operacion;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class OperacionResource extends Resource
{
    protected static ?string $model = Operacion::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Operación';
    protected static ?string $pluralModelLabel = 'Operaciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Terminada' => 'success',
                        'Pendiente' => 'warning',
                        'Cancelada' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('fecha_ejecucion')
                    ->dateTime('d/m/Y H:i')
                    ->label('Fecha')
                    ->sortable(),
                TextColumn::make('simbolo')
                    ->label('Activo')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Compra' => 'success',
                        'Venta' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('cantidad')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('precio_unitario')
                    ->money('ARS')
                    ->label('Precio Unit.'),
                TextColumn::make('total')
                    ->label('Total Operado')
                    ->state(fn ($record) => $record->cantidad * $record->precio_unitario)
                    ->money('ARS'),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->options([
                        'Compra' => 'Compra',
                        'Venta' => 'Venta',
                    ]),
                SelectFilter::make('estado')
                    ->options([
                        'iniciada' => 'Iniciada / Pendiente',
                        'Terminada' => 'Terminada / Liquidada',
                        'Cancelada' => 'Cancelada',
                    ])
                    ->multiple(),
            ])
            ->defaultSort('fecha_ejecucion', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOperacions::route('/'),
            'create' => Pages\CreateOperacion::route('/create'),
            'edit' => Pages\EditOperacion::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
