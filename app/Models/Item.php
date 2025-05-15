<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


class Item extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['name', 'description', 'price', 'quantity', 'mililitros']; // Agregar 'mililitros' aquí

    // Relación con imágenes
    public function images()
    {
        return $this->hasMany(ItemImage::class);
    }

    // Relación con carrito
    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'item_id');
    }

    // Relación con órdenes
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_item')->withPivot('quantity');
    }
}
