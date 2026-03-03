<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OportunidadResource\Pages;
use App\Models\Oportunidad;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Carbon\Carbon;
use Filament\Forms\Get;

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
                    ->default('Pendiente')
                    ->required(),
                Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true),
                Toggle::make('es_recurrente')
                    ->label('¿Repetir compra?')
                    ->inline(false)
                    ->live(),
                TextInput::make('mejora_porcentaje')
                    ->numeric()
                    ->suffix('%')
                    ->default(0)
                    ->visible(fn (Get $get) => $get('es_recurrente')),
                DateTimePicker::make('disponible_desde')
                    ->placeholder('Inmediato')
                    ->visible(fn (Get $get) => $get('es_recurrente')), 
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
                        'Pendiente' => 'warning',
                        'Ejecutada' => 'success',
                        'Cancelada' => 'danger',
                        default => 'gray',
                    }),
                ToggleColumn::make('is_active')->label('Activa'),
                TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->label('Última revisión'),
                IconColumn::make('es_recurrente')
                    ->boolean()
                    ->label('Recurrente')
                    ->trueIcon('heroicon-o-repeat')
                    ->falseIcon('heroicon-o-x-circle'),
                TextColumn::make('mejora_porcentaje')
                    ->suffix('%')
                    ->label('Mejora %'),
                TextColumn::make('disponible_desde')
                    ->label('Disponible desde')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->diffForHumans() : 'Inmediato'),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'Ejecutada' => 'Ejecutada',
                        'Cancelada' => 'Cancelada',
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