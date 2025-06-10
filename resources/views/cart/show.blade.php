<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Estilos básicos para que los elementos sean visibles, ajusta según tu CSS */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .navbar {
            background-color: #333;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar-links {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }
        .navbar-links li a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
        }
        .menu-toggle {
            display: none; /* Ocultar en desktop */
            font-size: 1.5rem;
            cursor: pointer;
        }
        .navbar-links-mobile {
            display: none; /* Ocultar por defecto */
            flex-direction: column;
            background-color: #444;
            position: absolute;
            top: 60px; /* Ajusta según la altura de tu navbar */
            left: 0;
            width: 100%;
            z-index: 1000;
        }
        .navbar-links-mobile.active {
            display: flex;
        }
        .navbar-links-mobile li a {
            padding: 1rem;
            border-bottom: 1px solid #555;
            text-align: center;
        }
        .cart-icon {
            position: relative;
            margin-left: 20px;
        }
        .cart-icon span {
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
            position: absolute;
            top: -5px;
            right: -10px;
        }
        .cart-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .cart-table th, .cart-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .cart-table th {
            background-color: #f2f2f2;
        }
        .cart-table img {
            max-width: 80px;
            height: auto;
            border-radius: 4px;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
        }
        .quantity-controls button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
            margin: 0 5px;
        }
        .quantity-controls button:hover {
            opacity: 0.9;
        }
        .quantity-input {
            width: 50px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        .remove-button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 4px;
        }
        .remove-button:hover {
            opacity: 0.9;
        }
        .cart-summary {
            margin-top: 2rem;
            text-align: right;
            border-top: 1px solid #eee;
            padding-top: 1rem;
        }
        .cart-summary p {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .empty-cart-message {
            text-align: center;
            font-size: 1.2rem;
            color: #777;
            margin-top: 50px;
            display: none; /* Hidden by default, JS controls visibility */
        }
        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        .cart-actions a, .cart-actions button {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .continue-shopping {
            background-color: #6c757d;
            color: white;
            border: none;
        }
        .continue-shopping:hover {
            background-color: #5a6268;
        }
        .checkout-button {
            background-color: #28a745;
            color: white;
            border: none;
        }
        .checkout-button:hover {
            background-color: #218838;
        }
        /* Media Queries para responsividad */
        @media (max-width: 768px) {
            .navbar-links {
                display: none;
            }
            .menu-toggle {
                display: block;
            }
            .cart-table, .cart-summary {
                padding: 0 1rem;
            }
            .cart-table th, .cart-table td {
                padding: 8px;
                font-size: 0.9rem;
            }
            .cart-table thead {
                display: none; /* Ocultar encabezado en móvil */
            }
            .cart-table tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #ddd;
                border-radius: 8px;
            }
            .cart-table td {
                display: block;
                text-align: right;
                border: none;
                position: relative;
                padding-left: 50%;
            }
            .cart-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: bold;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="/" class="site-title">Mi Tienda</a>
        <div class="menu-toggle">☰</div>
        <ul class="navbar-links">
            <li><a href="/">Inicio</a></li>
            <li><a href="/productos">Productos</a></li>
            <li><a href="/contacto">Contacto</a></li>
            <li class="cart-icon">
                <a href="/carrito">Carrito <span id="cart-item-count">0</span></a>
            </li>
        </ul>
        <ul class="navbar-links-mobile">
            <li><a href="/">Inicio</a></li>
            <li><a href="/productos">Productos</a></li>
            <li><a href="/contacto">Contacto</a></li>
            <li class="cart-icon">
                <a href="/carrito">Carrito <span id="cart-item-count-mobile">0</span></a>
            </li>
        </ul>
    </nav>

    <div class="cart-container">
        <h1>Tu Carrito de Compras</h1>

        <div class="empty-cart-message" style="display: {{ empty($cartItems) ? 'block' : 'none' }}">
            Tu carrito está vacío. ¡Empieza a agregar productos!
        </div>

        <table class="cart-table" style="display: {{ empty($cartItems) ? 'none' : 'table' }}">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="cart-items-body">
                @forelse ($cartItems as $item)
                    <tr data-product-id="{{ $item['product_id'] }}">
                        <td data-label="Producto">
                            <div style="display: flex; align-items: center;">
                                <img src="{{ $item['image_url'] ?? asset('images/default_product.png') }}" alt="{{ $item['name'] }}" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                {{ $item['name'] }}
                            </div>
                        </td>
                        <td data-label="Cantidad">
                            <div class="quantity-controls">
                                <button class="decrease-quantity" data-product-id="{{ $item['product_id'] }}">-</button>
                                <input type="number" class="quantity-input" value="{{ $item['quantity'] }}" min="0" data-product-id="{{ $item['product_id'] }}">
                                <button class="increase-quantity" data-product-id="{{ $item['product_id'] }}">+</button>
                            </div>
                        </td>
                        <td data-label="Precio Unitario">${{ number_format($item['price'], 2, ',', '.') }}</td>
                        <td data-label="Subtotal" class="item-subtotal">${{ number_format($item['subtotal'], 2, ',', '.') }}</td>
                        <td data-label="Acciones">
                            <button class="remove-button" data-product-id="{{ $item['product_id'] }}">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    {{-- No es necesario poner nada aquí, el mensaje de carrito vacío se maneja con CSS/JS --}}
                @endforelse
            </tbody>
        </table>

        <div class="cart-summary" style="display: {{ empty($cartItems) ? 'none' : 'block' }}">
            <p>Total: $<span id="cart-total">{{ number_format($total, 2, ',', '.') }}</span></p>
        </div>

        <div class="cart-actions" style="display: {{ empty($cartItems) ? 'none' : 'flex' }}">
            <a href="/productos" class="continue-shopping">Seguir Comprando</a>
            <form id="mercadopago-checkout-form" action="{{ route('mercadopago.create_preference') }}" method="POST">
                @csrf
                <input type="hidden" name="amount" id="mercadopago-amount" value="{{ $total }}">
                <input type="hidden" name="description" id="mercadopago-description" value="Compra de productos en Mi Tienda">
                <button type="submit" class="checkout-button">Pagar con Mercado Pago</button>
            </form>
        </div>
        {{-- Este botón es solo para cuando el carrito está vacío y no hay acciones de pago --}}
        <div class="cart-actions" style="display: {{ empty($cartItems) ? 'flex' : 'none' }}">
            <a href="/productos" class="continue-shopping">Seguir Comprando</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const mobileMenu = document.querySelector('.navbar-links-mobile');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const cartItemCountElement = document.getElementById('cart-item-count');
            const cartItemCountMobileElement = document.getElementById('cart-item-count-mobile');
            const cartItemsBody = document.getElementById('cart-items-body');
            const cartTable = document.querySelector('.cart-table'); // Añadir referencia a la tabla
            const cartTotalElement = document.getElementById('cart-total');
            const cartSummary = document.querySelector('.cart-summary');
            const emptyCartMessage = document.querySelector('.empty-cart-message');
            const cartActionsContainer = document.querySelector('.cart-actions'); // Nuevo: Contenedor de botones

            // Elementos del formulario de Mercado Pago
            const mercadopagoAmountInput = document.getElementById('mercadopago-amount');
            const mercadopagoDescriptionInput = document.getElementById('mercadopago-description');
            const mercadopagoCheckoutForm = document.getElementById('mercadopago-checkout-form');


            // Función para alternar el menú móvil
            if (menuToggle && mobileMenu) {
                menuToggle.addEventListener('click', function() {
                    mobileMenu.classList.toggle('active');
                });
            }

            // Función para formatear el número para la UI (usando coma para decimales y punto para miles)
            function formatNumberForUI(number) {
                return parseFloat(number).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // Función para obtener el número de ítems del carrito desde la sesión/DB
            function getCartCount() {
                fetch('/api/cart-count', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (cartItemCountElement) {
                        cartItemCountElement.textContent = data.cartCount;
                    }
                    if (cartItemCountMobileElement) {
                        cartItemCountMobileElement.textContent = data.cartCount;
                    }
                })
                .catch(error => {
                    console.error('Error al obtener el contador del carrito:', error);
                    if (cartItemCountElement) cartItemCountElement.textContent = '0';
                    if (cartItemCountMobileElement) cartItemCountMobileElement.textContent = '0';
                });
            }

            // Llama a la función al cargar la página para inicializar el contador
            getCartCount();

            // Función para actualizar el subtotal de un ítem y el total general
            function updateCartUI(productId, newQuantity, newSubtotal, newTotal, cartCount) {
                const row = cartItemsBody.querySelector(`tr[data-product-id="${productId}"]`);
                const cartIsEmpty = cartCount === 0;

                if (newQuantity <= 0) { // Si la cantidad es 0 o menos, el ítem se ha eliminado
                    if (row) {
                        row.remove(); // Eliminar la fila del DOM
                    }
                } else if (row) { // Si el ítem aún existe y tiene cantidad > 0
                    row.querySelector('.quantity-input').value = newQuantity;
                    row.querySelector('.item-subtotal').textContent = '$' + formatNumberForUI(newSubtotal);
                }

                // Actualizar el total general, incluso si el carrito está vacío (mostrar 0.00)
                if (cartTotalElement) {
                    cartTotalElement.textContent = formatNumberForUI(newTotal);
                    // Actualiza el hidden input del formulario de Mercado Pago
                    if (mercadopagoAmountInput) {
                        mercadopagoAmountInput.value = newTotal; // Asegura que el valor sea un número flotante
                    }
                }


                // Manejar la visibilidad del resumen del carrito y el mensaje de carrito vacío
                if (cartIsEmpty) {
                    cartItemsBody.innerHTML = ''; // Asegurarse de que no queden ítems visualmente

                    if (cartTable) { // Oculta la tabla completa
                        cartTable.style.display = 'none';
                    }
                    if (cartSummary) { // Oculta el resumen del total
                        cartSummary.style.display = 'none';
                    }
                    if (emptyCartMessage) { // Muestra el mensaje de carrito vacío
                        emptyCartMessage.style.display = 'block';
                    }
                    // Oculta el contenedor de acciones si el carrito está vacío
                    if (cartActionsContainer) {
                        cartActionsContainer.style.display = 'none';
                    }
                    // Muestra el botón "Seguir Comprando" que está fuera del cart-actions para el carrito vacío
                    document.querySelector('.cart-container > .cart-actions:last-of-type').style.display = 'flex';


                } else {
                    if (cartTable) { // Muestra la tabla completa
                        cartTable.style.display = 'table'; // Usar 'table' para tablas
                    }
                    if (cartSummary) { // Muestra el resumen del total
                        cartSummary.style.display = 'block';
                    }
                    if (emptyCartMessage) { // Oculta el mensaje de carrito vacío
                        emptyCartMessage.style.display = 'none';
                    }
                    // Muestra el contenedor de acciones si el carrito tiene ítems
                    if (cartActionsContainer) {
                        cartActionsContainer.style.display = 'flex';
                    }
                    // Oculta el botón "Seguir Comprando" duplicado
                    document.querySelector('.cart-container > .cart-actions:last-of-type').style.display = 'none';

                }
            }


            // Manejar cambios de cantidad y eliminación de ítems
            cartItemsBody.addEventListener('click', function(event) {
                const target = event.target;
                const productId = target.dataset.productId;

                if (!productId) return; // No es un botón de cantidad o eliminación

                // Incrementar o decrementar cantidad
                if (target.classList.contains('increase-quantity') || target.classList.contains('decrease-quantity')) {
                    const quantityInput = target.closest('.quantity-controls').querySelector('.quantity-input');
                    let newQuantity = parseInt(quantityInput.value);

                    if (target.classList.contains('increase-quantity')) {
                        newQuantity++;
                    } else if (target.classList.contains('decrease-quantity')) {
                        newQuantity--;
                    }

                    if (newQuantity < 1) {
                        if (!confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
                            return; // Si cancela, no hagas nada
                        }
                    }

                    // Enviar petición AJAX para actualizar
                    fetch('{{ route('cart.update') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            quantity: newQuantity
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw new Error(err.message || 'Error al actualizar la cantidad.'); });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Cantidad actualizada:', data);
                        // Asegúrate de pasar data.cartCount a updateCartUI
                        updateCartUI(productId, data.item.quantity, data.item.subtotal, data.total, data.cartCount);
                        getCartCount(); // Actualizar contador del navbar
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Hubo un error al actualizar la cantidad: ' + error.message);
                    });
                }

                // Eliminar ítem
                if (target.classList.contains('remove-button')) {
                    if (!confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
                        return;
                    }

                    fetch('{{ route('cart.remove') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ product_id: productId })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw new Error(err.message || 'Error al eliminar el producto.'); });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Producto eliminado:', data);
                        // No es necesario eliminar la fila aquí, updateCartUI lo hará si newQuantity <= 0 (o si se pasa cartCount = 0)
                        updateCartUI(productId, 0, 0, data.total, data.cartCount); // Pasar 0 para que elimine la fila y recalcule
                        getCartCount(); // Actualizar contador del navbar
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Hubo un error al eliminar el producto: ' + error.message);
                    });
                }
            });

            // Manejar cambio manual en el input de cantidad
            cartItemsBody.addEventListener('change', function(event) {
                const target = event.target;
                if (target.classList.contains('quantity-input')) {
                    const productId = target.dataset.productId;
                    let newQuantity = parseInt(target.value);

                    if (isNaN(newQuantity) || newQuantity < 0) {
                        newQuantity = 1; // Restablecer a 1 si es inválido
                        target.value = 1;
                    }

                    if (newQuantity === 0) {
                        if (!confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
                            target.value = 1; // Restablecer si cancela
                            return;
                        }
                    }

                    fetch('{{ route('cart.update') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            quantity: newQuantity
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw new Error(err.message || 'Error al actualizar la cantidad.'); });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Cantidad actualizada:', data);
                        // Asegúrate de pasar data.cartCount a updateCartUI
                        updateCartUI(productId, data.item.quantity, data.item.subtotal, data.total, data.cartCount);
                        getCartCount(); // Actualizar contador del navbar
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Hubo un error al actualizar la cantidad: ' + error.message);
                    });
                }
            });

            // Actualizar la descripción del formulario de Mercado Pago si cambia
            if (mercadopagoDescriptionInput) {
                // Podrías tener una lógica más sofisticada aquí si quieres una descripción dinámica.
                // Por ahora, es un valor fijo.
                // mercadopagoDescriptionInput.value = "Compra de varios productos en Tienda JD";
            }

            // Asegurarse de que el botón de pago esté oculto si el carrito está vacío al cargar la página
            updateCartUI(null, null, null, {{ $total ?? 0 }}, {{ empty($cartItems) ? 0 : count($cartItems) }});
        });
    </script>
</body>
</html>