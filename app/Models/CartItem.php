<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'item_id', 'quantity'];

    // Relación con el modelo Item
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}