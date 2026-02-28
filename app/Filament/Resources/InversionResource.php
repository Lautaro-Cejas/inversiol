<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InversionResource\Pages;
use App\Models\Inversion;
use App\Services\IolService;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class InversionResource extends Resource
{
    protected static ?string $model = Inversion::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $modelLabel = 'Inversión';
    protected static ?string $pluralModelLabel = 'Inversiones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('activo')
                    ->required()
                    ->maxLength(255),
                TextInput::make('cantidad')
                    ->required()
                    ->numeric(),
                TextInput::make('precio_compra')
                    ->required()
                    ->numeric(),
                TextInput::make('precio_actual')
                    ->numeric()
                    ->default(null),
                DatePicker::make('fecha_operacion')
                    ->required(),
                TextInput::make('take_profit_porcentaje')
                    ->label('Take Profit (%)')
                    ->required()
                    ->numeric()
                    ->step('0.1')
                    ->suffix('%'),
                TextInput::make('stop_loss_porcentaje')
                    ->label('Stop Loss (%)')
                    ->required()
                    ->numeric()
                    ->step('0.1')
                    ->suffix('%'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('activo')
                    ->searchable(),
                TextColumn::make('cantidad')
                    ->badge()
                    ->color(fn (string $state): string => $state == 0 ? 'warning' : 'success')
                    ->formatStateUsing(fn (string $state) => $state == 0 ? 'En Liquidación (0)' : $state)
                    ->sortable(),
                TextColumn::make('precio_compra')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('precio_actual')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('fecha_operacion')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('take_profit_porcentaje')
                    ->label('TP %')
                    ->color('success')
                    ->suffix('%'),
                TextColumn::make('stop_loss_porcentaje')
                    ->label('SL %')
                    ->color('danger')
                    ->suffix('%'),
            ])
            ->filters([
                Filter::make('sin_liquidar')
                    ->label('Sin liquidar')
                    ->query(fn ($query) => $query->where('cantidad', '>', 0)),
                Filter::make('liquidado')
                    ->label('Liquidado')
                    ->query(fn ($query) => $query->where('cantidad', 0)),
            ])
            ->actions([
                Action::make('vender_ahora')
                    ->label('Vender')
                    ->icon('heroicon-o-banknotes')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Inversion $record) => "Vender {$record->cantidad}x {$record->activo}")
                    ->modalDescription('Se enviará una orden de venta a precio de mercado (Contado Inmediato) a InvertirOnline. Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, vender ahora')
                    ->action(function (Inversion $record) {
                        $iolService = app(IolService::class);

                        $precioActual = $iolService->getCotizacion($record->activo);

                        if ($precioActual <= 0) {
                            Notification::make()
                                ->title('Error al cotizar')
                                ->body("No se pudo obtener el precio de {$record->activo}.")
                                ->danger()
                                ->send();
                            return;
                        }

                        $respuesta = $iolService->venderActivo($record->activo, $record->cantidad, $precioActual, 't0');

                        if ($respuesta && isset($respuesta['numeroOperacion'])) {
                            $record->update(['cantidad' => 0]); 

                            Notification::make()
                                ->title('¡Venta ejecutada!')
                                ->body("Se mandó la orden por {$record->cantidad} nominales de {$record->activo} a $ {$precioActual}.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Operación rechazada')
                                ->body('IOL rechazó la orden. Revisá si el mercado está abierto o mirá los logs.')
                                ->danger()
                                ->send();
                        }
                    }),
                
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListInversions::route('/'),
            'create' => Pages\CreateInversion::route('/create'),
            'edit' => Pages\EditInversion::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
