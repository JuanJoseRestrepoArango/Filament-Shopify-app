<?php

namespace App\Filament\Resources;

use Exception;
use Filament\Forms;
use Filament\Tables;
use App\Models\Variante;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Services\ShopifyService;
use Filament\Resources\Resource;
use Filament\Forms\Components\Split;
use function Laravel\Prompts\select;
use Filament\Forms\Components\Select;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\VarianteResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\VarianteResource\RelationManagers;

class VarianteResource extends Resource
{
    protected static ?string $model = Variante::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Split::make([
                    Section::make([
                        Select::make('productos_id')
                            ->relationship('productos','nombre')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('nombre')
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('precio')
                            ->numeric(),
                        
                        TextInput::make('stock')
                            ->numeric()
                            ->default(0),
                    ]),
                    Section::make([
                        TextInput::make('shopify_variante_id')
                            ->maxLength(255)
                            ->label('ID de la variante en Shopify')
                            ->readOnly(),
                        TextInput::make('inventario_item_id')
                            ->maxLength(255)
                            ->label('ID de la variante en el Inventario de Shopify')
                            ->readOnly(),
                    ])->grow(false),
                ])->columnSpanFull(),
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('productos_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('precio')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shopify_variante_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('inventario_item_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->before(function($records){
                        foreach($records as $record){
                            if($record->shopify_variante_id){
                                try {
                                    app(ShopifyService::class)->eliminarVariante($record->shopify_variante_id,$record->productos->shopify_id);
                                    Notification::make()
                                    ->title('Shopify Eliminado Correctamente')
                                    ->success()
                                    ->body('Se ha eliminado la variante:' . $record->nombre . ' en Shopify')
                                    ->send();
                                } catch (Exception $e) {
                                    Notification::make()
                                    ->title('Shopify Error al Eliminar')
                                    ->danger()
                                    ->body('No se pudo eliminar la variante: ' . $record->nombre . ' en Shopify' )
                                    ->send();
                                }
                            }
                        }
                    }),
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
            'index' => Pages\ListVariantes::route('/'),
            'create' => Pages\CreateVariante::route('/create'),
            'edit' => Pages\EditVariante::route('/{record}/edit'),
        ];
    }
}
