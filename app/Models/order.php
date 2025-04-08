<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'customer_name',
        'total',
        'estatus', // Agregar estatus
    ];

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con los ítems (a través de la tabla pivote)
    public function items()
    {
        return $this->belongsToMany(Item::class, 'order_item')
            ->withPivot('quantity');
    }
}