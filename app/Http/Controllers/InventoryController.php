<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\StockAlert;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\StockAvailableMail;

class InventoryController extends Controller
{
    // Mostrar todos los productos con sus imágenes
public function getItems()
{
    return response()->json(
        Item::with('images')->get() // Esto ya excluye eliminados si SoftDeletes está bien implementado
    );
}

    public function getItem($id)
    {
        $item = Item::with('images')->find($id);
        if (!$item) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        return response()->json($item);
    }

    // Agregar nuevo producto
    public function addItem(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'mililitros' => 'required|integer|min:0', // Validar mililitros
            'image_urls' => 'nullable|array|max:4',
            'image_urls.*' => 'url'
        ]);
    
        $item = Item::create($request->only(['name', 'description', 'price', 'quantity', 'mililitros'])); // Asegúrate de incluir 'mililitros'
    
        if ($request->has('image_urls')) {
            foreach ($request->image_urls as $url) {
                $item->images()->create(['url' => $url]);
            }
        }
    
        return response()->json(['message' => 'Producto agregado exitosamente']);
    }

    // Subida de imagen individual
    public function uploadImage(Request $request)
    {
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            return response()->json(['path' => asset("storage/$path")]);
        }

        return response()->json(['message' => 'No se pudo subir la imagen'], 400);
    }

    // Actualizar producto
public function updateItem(Request $request, $id)
{
    $item = Item::find($id);
    if (!$item) {
        return response()->json(['message' => 'Producto no encontrado'], 404);
    }

    $previousQuantity = $item->quantity;
    $item->update($request->only(['name', 'description', 'price', 'quantity','mililitros']));

    if ($request->has('image_urls')) {
        // Elimina imágenes antiguas
        $item->images()->delete();

        // Guarda las nuevas imágenes
        foreach ($request->image_urls as $url) {
            $item->images()->create(['url' => $url]);
        }
    }

    // Código para notificar stock (sin cambios)
    if ($previousQuantity === 0 && $item->quantity > 0) {
        $alerts = StockAlert::where('item_id', $item->id)->with('user')->get();

        foreach ($alerts as $alert) {
            \Log::info('Enviando correo a: ' . $alert->user->email);
            \Mail::to($alert->user->email)->send(new StockAvailableMail($item));
        }

        StockAlert::where('item_id', $item->id)->delete();
    }

    return response()->json(['message' => 'Producto actualizado']);
}


    // Eliminar producto
    public function deleteItem($id)
    {
        $item = Item::find($id);
        if (!$item) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $item->delete();
        return response()->json(['message' => 'Producto eliminado']);
    }

    // Restar stock individualmente
    public function subtractStock($itemId, $quantity)
    {
        $item = Item::find($itemId);

        if (!$item) {
            return response()->json(['message' => 'Ítem no encontrado'], 404);
        }

        if ($item->quantity < $quantity) {
            return response()->json(['message' => 'No hay suficiente stock disponible'], 400);
        }

        $item->quantity -= $quantity;
        $item->save();

        return response()->json(['message' => 'Stock actualizado correctamente']);
    }

    // Restar stock en lote
    public function batchSubtractStock(Request $request)
    {
        $items = $request->input('items');

        foreach ($items as $itemData) {
            $item = Item::find($itemData['item_id']);

            if (!$item) continue;

            if ($item->quantity >= $itemData['quantity']) {
                $item->quantity -= $itemData['quantity'];
                $item->save();
            }
        }

        return response()->json(['message' => 'Stock actualizado']);
    }

public function getDeletedItems()
{
    // Asegúrate que el modelo Item tenga el trait SoftDeletes
    $deletedItems = Item::onlyTrashed()->get();

    if ($deletedItems->isEmpty()) {
        return response()->json(['message' => 'No hay productos eliminados'], 404);
    }

    return response()->json($deletedItems);
}
public function restoreItem($id)
{
    $item = Item::withTrashed()->find($id);

    if (!$item || !$item->trashed()) {
        return response()->json(['message' => 'Producto no encontrado o no está eliminado'], 404);
    }

    $item->restore();
    return response()->json(['message' => 'Producto restaurado exitosamente']);
}
public function updateImage(Request $request, $id)
{
    $request->validate([
        'url' => 'required|url'  // Validamos que la URL sea una URL válida
    ]);

    // Buscamos la imagen por su ID
    $image = ItemImage::find($id);

    if (!$image) {
        return response()->json(['message' => 'Imagen no encontrada'], 404);
    }

    // Actualizamos la URL de la imagen
    $image->url = $request->url;
    $image->save();

    return response()->json([
        'message' => 'Imagen actualizada correctamente',
        'image' => $image  // Opcional: devolver los datos actualizados
    ]);
}
}
