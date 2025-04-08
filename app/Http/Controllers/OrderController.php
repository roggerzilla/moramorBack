<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Notifications\StockInsuficienteNotification;

class OrderController extends Controller
{
    // Obtener la lista de pedidos
    public function index(Request $request)
    {
        // Obtener los parámetros de búsqueda
        $searchId = $request->query('id');
        $searchName = $request->query('name');
        $searchDate = $request->query('date');
    
        // Consulta base
        $orders = Order::with(['user', 'items'])
            ->when($searchId, function ($query, $searchId) {
                return $query->where('id', $searchId);
            })
            ->when($searchName, function ($query, $searchName) {
                return $query->where('customer_name', 'like', "%$searchName%");
            })
            ->when($searchDate, function ($query, $searchDate) {
                return $query->whereDate('created_at', $searchDate);
            })
            ->get();
    
        // Formatear la respuesta
        $formattedOrders = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'customer_name' => $order->customer_name, // Nombre del cliente
                'total' => $order->total,
                'estatus' => $order->estatus, // Incluir el campo estatus
                'created_at' => $order->created_at,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'quantity' => $item->pivot->quantity,
                        'price' => $item->price,
                    ];
                }),
            ];
        });
    
        return response()->json($formattedOrders);
    }

    public function store(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'customer_name' => 'required|string',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
    
        // Iniciar una transacción de base de datos
        DB::beginTransaction();
    
        try {
            // Verificar el stock antes de crear la orden
            foreach ($request->items as $item) {
                $itemModel = Item::find($item['id']);
    
                if ($itemModel->quantity < $item['quantity']) {
                    // Notificar al usuario que no hay suficiente stock
                    $user = User::find($request->user_id);
                    Notification::send($user, new StockInsuficienteNotification($itemModel->name));
    
                    // Revertir la transacción
                    DB::rollBack();
    
                    return response()->json([
                        'message' => 'No hay suficiente stock para el ítem: ' . $itemModel->name,
                    ], 400);
                }
            }
    
            // Calcular el total del pedido
            $total = 0;
            foreach ($request->items as $item) {
                $itemModel = Item::find($item['id']);
                $total += $itemModel->price * $item['quantity'];
            }
    
            // Crear la orden con el estatus "pedido"
            $order = Order::create([
                'user_id' => $request->user_id,
                'customer_name' => $request->customer_name,
                'total' => $total,
                'estatus' => 'pedido', // Establecer el estatus como "pedido"
            ]);
    
            // Asociar los ítems a la orden en la tabla pivote
            foreach ($request->items as $item) {
                $order->items()->attach($item['id'], ['quantity' => $item['quantity']]);
    
                // Restar el stock del inventario
                $itemModel = Item::find($item['id']);
                $itemModel->quantity -= $item['quantity'];
                $itemModel->save();
            }
    
            // Confirmar la transacción
            DB::commit();
    
            // Devolver una respuesta exitosa
            return response()->json([
                'message' => 'Orden creada correctamente',
                'order' => $order->load('items'), // Cargar los ítems asociados
            ], 201);
    
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();
    
            // Devolver un mensaje de error
            return response()->json([
                'message' => 'Error al crear la orden',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        // Validar los datos de entrada
        $request->validate([
            'estatus' => 'required|in:pedido,enviado,cancelado',
        ]);

        // Buscar la orden
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Orden no encontrada'], 404);
        }

        // Actualizar el estatus
        $order->estatus = $request->estatus;
        $order->save();

        return response()->json(['message' => 'Estatus actualizado correctamente']);
    }
}