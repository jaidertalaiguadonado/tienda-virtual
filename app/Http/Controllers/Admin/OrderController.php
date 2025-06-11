<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order; // Asegúrate de importar el modelo Order
use App\Models\User; // Asegúrate de importar el modelo User si lo vas a usar
use App\Models\Product; // Asegúrate de importar el modelo Product si lo vas a usar
use App\Models\OrderItem; // Asegúrate de importar el modelo OrderItem si lo usas
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource (Listado de pedidos).
     */
    public function index()
    {
        // Obtener todos los pedidos con sus usuarios relacionados y la ubicación del usuario
        // Pagina los resultados para evitar cargar demasiados pedidos a la vez.
        // Asegúrate de que Order tiene una relación 'user' y User tiene una relación 'location'.
        $orders = Order::with(['user.location'])->latest()->paginate(10);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Display the specified resource (Detalles de un pedido específico).
     */
    public function show(Order $order)
    {
        // Carga el pedido con sus ítems de pedido y los productos asociados a esos ítems.
        // También carga el usuario relacionado y su ubicación.
        // Asegúrate de que Order tiene una relación 'items', OrderItem tiene una relación 'product',
        // y User tiene una relación 'location'.
        $order->load(['items.product', 'user.location']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for creating a new resource (Normalmente no se crea un pedido manualmente desde el admin).
     */
    public function create()
    {
        // Puedes redirigir o mostrar un error si no quieres que se creen pedidos manualmente.
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Normalmente no se almacenan pedidos desde aquí.
        abort(404);
    }


    /**
     * Show the form for editing the specified resource (Editar un pedido).
     */
    public function edit(Order $order)
    {
        // Si necesitas una vista para editar el estado del pedido, por ejemplo.
        return view('admin.orders.edit', compact('order'));
    }

    /**
     * Update the specified resource in storage (Actualizar un pedido, ej. cambiar estado).
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled', // Ajusta los estados según tu app
        ]);

        $order->status = $request->status;
        $order->save();

        return redirect()->route('admin.orders.index')->with('success', 'Estado del pedido actualizado.');
    }

    /**
     * Remove the specified resource from storage (Eliminar un pedido).
     */
    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Pedido eliminado correctamente.');
    }
}