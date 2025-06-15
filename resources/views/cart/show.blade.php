<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- ¡Importante para las peticiones AJAX! --}}
    <title>{{ config('app.name', 'Mi Tienda') }}</title>
    {{-- Aquí podrías enlazar tus hojas de estilo CSS --}}
    <style>
        /* Estilos básicos para la demostración */
        body {
            font-family: sans-serif;
            margin: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .navbar {
            background-color: #333;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar-brand {
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .navbar-links {
            display: flex;
            gap: 1.5rem;
        }
        .navbar-links-mobile {
            display: none; /* Oculto por defecto, se muestra con JS en pantallas pequeñas */
            flex-direction: column;
            position: absolute;
            top: 60px; /* Ajustar según la altura de tu navbar */
            left: 0;
            width: 100%;
            background-color: #444;
            padding: 1rem 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        .navbar-links-mobile.active {
            display: flex;
        }
        .navbar-link, .logout-button-navbar {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .navbar-link:hover, .logout-button-navbar:hover {
            background-color: #555;
        }
        .logout-button-navbar {
            padding: 0; /* Para que no tenga padding extra si es solo texto */
            text-align: left; /* Alinea el texto a la izquierda en el menú móvil */
        }
        .menu-toggle {
            display: none; /* Oculto por defecto, se muestra en pantallas pequeñas */
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
        }

        .cart-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 1.5rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .cart-title {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 2rem;
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        .cart-table th, .cart-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        .cart-table th {
            background-color: #f8f8f8;
            font-weight: bold;
            color: #555;
        }
        .cart-table td {
            vertical-align: middle;
        }
        .cart-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 10px;
            vertical-align: middle;
        }
        .cart-item-name {
            font-weight: 500;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .quantity-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: background-color 0.2s;
        }
        .quantity-button:hover {
            background-color: #0056b3;
        }
        .quantity-input {
            width: 50px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            text-align: center;
            -moz-appearance: textfield; /* Para Firefox */
        }
        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .remove-item, .remove-button { /* Mantengo ambos por si el CSS se usaba con ambos */
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .remove-item:hover, .remove-button:hover {
            background-color: #c82333;
        }

        .cart-summary {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
            border-top: 1px solid #eee;
            padding-top: 1.5rem;
            margin-top: 1.5rem;
        }
        .cart-summary p {
            margin: 0;
            font-size: 1.1rem;
            color: #555;
        }
        .cart-summary .final-total-row {
            font-size: 1.4rem;
            color: #333;
            font-weight: bold;
            border-top: 2px solid #007bff;
            padding-top: 10px;
            width: 100%;
            text-align: right;
        }

        .empty-cart-message {
            text-align: center;
            font-size: 1.2rem;
            color: #777;
            padding: 3rem 0;
        }

        .cart-actions, .empty-cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            flex-wrap: wrap; /* Permite que los botones se envuelvan en pantallas pequeñas */
            gap: 1rem; /* Espacio entre los botones */
        }
        .cart-actions.empty-cart-actions {
            justify-content: center; /* Centra el botón cuando el carrito está vacío */
        }

        .continue-shopping-button, .mercadopago-pay-button {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            text-decoration: none; /* Para el anchor */
            text-align: center;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .continue-shopping-button {
            background-color: #6c757d;
            color: white;
        }
        .continue-shopping-button:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        .mercadopago-pay-button {
            background-color: #009ee3; /* Color de Mercado Pago */
            color: white;
        }
        .mercadopago-pay-button:hover {
            background-color: #007bb6;
            transform: translateY(-2px);
        }

        .footer {
            text-align: center;
            padding: 1.5rem;
            margin-top: 3rem;
            background-color: #333;
            color: white;
            font-size: 0.9rem;
        }

        /* Media Queries para diseño responsivo */
        @media (max-width: 768px) {
            .navbar-links {
                display: none; /* Oculta el menú grande */
            }
            .menu-toggle {
                display: block; /* Muestra el botón de hamburguesa */
            }
            .cart-table thead {
                display: none; /* Oculta el encabezado de la tabla en móvil */
            }
            .cart-table, .cart-table tbody, .cart-table tr, .cart-table td {
                display: block;
                width: 100%;
            }
            .cart-table tr {
                margin-bottom: 1rem;
                border: 1px solid #ddd;
                display: flex;
                flex-wrap: wrap;
                padding: 10px;
                border-radius: 8px;
            }
            .cart-table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
                border: none;
                width: 100%; /* Cada celda ocupa todo el ancho */
            }
            .cart-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                width: calc(50% - 20px);
                text-align: left;
                font-weight: bold;
                color: #555;
            }
            .cart-item-image {
                float: left; /* Alinea la imagen a la izquierda */
                margin-bottom: 10px;
            }
            .cart-item-name {
                display: block;
                text-align: left;
                margin-left: 70px; /* Espacio para la imagen */
            }
            .quantity-controls {
                justify-content: flex-end; /* Alinea los controles de cantidad a la derecha */
            }
            .cart-summary {
                align-items: center; /* Centra el resumen en móvil */
                text-align: center;
            }
            .cart-summary .final-total-row {
                text-align: center;
            }
            .cart-actions {
                flex-direction: column; /* Apila los botones de acción */
                align-items: center;
            }
            .continue-shopping-button, .mercadopago-pay-button {
                width: 100%; /* Botones de ancho completo */
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="{{ route('welcome') }}" class="navbar-brand">{{ config('app.name', 'Mi Tienda') }}</a>
        <button class="menu-toggle" aria-label="Toggle navigation menu">
            ☰
        </button>
        <div class="navbar-links">
            <a href="{{ route('cart.show') }}" class="navbar-link">
                Carrito (<span id="cart-item-count">0</span>)
            </a>
            @auth
                @if (Auth::user()?->isAdmin())
                    <a href="{{ url('/dashboard') }}" class="navbar-link">Dashboard</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-button-navbar">
                        {{ __('Cerrar Sesión') }}
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="navbar-link">Iniciar Sesión</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="navbar-link">Registrarse</a>
                @endif
            @endauth
        </div>

        {{-- Menú móvil --}}
        <div class="navbar-links-mobile">
            <a href="{{ route('cart.show') }}" class="navbar-link">
                Carrito (<span id="cart-item-count-mobile">0</span>)
            </a>
            @auth
                @if (Auth::user()?->isAdmin())
                    <a href="{{ url('/dashboard') }}" class="navbar-link">Dashboard</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-button-navbar">
                        {{ __('Cerrar Sesión') }}
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="navbar-link">Iniciar Sesión</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="navbar-link">Registrarse</a>
                @endif
            @endauth
        </div>
    </nav>

    <div class="cart-container">
        <h1 class="cart-title">Tu Carrito de Compras</h1>

        {{-- La tabla del carrito se mostrará/ocultará con JavaScript --}}
        <table class="cart-table" style="display: {{ empty($cartItems) ? 'none' : 'table' }};">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio Unitario (IVA Inc.)</th>
                    <th>Cantidad</th>
                    <th>Subtotal (IVA Inc.)</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="cart-items-body">
                @foreach($cartItems as $item)
                    {{-- AQUÍ SE HAN CAMBIADO LOS ACCESOS DE ARRAY A OBJETO --}}
                    <tr data-id="{{ $item->id }}"> 
                        <td data-label="Producto:">
                            <img src="{{ asset($item->image_url) }}" alt="{{ $item->name }}" class="cart-item-image">
                            <span class="cart-item-name">{{ $item->name }}</span>
                        </td>
                        {{-- Utiliza $item->subtotal_item_gross / $item->quantity para el precio unitario bruto --}}
                        <td data-label="Precio Unitario:">${{ number_format($item->subtotal_item_gross / $item->quantity, 2, ',', '.') }}</td>
                        <td data-label="Cantidad:">
                            <div class="quantity-controls">
                                <button type="button" class="quantity-button decrease-quantity" data-id="{{ $item->id }}">-</button>
                                <input type="number" value="{{ $item->quantity }}" min="0" class="quantity-input item-quantity-input" data-id="{{ $item->id }}">
                                <button type="button" class="quantity-button increase-quantity" data-id="{{ $item->id }}">+</button>
                            </div>
                        </td>
                        <td class="item-subtotal-gross" data-id="{{ $item->id }}" data-label="Subtotal:">${{ number_format($item->subtotal_item_gross, 2, ',', '.') }}</td>
                        <td data-label="Acción:">
                            {{-- Clase corregida para el JavaScript, usando 'remove-item' --}}
                            <button type="button" class="remove-item" data-id="{{ $item->id }}">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- El resumen del carrito se mostrará/ocultará con JavaScript --}}
        <div class="cart-summary" style="display: {{ empty($cartItems) ? 'none' : 'flex' }};">
            <p>Subtotal Productos (sin IVA): $<span id="subtotal_net_products">{{ number_format($subtotal_net_products, 2, ',', '.') }}</span></p>
            <p>IVA Productos ({{ \App\Http\Controllers\CartController::IVA_RATE * 100 }}%): $<span id="iva_products_amount">{{ number_format($iva_products_amount, 2, ',', '.') }}</span></p>
            <p>Subtotal Productos (con IVA): $<span id="subtotal_gross_products">{{ number_format($subtotal_gross_products, 2, ',', '.') }}</span></p>
            <p>Comisión Mercado Pago: $<span id="mp_fee_amount">{{ number_format($mp_fee_amount, 2, ',', '.') }}</span></p>
            <p class="final-total-row"><strong>Total Final a Pagar: $<span id="final_total">{{ number_format($final_total, 2, ',', '.') }}</span></strong></p>
        </div>

        {{-- Mensaje de carrito vacío --}}
        <p class="empty-cart-message" style="display: {{ empty($cartItems) ? 'block' : 'none' }};">Tu carrito está vacío. ¡Empieza a añadir productos!</p>

        {{-- Acciones del carrito (Pagar, Seguir Comprando) - se ocultan/muestran con JS --}}
        <div class="cart-actions" style="display: {{ empty($cartItems) ? 'none' : 'flex' }};">
            <a href="{{ route('welcome') }}" class="continue-shopping-button">Seguir Comprando</a>

            <form action="{{ route('mercadopago.pay') }}" method="POST" id="mercadopago-checkout-form">
                @csrf
                <input type="hidden" name="total_amount" id="mercadopago-amount" value="{{ $final_total }}">
                <input type="hidden" name="description" id="mercadopago-description" value="Compra en Tienda JD">
                <button type="submit" class="mercadopago-pay-button">Pagar con Mercado Pago</button>
            </form>
        </div>
        
        {{-- ESTE DIV ES SOLO PARA EL BOTÓN "SEGUIR COMPRANDO" CUANDO EL CARRITO ESTÁ VACÍO --}}
        <div class="empty-cart-actions" style="display: {{ empty($cartItems) ? 'flex' : 'none' }}; justify-content: center;">
            <a href="{{ route('welcome') }}" class="continue-shopping-button">Seguir Comprando</a>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Mi Tienda') }}. Todos los derechos reservados.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const mobileMenu = document.querySelector('.navbar-links-mobile');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const cartItemCountElement = document.getElementById('cart-item-count');
            const cartItemCountMobileElement = document.getElementById('cart-item-count-mobile');
            
            // Elementos de la tabla y resumen del carrito
            const cartItemsBody = document.getElementById('cart-items-body');
            const cartTable = document.querySelector('.cart-table');
            const cartSummary = document.querySelector('.cart-summary');
            const emptyCartMessage = document.querySelector('.empty-cart-message');
            const cartActionsContainer = document.querySelector('.cart-actions');
            const emptyCartActionsContainer = document.querySelector('.empty-cart-actions');

            // Elementos del desglose de totales
            const subtotalNetProductsElement = document.getElementById('subtotal_net_products');
            const ivaProductsAmountElement = document.getElementById('iva_products_amount');
            const subtotalGrossProductsElement = document.getElementById('subtotal_gross_products');
            const mpFeeAmountElement = document.getElementById('mp_fee_amount');
            const finalTotalElement = document.getElementById('final_total');

            const mercadopagoAmountInput = document.getElementById('mercadopago-amount');
            const mercadopagoDescriptionInput = document.getElementById('mercadopago-description');
            
            // Toggle para el menú móvil
            if (menuToggle && mobileMenu) {
                menuToggle.addEventListener('click', function() {
                    mobileMenu.classList.toggle('active');
                });
            }
            
            // Función para formatear números para la interfaz de usuario
            function formatNumberForUI(number) {
                // Asegúrate de que el número sea un float antes de formatear
                return parseFloat(number).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // Función para obtener y actualizar el contador del carrito en el navbar
            function getCartCount() {
                fetch('{{ route('api.cart.count') }}', {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
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
            
            // Llamar al conteo del carrito al cargar la página
            getCartCount();

            // Función para actualizar toda la UI del carrito
            function updateCartUI(data) {
                // Actualizar los elementos del resumen del carrito
                if (subtotalNetProductsElement) {
                    subtotalNetProductsElement.textContent = formatNumberForUI(data.subtotal_net_products);
                }
                if (ivaProductsAmountElement) {
                    ivaProductsAmountElement.textContent = formatNumberForUI(data.iva_products_amount);
                }
                if (subtotalGrossProductsElement) {
                    subtotalGrossProductsElement.textContent = formatNumberForUI(data.subtotal_gross_products);
                }
                if (mpFeeAmountElement) {
                    mpFeeAmountElement.textContent = formatNumberForUI(data.mp_fee_amount);
                }
                if (finalTotalElement) {
                    finalTotalElement.textContent = formatNumberForUI(data.final_total);
                }

                // Actualizar el monto para Mercado Pago
                if (mercadopagoAmountInput) {
                    mercadopagoAmountInput.value = data.final_total; 
                }

                // Actualizar la visibilidad de la tabla y los botones
                const cartIsEmpty = data.cartCount === 0;

                if (cartTable) {
                    cartTable.style.display = cartIsEmpty ? 'none' : 'table';
                }
                if (cartSummary) {
                    cartSummary.style.display = cartIsEmpty ? 'none' : 'flex';
                }
                if (emptyCartMessage) {
                    emptyCartMessage.style.display = cartIsEmpty ? 'block' : 'none';
                }
                if (cartActionsContainer) {
                    cartActionsContainer.style.display = cartIsEmpty ? 'none' : 'flex';
                }
                if (emptyCartActionsContainer) {
                    emptyCartActionsContainer.style.display = cartIsEmpty ? 'flex' : 'none';
                }

                // Si hay un ítem específico actualizado en la respuesta (desde update)
                // Esto se usa cuando se actualiza solo un ítem y se devuelven sus detalles.
                if (data.item && typeof data.item.id !== 'undefined') { // Verificar que item.id existe
                    const itemSubtotalGrossElement = document.querySelector(`.item-subtotal-gross[data-id="${data.item.id}"]`);
                    if (itemSubtotalGrossElement) {
                        itemSubtotalGrossElement.textContent = formatNumberForUI(data.item.subtotal_item_gross);
                    }
                    const itemQuantityInput = document.querySelector(`.item-quantity-input[data-id="${data.item.id}"]`);
                    if (itemQuantityInput) {
                        itemQuantityInput.value = data.item.quantity;
                    }
                }
                // Si el carrito está vacío y la tabla se oculta, también limpiar el tbody
                if (cartIsEmpty && cartItemsBody) {
                    cartItemsBody.innerHTML = '';
                }
            }
            
            // Función para enviar peticiones AJAX (generalizada)
            async function sendCartRequest(url, method, payloadData) {
                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payloadData)
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        // Mostrar mensaje de error del backend si hay un status 'error'
                        alert(errorData.message || 'Error en la petición.');
                        return;
                    }

                    const responseData = await response.json();
                    console.log('Respuesta del servidor:', responseData);
                    
                    // Actualizar la interfaz con los nuevos totales
                    updateCartUI(responseData);

                    // Lógica para eliminar la fila si la cantidad es 0 o se eliminó
                    // Ahora se basa en el 'cartCount' en la respuesta, y si el item específico fue eliminado.
                    if (responseData.cartCount === 0 || (responseData.item && responseData.item.quantity === 0)) {
                        const rowToRemove = document.querySelector(`tr[data-id="${payloadData.id}"]`);
                        if (rowToRemove) {
                            rowToRemove.remove();
                        }
                    }
                    
                    getCartCount(); // Actualizar el contador del navbar
                    
                } catch (error) {
                    console.error('Error:', error);
                    alert('Ocurrió un error al procesar la solicitud: ' + error.message);
                }
            }

            // Manejadores de eventos usando delegación en cartItemsBody
            if (cartItemsBody) {
                cartItemsBody.addEventListener('click', function(event) {
                    const target = event.target;
                    // Obtener el data-id de la fila más cercana (que es el ID del CartItem o product_id)
                    const itemId = target.closest('tr')?.dataset.id; 

                    if (!itemId) return; 

                    if (target.classList.contains('increase-quantity') || target.classList.contains('decrease-quantity')) {
                        const quantityInput = document.querySelector(`.item-quantity-input[data-id="${itemId}"]`);
                        let newQuantity = parseInt(quantityInput.value);

                        if (target.classList.contains('increase-quantity')) {
                            newQuantity++;
                        } else if (target.classList.contains('decrease-quantity')) {
                            newQuantity--;
                        }

                        // No permitir cantidades negativas, si es < 0, se procesa como 0 (eliminar)
                        if (newQuantity < 0) newQuantity = 0; 

                        sendCartRequest('{{ route('cart.update') }}', 'POST', { id: itemId, quantity: newQuantity });
                    }
                    
                    // Clase corregida para el botón de eliminar!
                    if (target.classList.contains('remove-item')) { 
                        sendCartRequest('{{ route('cart.remove') }}', 'POST', { id: itemId });
                    }
                });
                
                cartItemsBody.addEventListener('change', function(event) {
                    const target = event.target;
                    if (target.classList.contains('item-quantity-input')) {
                        const itemId = target.dataset.id;
                        let newQuantity = parseInt(target.value);

                        if (isNaN(newQuantity) || newQuantity < 0) {
                            newQuantity = 1; 
                            target.value = 1; // Revertir el valor en el input
                        }

                        sendCartRequest('{{ route('cart.update') }}', 'POST', { id: itemId, quantity: newQuantity });
                    }
                });
            }

            // Inicializar la UI con los datos que vienen del servidor (al cargar la página)
            // Aseguramos que los valores sean números.
            // NOTA IMPORTANTE: Si los datos de $cartItems se serializan desde PHP a JSON
            // para el JS, Laravel los convertirá a objetos JavaScript si eran objetos PHP.
            // Si eran arrays PHP, los convertirá a arrays JS. El `data.item.id` en updateCartUI
            // espera un objeto.
            updateCartUI({
                cartItems: @json($cartItems), // Si esto es un array de objetos o de arrays, debe coincidir
                subtotal_net_products: parseFloat({{ $subtotal_net_products }}),
                iva_products_amount: parseFloat({{ $iva_products_amount }}),
                subtotal_gross_products: parseFloat({{ $subtotal_gross_products }}),
                mp_fee_amount: parseFloat({{ $mp_fee_amount }}),
                final_total: parseFloat({{ $final_total }}),
                cartCount: parseInt({{ $cartCount }})
            });
        });
    </script>
</body>
</html>