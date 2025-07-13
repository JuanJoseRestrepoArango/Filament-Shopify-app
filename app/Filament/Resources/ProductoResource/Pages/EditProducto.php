<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use Exception;
use Filament\Actions;
use App\Services\ShopifyService;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProductoResource;

class EditProducto extends EditRecord
{
    protected static string $resource = ProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(){
        $producto = $this->record;

        try {
            $shopify = new ShopifyService();

            if(!$producto->shopify_id){
                $respuesta = $shopify->crearProducto([
                    'title' => $producto->nombre,
                    'body_html' => $producto->descripcion,
                    'variants' => [
                        [
                            'price' => $producto->precio ?? 0
                        ]
                        ],
                    'images' => $producto->imagen_url ? [['src' => $producto->imagen_url]] : [] ,
                ]);

                DB::transaction(function() use ($producto,$respuesta) {
                    $producto->update([
                        'shopify_id' => $respuesta['id'],
                    ]);
                });

                Notification::make()
                    ->title('Shopify Actualizado Correctamente')
                    ->success()
                    ->body('Se ha guardado el producto en Shopify')
                    ->send();
            }else{

                $shopify->actualizaProductoEntero($producto->shopify_id, [
                    'title' => $producto->nombre,
                    'body_html' => $producto->descripcion,
                    'variants' => [
                        [
                            'price' => $producto->precio ?? 0
                        ]
                        ],
                    'images' => $producto->imagen_url ? [['src' => $producto->imagen_url]] : [] ,
                ]);

                Notification::make()
                    ->title('Shopify Actualizado Correctamente')
                    ->success()
                    ->body('Se ha actualizado el producto en Shopify')
                    ->send();

            }

        } catch (Exception $e) {
            Notification::make()
            ->title('Shopify no se actualizo')
            ->danger()
            ->body('Se ha producido un error:' . $e)
            ->send();
        }
    }

}
