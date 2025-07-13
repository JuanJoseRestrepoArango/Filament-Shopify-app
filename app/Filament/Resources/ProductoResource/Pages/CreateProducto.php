<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use Exception;
use Filament\Actions;
use App\Services\ShopifyService;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ProductoResource;

class CreateProducto extends CreateRecord
{
    protected static string $resource = ProductoResource::class;
    

    protected function afterCreate(): void
    {

        $producto = $this->record;

        try {

            $respuesta  = app(ShopifyService::class)->crearProducto([
                'title' => $producto->nombre,
                'body_html' => $producto->descripcion,
                'variants' => [
                    [
                        'price' => $producto->precio ?? 0
                    ]
                    ],
                'images' => $producto->imagen_url ? [['src' => $producto->imagen_url]] : [] ,
            ]);

            DB::transaction(function() use ($producto,$respuesta){
                $producto->update([
                    'shopify_id' => $respuesta['id'],
                ]);
            });

            Notification::make()
            ->title('Shopify Actualizado Correctamente')
            ->success()
            ->body('Se ha guardado el producto en Shopify')
            ->send();

        } catch (Exception $e) {
            Notification::make()
            ->title('Shopify no se actualizo')
            ->danger()
            ->body('Se ha producido un error:' . $e)
            ->send();
        }
    
    
    }
}
