<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CartItem;
use App\Models\Cart;
use App\Models\Item; // Cambiado de Product a Item

class CartController extends Controller
{
    // Obtener los ítems del carrito del usuario autenticado
    public function getCartItems()
    {
        $user = Auth::user();

        // Obtener los ítems del carrito con la información del ítem
        $cartItems = CartItem::where('user_id', $user->id)
            ->with('item') // Cargar la relación con el ítem
            ->get();

        return response()->json($cartItems);
    }

    // Agregar un ítem al carrito
    public function addToCart(Request $request)
    {
        $user = Auth::user();

        // Validar los datos de entrada
        $request->validate([
            'item_id' => 'required|exists:items,id', // Cambiado de product_id a item_id
            'quantity' => 'required|integer|min:1',
        ]);

        // Verificar si el ítem ya está en el carrito
        $cartItem = CartItem::where('user_id', $user->id)
            ->where('item_id', $request->item_id) // Cambiado de product_id a item_id
            ->first();

        if ($cartItem) {
            // Si el ítem ya está en el carrito, actualizar la cantidad
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            // Si el ítem no está en el carrito, crear un nuevo ítem
            CartItem::create([
                'user_id' => $user->id,
                'item_id' => $request->item_id, // Cambiado de product_id a item_id
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json(['message' => 'Ítem agregado al carrito']);
    }

    // Actualizar la cantidad de un ítem en el carrito
    public function updateCartItem(Request $request, $id)
    {
        $user = Auth::user();

        // Validar los datos de entrada
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Buscar el ítem del carrito
        $cartItem = CartItem::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Ítem del carrito no encontrado'], 404);
        }

        // Actualizar la cantidad
        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json(['message' => 'Cantidad actualizada correctamente']);
    }

    // Eliminar un ítem del carrito
    public function removeFromCart($id)
    {
        $user = Auth::user();

        // Buscar el ítem del carrito
        $cartItem = CartItem::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Ítem del carrito no encontrado'], 404);
        }

        // Eliminar el ítem del carrito
        $cartItem->delete();

        return response()->json(['message' => 'Ítem eliminado del carrito']);
    }
    public function clearCart()
    {
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }
    
        // Usar el mismo modelo que removeFromCart()
        CartItem::where('user_id', $user->id)->delete();
    
        return response()->json(['message' => 'Carrito vaciado correctamente'], 200);
    }
    
}