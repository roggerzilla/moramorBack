<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Item;


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
            'customer_name' => 'required|string', // Nombre del cliente
            'items' => 'required|array',
            'items.*.id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);
    
        // Iniciar una transacción de base de datos
        DB::beginTransaction();
    
        try {
            // Calcular el total del pedido
            $total = 0;
            foreach ($request->items as $item) {
                $itemModel = Item::find($item['id']);
                $total += $itemModel->price * $item['quantity'];
            }
    
            // Crear la orden
            $order = Order::create([
                'user_id' => $request->user_id,
                'customer_name' => $request->customer_name, // Guardar el nombre del cliente
                'total' => $total,
            ]);
    
            // Asociar los ítems a la orden en la tabla pivote
            foreach ($request->items as $item) {
                $order->items()->attach($item['id'], ['quantity' => $item['quantity']]);
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
}