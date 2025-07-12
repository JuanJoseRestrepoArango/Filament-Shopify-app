<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class ShopifyService {
    protected string $base_url;
    protected string $token_acceso;

    public function __construct(){
        $this->token_acceso = config('services.shopify.token');
        $this->base_url = 'https://' . config('services.shopify.domain') . '/admin/api/' . config('services.shopify.version');
    }

    protected function headers(){
        return [
            'X-Shopify-Access-Token' => $this->token_acceso,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    protected function manejoError($respuesta){
        if($respuesta->successful()){
            return $respuesta->json();
        }

        throw new Exception('Error en la Api de Shoppify: ' . $respuesta->body());
    }

    //Obtener productos

    public function getProductos(){
        $respuesta = Http::withHeaders($this->headers())->get("{$this->base_url}/products.json");

        return $this->manejoError($respuesta)['products'];
    }

    //obtener Producto por id
    public function getProducto($producto_id){
        $respuesta = Http::withHeaders($this->headers())->get("{$this->base_url}/products/{$producto_id}.json");

        return $this->manejoError($respuesta)['product'];
    }

    //crear Producto
    public function crearProducto($datos){
        $respues = Http::withHeaders($this->headers())->post("{$this->base_url}/products.json",['product' =>$datos]);

        return $this->manejoError($respues)['product'];
    }

    //Actualizar un producto entero 
    public function actualizaProductoEntero($producto_id,$datos){
        $respuesta = Http::withHeaders($this->headers())->put("{$this->base_url}/products/{$producto_id}.json",['product' => $datos]);

        return $this->manejoError($respuesta)['product'];
    }
    //actualizar una parte del producto
    public function ActualizarProductoParte($producto_id,$datos){
        $respuesta = Http::withHeaders($this->headers())->patch("{$this->base_url}/products/{$producto_id}.json",['product' => $datos]);

        return $this->manejoError($respuesta)['product'];
    }

    //eliminar producto 
    public function eliminarProducto($producto_id){
        $respuesta = Http::withHeaders($this->headers())->delete("{$this->base_url}/products/{$producto_id}.json");
        
        return $respuesta->successful();
    }


    //Obtener Variantes
    public function getVariantes($producto_id){
        $respuesta = Http::withHeaders($this->headers())->get("{$this->base_url}/products/{$producto_id}/variants.json");

        return $this->manejoError($respuesta)['variants'];
    }

    //Obtener un item del inventario desde la variante 
    public function getItemInventarioPorVariante($variante_id){
        $respuesta = Http::withHeaders($this->headers())->get("{$this->base_url}/variants/{$variante_id}.json");

        return $this->manejoError($respuesta)['variant']['inventory_item_id'];
    }

    //Avtualizar Inventario 
    public function actualizarInventario($item_id,$ubicacion_id,$nuevaCantidad){
        $respuesta = Http::withHeaders($this->headers())->post("{$this->base_url}/inventory_levels/set.json",[
            'location_id' => $ubicacion_id,
            'inventory_item_id' => $item_id,
            'available' => $nuevaCantidad,
        ]);

        return $this->manejoError($respuesta);
    }

    //incrementar cantidad en inventario
    public function incrementarCantidadInventario($item_id,$ubicacion_id,$cantidad){
        $cantidad_actual = $this->getNivelesInventario($ubicacion_id);
        $nivel = collect($cantidad_actual)->firstWhere('inventory_item_id', $item_id);
        $nuevaCantidad = ($nivel['available'] ?? 0) + $cantidad;

        return $this->actualizarInventario($item_id,$ubicacion_id,$nuevaCantidad);
    }

    //decremento cantidad en inventario 
    public function decrementarCantidadInventario($item_id,$ubicacion_id,$cantidad){
        $cantidad_actual = $this->getNivelesInventario($ubicacion_id);
        $nivel = collect($cantidad_actual)->firstWhere('inventory_item_id', $item_id);
        $nuevaCantidad = max(0,($nivel['available'] ?? 0) - $cantidad);
        
        return $this->actualizarInventario($item_id,$ubicacion_id,$nuevaCantidad);
    }
    //Obtener ubicaciones

    public function getUbicaciones(){
        $respuesta = Http::withHeaders($this->headers())->get("{$this->base_url}/locations.json");

        return $this->manejoError($respuesta)['locations'];
    }

    //Obtener niveles de inventario 

    public function getNivelesInventario($ubicacion_id){
        $respuesta = Http::withHeaders($this->headers())->get("{$this->base_url}/inventory_levels.json?location_ids={$ubicacion_id}");

        return $this->manejoError($respuesta)['inventory_levels'];
    }

    // configuracion variables
    // se deben guardar configurar las variables de entorno en el config/service.php donde se agrega esto  
    // 'shopify' => [
    // 'token' => env('SHOPIFY_ACCESS_TOKEN'),
    // 'domain' => env('SHOPIFY_STORE_DOMAIN'),
    // 'version' => env('SHOPIFY_API_VERSION'),
    // ],


}