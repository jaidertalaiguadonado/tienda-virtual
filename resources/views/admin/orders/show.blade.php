<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles del Pedido') }} #{{ $order->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Pedido</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p><strong>ID de Pedido:</strong> {{ $order->id }}</p>
                            <p><strong>Estado:</strong> {{ $order->status }}</p>
                            <p><strong>Total:</strong> ${{ number_format($order->total, 0, ',', '.') }}</p>
                            <p><strong>Fecha de Pedido:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p><strong>Usuario:</strong> {{ $order->user->name ?? 'N/A' }}</p>
                            <p><strong>Email:</strong> {{ $order->user->email ?? 'N/A' }}</p>
                            <p><strong>Ubicación:</strong>
                                @if ($order->user && $order->user->location)
                                    {{ $order->user->location->address ?? 'No disponible' }}
                                    @if ($order->user->location->latitude && $order->user->location->longitude)
                                        <br><small>Lat: {{ $order->user->location->latitude }}, Lng: {{ $order->user->location->longitude }}</small>
                                    @endif
                                @else
                                    No disponible
                                @endif
                            </p>
                            </div>
                    </div>

                    <h4 class="text-md font-medium text-gray-900 mt-8 mb-4">Productos del Pedido</h4>
                    @if ($order->items->isEmpty())
                        <p>No hay productos en este pedido.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Producto
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cantidad
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Precio Unitario
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Subtotal
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($order->items as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $item->product->name ?? 'Producto no disponible' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                ${{ number_format($item->price, 0, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                ${{ number_format($item->quantity * $item->price, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Volver al Listado de Pedidos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>