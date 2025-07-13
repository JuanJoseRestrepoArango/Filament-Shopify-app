<?php

namespace App\Filament\Resources\VarianteResource\Pages;

use Filament\Actions;
use App\Services\ShopifyService;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\VarianteResource;
use Exception;

class EditVariante extends EditRecord
{
    protected static string $resource = VarianteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(){
        $variante = $this->record;

        $producto = $variante->productos;

        if (!$producto || empty($producto->shopify_id)) {
            Notification::make()
                ->title('Error al sincronizar con Shopify')
                ->danger()
                ->body('La variante no tiene un producto válido asociado o el producto no ha sido sincronizado con Shopify.')
                ->send();
            return;
        }

        try{

            if(!$variante->shopify_variante_id){
                    $respuesta = app(ShopifyService::class)->crearVariante([
                        'option1' => $variante->nombre,
                        'price' => $variante->precio ?? 0,
                        'sku' => 'SKU' . $variante->id,
                    ],$producto->shopify_id);

                    DB::transaction(function() use ($variante,$respuesta){
                        $variante->update([
                            'shopify_variante_id' => $respuesta['id'],
                            'inventario_item_id' => $respuesta['inventory_item_id'],
                        ]);
                    });

                    Notification::make()
                    ->title('Shopify Actualizado Correctamente')
                    ->success()
                    ->body('Se ha guardado la variante en Shopify')
                    ->send();
            }else{
                app(ShopifyService::class)->actualizarVariante($variante->shopify_variante_id,[
                    'option1' => $variante->nombre,
                    'price' => $variante->precio ?? 0,
                    'sku' => 'SKU' . $variante->id,
                ]);

                Notification::make()
                    ->title('Shopify Actualizado Correctamente')
                    ->success()
                    ->body('Se ha guardado la variante en Shopify')
                    ->send();

            }
        }catch(Exception $e){
            Notification::make()
            ->title('Shopify no se actualizo')
            ->danger()
            ->body('Se ha producido un error:' . $e)
            ->send();
        }
    }
}
