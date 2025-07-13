<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variante extends Model
{
    protected $table = 'variantes';

    protected $fillable = [
        'productos_id',
        'nombre',
        'precio',
        'shopify_variante_id',
        'inventario_item_id',
        'stock'
    ];

    public function productos(){
        return $this->belongsTo(Producto::class,'productos_id');
    }

}
