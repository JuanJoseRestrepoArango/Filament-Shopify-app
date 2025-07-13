<?php

namespace App\Filament\Resources;

use Exception;
use Filament\Forms;
use Filament\Tables;
use App\Models\Producto;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Services\ShopifyService;
use Filament\Resources\Resource;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductoResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use App\Filament\Resources\ProductoResource\RelationManagers;
use Filament\Forms\Components\Section;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    TextInput::make('nombre')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('precio')
                        ->label('Valor Relacionado')
                        ->required()
                        ->prefix('$')
                        ->currencyMask(thousandSeparator: ',',decimalSeparator: '.',precision: 2)             
                        ->required()
                        ->rules(['min:0.0'])->validationMessages(['min' => 'El valor debe ser mayor o igual a 0']),
                    Textarea::make('descripcion')
                        ->columnSpanFull(),
                    TextInput::make('stock_local')
                        ->numeric()
                        ->default(0)
                        ->rules(['min:0.0'])->validationMessages(['min' => 'El valor debe ser mayor o igual a 0']),
                    TextInput::make('shopify_id')
                        ->maxLength(255)
                        ->readOnly(),
                    Toggle::make('activo')
                        ->onColor('success')
                        ->offColor('danger'),
                    TextInput::make('imagen_url')
                        ->maxLength(255),

                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable(),
                TextColumn::make('precio')->label('Precio')
                    ->money('COP')->searchable()->toggleable(),
                TextColumn::make('stock_local')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('shopify_id')
                    ->searchable(),
                IconColumn::make('activo')
                    ->boolean(),
                TextColumn::make('imagen_url')
                    ->searchable()
                    ->default('-'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
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

                            if($record->shopify_id){
                                try {
                                    app(ShopifyService::class)->eliminarProducto($record->shopify_id);

                                    Notification::make()
                                    ->title('Shopify Eliminado Correctamente')
                                    ->success()
                                    ->body('Se ha eliminado el producto:' . $record->nombre . ' en Shopify')
                                    ->send();
                                } catch (Exception $e) {
                                    Notification::make()
                                    ->title('Shopify Error al Eliminar')
                                    ->danger()
                                    ->body('No se pudo eliminar el producto: ' . $record->nombre . ' en Shopify' )
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
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }
}
