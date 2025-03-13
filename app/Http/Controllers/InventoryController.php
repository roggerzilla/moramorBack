<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class InventoryController extends Controller
{
    // ✅ Esto puede verlo el Super Usuario y Administradores
    public function getItems()
    {
        return response()->json(Item::all());
    }

    public function getItem($id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        return response()->json($item);
    }

    // ✅ Solo el Super Usuario puede agregar productos
    public function addItem(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'image_url' => 'nullable|url', // Asegúrate de que la URL de la imagen sea válida
        ]);
    
        // Crear el ítem en la base de datos
        $item = Item::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'image_url' => $request->image_url,
        ]);

        return response()->json(['message' => 'Producto agregado exitosamente']);
    }

    // ✅ Solo el Super Usuario puede subir imágenes
    public function uploadImage(Request $request)
    {
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            return response()->json(['path' => $path]);
        }

        return response()->json(['message' => 'No se pudo subir la imagen'], 400);
    }

    // ✅ Solo el Super Usuario puede actualizar productos
    public function updateItem(Request $request, $id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $item->update($request->all());
        return response()->json(['message' => 'Producto actualizado']);
    }

    // ✅ Solo el Super Usuario puede eliminar productos
    public function deleteItem($id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $item->delete();
        return response()->json(['message' => 'Producto eliminado']);
    }
}
