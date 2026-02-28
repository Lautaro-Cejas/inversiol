<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OportunidadResource\Pages;
use App\Models\Oportunidad;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;

class OportunidadResource extends Resource
{
    protected static ?string $model = Oportunidad::class;
    protected static ?string $modelLabel = 'Oportunidad';
    protected static ?string $pluralModelLabel = 'Oportunidades';
    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('simbolo')
                    ->required()
                    ->maxLength(10)
                    ->placeholder('Ej: SPY')
                    ->extraInputAttributes(['style' => 'text-transform:uppercase']),
                TextInput::make('precio_gatillo')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('cantidad')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Select::make('estado')
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'Ejecutada' => 'Ejecutada',
                        'Cancelada' => 'Cancelada',
                    ])
                    ->default('pendiente')
                    ->required(),
                Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('simbolo')->searchable()->sortable(),
                TextColumn::make('precio_gatillo')->money('ARS')->sortable(),
                TextColumn::make('cantidad'),
                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'ejecutada' => 'success',
                        'cancelada' => 'danger',
                        default => 'gray',
                    }),
                ToggleColumn::make('is_active')->label('Activa'),
                TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->label('Última revisión'),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'ejecutada' => 'Ejecutada',
                        'cancelada' => 'Cancelada',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
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
            'index' => Pages\ListOportunidads::route('/'),
            'create' => Pages\CreateOportunidad::route('/create'),
            'edit' => Pages\EditOportunidad::route('/{record}/edit'),
        ];
    }

    public static function canDelete($record): bool
    {        
        return true;
    }
}
