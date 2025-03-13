<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['name', 'description', 'price', 'quantity', 'image_url'];

    // Relación con el modelo CartItem
    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'item_id');
    }
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_item')
            ->withPivot('quantity');
    }
}