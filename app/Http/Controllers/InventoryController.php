<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\StockAlert;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\StockAvailableMail; // Importación correcta


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
    
        // Guardar cantidad anterior antes de actualizar
        $previousQuantity = $item->quantity;
    
        // Actualizar
        $item->update($request->all());
    
        // Si antes no había stock y ahora sí, entonces hubo restock
        if ($previousQuantity === 0 && $item->quantity > 0) {
            // Buscar usuarios que pidieron notificación para este ítem
            $alerts = StockAlert::where('item_id', $item->id)->with('user')->get();
    
            foreach ($alerts as $alert) {
                // Enviar el correo (esto ya se irá en cola si tienes ShouldQueue en el Mailable)
                Log::info('Enviando correo a: ' . $alert->user->email);
                Mail::to($alert->user->email)->send(new StockAvailableMail($item));
            }
    
            // Eliminar las alertas una vez que se notificó
            StockAlert::where('item_id', $item->id)->delete();
        }
    
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

    // InventoryController.php

public function subtractStock($itemId, $quantity)
{
    $item = Item::find($itemId);

    if (!$item) {
        return response()->json(['message' => 'Ítem no encontrado'], 404);
    }

    // Verificar si hay suficiente stock
    if ($item->quantity < $quantity) {
        return response()->json(['message' => 'No hay suficiente stock disponible'], 400);
    }

    // Restar la cantidad comprada del stock
    $item->quantity -= $quantity;
    $item->save();

    return response()->json(['message' => 'Stock actualizado correctamente']);
}
}
