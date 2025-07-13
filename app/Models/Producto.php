<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = "productos";

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'stock_local',
        'shopify_id',
        'activo',
        'imagen_url'
    ];

    public function variantes(){
        return $this->hasMany(Variante::class);
    }
    
}
