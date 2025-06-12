<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Tienda JD') }} - Carrito</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/js/app.js'])
    <style>
        /* Estilos básicos para el carrito - Puedes mover esto a un archivo CSS externo */
        body {
            font-family: 'Open Sans', sans-serif;
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
            flex-wrap: wrap;
        }
        .navbar-brand {
            color: white;
            text-decoration: none;
            font-weight: 800;
            font-size: 1.5rem;
        }
        .navbar-links, .navbar-links-mobile {
            display: flex;
            gap: 1.5rem;
        }
        .navbar-links-mobile {
            display: none; /* Oculto por defecto, visible en móvil */
            flex-direction: column;
            width: 100%;
            text-align: center;
            background-color: #444;
            padding: 1rem 0;
            margin-top: 1rem;
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
            font-family: inherit;
            font-size: 1rem;
        }
        .navbar-link:hover, .logout-button-navbar:hover {
            background-color: #555;
        }
        .menu-toggle {
            display: none; /* Oculto por defecto, visible en móvil */
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .cart-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 1.5rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .cart-title {
            text-align: center;
            color: #333;
            margin-bottom: 2rem;
            font-size: 2rem;
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        .cart-table th, .cart-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .cart-table th {
            background-color: #f8f8f8;
            font-weight: 600;
        }
        .cart-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            margin-right: 10px;
            border-radius: 4px;
            vertical-align: middle;
        }
        .cart-item-name {
            font-weight: 600;
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
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }
        .quantity-button:hover {
            background-color: #0056b3;
        }
        .quantity-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            -moz-appearance: textfield; /* Para Firefox */
        }
        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .remove-button {
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .remove-button:hover {
            background-color: #c82333;
        }
        .cart-summary {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .cart-summary div {
            width: 100%;
            display: flex;
            justify-content: space-between;
        }
        .cart-summary span {
            font-weight: 700;
            color: #007bff;
        }
        .empty-cart-message {
            text-align: center;
            font-size: 1.2rem;
            color: #666;
            margin-top: 3rem;
            margin-bottom: 3rem;
        }
        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
            gap: 1rem; /* Espacio entre los botones */
        }
        .continue-shopping-button, .mercadopago-pay-button {
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
            text-align: center;
            flex-grow: 1; /* Permitir que los botones crezcan */
        }
        .continue-shopping-button {
            background-color: #6c757d;
            color: white;
            border: none;
            cursor: pointer;
        }
        .continue-shopping-button:hover {
            background-color: #5a6268;
        }
        .mercadopago-pay-button {
            background-color: #009ee3; /* Color de Mercado Pago */
            color: white;
            border: none;
            cursor: pointer;
        }
        .mercadopago-pay-button:hover {
            background-color: #007bb6;
        }

        .footer {
            text-align: center;
            padding: 2rem;
            background-color: #333;
            color: white;
            margin-top: 3rem;
        }

        /* Media Queries para responsividad */
        @media (max-width: 768px) {
            .navbar-links {
                display: none;
            }
            .menu-toggle {
                display: block;
            }
            .navbar-links-mobile.active {
                display: flex;
            }
            .navbar-links-mobile {
                display: none;
                flex-direction: column;
                width: 100%;
            }
            .navbar-links-mobile .navbar-link {
                width: 100%;
                text-align: center;
                padding: 0.8rem 0;
            }

            .cart-table, .cart-table tbody, .cart-table tr, .cart-table td {
                display: block;
                width: 100%;
            }
            .cart-table thead {
                display: none;
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
                padding-8px;
                position: relative;
                padding-left: 50%; /* Espacio para la etiqueta */
                min-height: 40px; /* Asegura espacio para contenido */
                display: flex;
                align-items: center;
                justify-content: flex-end; /* Alinea contenido a la derecha */
            }
            .cart-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                width: calc(50% - 20px); /* Ajusta ancho para etiqueta */
                text-align: left;
                font-weight: bold;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .cart-table td:nth-child(1) { /* Estilo específico para el producto */
                display: flex;
                flex-direction: column; /* Cambia a columna para imagen y nombre */
                align-items: flex-start;
                padding-left: 10px; /* Elimina padding para label */
                justify-content: center;
                width: 100%; /* Ocupa todo el ancho */
            }
            .cart-table td:nth-child(1)::before {
                display: none; /* Oculta la etiqueta para el primer td */
            }
            .cart-item-image {
                margin-right: 0;
                margin-bottom: 10px;
                align-self: center; /* Centra la imagen */
            }
            .cart-actions {
                flex-direction: column;
            }
        }
    </style>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Tienda JD') }} - Carrito</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/js/app.js'])
    {{-- Si tienes un archivo CSS para el carrito, agrégalo aquí --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/cart.css') }}"> --}}
    <style>
        /* Estilos básicos para el carrito para que se vea algo */
        body { font-family: 'Open Sans', sans-serif; margin: 0; background-color: #f4f4f4; }
        .navbar { background-color: #333; color: white; padding: 1em; display: flex; justify-content: space-between; align-items: center; }
        .navbar-brand { color: white; text-decoration: none; font-size: 1.5em; }
        .navbar-link { color: white; text-decoration: none; margin-left: 1em; }
        .navbar-links { display: flex; }
        .navbar-links-mobile { display: none; /* Oculto por defecto, se muestra con JS para móviles */ }
        .menu-toggle { display: none; /* Oculto por defecto, se muestra con JS para móviles */ }
        .logout-button-navbar { background: none; border: none; color: white; cursor: pointer; font-size: 1em; margin-left: 1em; }

        .cart-container { max-width: 960px; margin: 2em auto; padding: 1em; background-color: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; margin-bottom: 1.5em; }
        .cart-table { width: 100%; border-collapse: collapse; margin-bottom: 2em; }
        .cart-table th, .cart-table td { border: 1px solid #ddd; padding: 0.8em; text-align: left; }
        .cart-table th { background-color: #f8f8f8; }
        .cart-table img { vertical-align: middle; margin-right: 0.5em; }
        .quantity-control { display: flex; align-items: center; }
        .quantity-control button { background-color: #007bff; color: white; border: none; padding: 0.5em 0.8em; cursor: pointer; border-radius: 4px; font-size: 1em; }
        .quantity-control input { width: 40px; text-align: center; margin: 0 0.5em; border: 1px solid #ccc; border-radius: 4px; padding: 0.4em; }
        .remove-item { background-color: #dc3545; color: white; border: none; padding: 0.5em 1em; cursor: pointer; border-radius: 4px; }
        .cart-summary { background-color: #f9f9f9; border: 1px solid #eee; padding: 1.5em; border-radius: 8px; margin-top: 1em; display: flex; flex-direction: column; align-items: flex-end; }
        .cart-summary p { margin: 0.5em 0; font-size: 1.1em; }
        .cart-summary strong { font-size: 1.3em; color: #007bff; }
        .empty-cart-message { text-align: center; margin-top: 2em; font-size: 1.2em; color: #666; }
        .cart-actions { display: flex; justify-content: space-between; margin-top: 2em; }
        .continue-shopping-button, .mercadopago-pay-button { background-color: #28a745; color: white; padding: 0.8em 1.5em; text-decoration: none; border-radius: 4px; font-size: 1.1em; border: none; cursor: pointer; }
        .mercadopago-pay-button { background-color: #007bff; }
        .footer { text-align: center; padding: 1em; background-color: #333; color: white; margin-top: 2em; }
        /* Media queries para móviles */
        @media (max-width: 768px) {
            .navbar-links { display: none; }
            .navbar-links-mobile { display: flex; flex-direction: column; position: absolute; top: 60px; left: 0; width: 100%; background-color: #333; padding: 1em 0; z-index: 1000; }
            .navbar-links-mobile.active { display: flex; }
            .navbar-link, .logout-button-navbar { width: 100%; text-align: center; padding: 0.5em 0; }
            .menu-toggle { display: block; background: none; border: none; color: white; font-size: 1.5em; cursor: pointer; }
            .cart-actions { flex-direction: column; align-items: center; }
            .continue-shopping-button, .mercadopago-pay-button { width: 80%; margin-bottom: 1em; }
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
        <h1>Tu Carrito de Compras</h1>

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
            {{-- AÑADIDO: id="cart-items-body" al tbody --}}
            <tbody id="cart-items-body">
                @foreach($cartItems as $item)
                    {{-- AÑADIDO: data-id a la fila --}}
                    <tr data-id="{{ $item['id'] }}">
                        <td>
                            <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}" width="50">
                            {{ $item['name'] }}
                        </td>
                        {{-- OK: price_unit_gross --}}
                        <td>${{ number_format($item['price_unit_gross'], 2, ',', '.') }}</td>
                        <td>
                            <div class="quantity-control">
                                {{-- Los data-id son el id del CartItem --}}
                                <button type="button" class="decrease-quantity" data-id="{{ $item['id'] }}">-</button>
                                {{-- OK: item-quantity-input --}}
                                <input type="number" value="{{ $item['quantity'] }}" min="0" class="item-quantity-input" data-id="{{ $item['id'] }}">
                                <button type="button" class="increase-quantity" data-id="{{ $item['id'] }}">+</button>
                            </div>
                        </td>
                        {{-- OK: item-subtotal-gross --}}
                        <td class="item-subtotal-gross" data-id="{{ $item['id'] }}">${{ number_format($item['subtotal_item_gross'], 2, ',', '.') }}</td>
                        <td>
                            {{-- OK: remove-item --}}
                            <button type="button" class="remove-item" data-id="{{ $item['id'] }}">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- El resumen del carrito se mostrará/ocultará con JavaScript --}}
        <div class="cart-summary" style="display: {{ empty($cartItems) ? 'none' : 'flex' }};">
            {{-- IDs corregidos para coincidir con JS --}}
            <p>Subtotal Productos (sin IVA): $<span id="subtotal_net_products">{{ number_format($subtotal_net_products, 2, ',', '.') }}</span></p>
            {{-- Usamos la constante de la clase CartController --}}
            <p>IVA Productos ({{ \App\Http\Controllers\CartController::IVA_RATE * 100 }}%): $<span id="iva_products_amount">{{ number_format($iva_products_amount, 2, ',', '.') }}</span></p>
            <p>Subtotal Productos (con IVA): $<span id="subtotal_gross_products">{{ number_format($subtotal_gross_products, 2, ',', '.') }}</span></p>
            <p>Comisión Mercado Pago: $<span id="mp_fee_amount">{{ number_format($mp_fee_amount, 2, ',', '.') }}</span></p>
            <p><strong>Total Final a Pagar: $<span id="final_total">{{ number_format($final_total, 2, ',', '.') }}</span></strong></p>
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
            const cartItemsBody = document.getElementById('cart-items-body'); // Corregido: ahora el tbody tiene este ID
            const cartTable = document.querySelector('.cart-table'); // Corregido: la tabla tiene esta clase
            const cartSummary = document.querySelector('.cart-summary');
            const emptyCartMessage = document.querySelector('.empty-cart-message');
            const cartActionsContainer = document.querySelector('.cart-actions'); // Contenedor de botones de pago/seguir comprando
            const emptyCartActionsContainer = document.querySelector('.empty-cart-actions'); // Contenedor del botón seguir comprando solo para carrito vacío

            // Elementos del desglose de totales (IDs corregidos)
            const subtotalNetProductsElement = document.getElementById('subtotal_net_products');
            const ivaProductsAmountElement = document.getElementById('iva_products_amount');
            const subtotalGrossProductsElement = document.getElementById('subtotal_gross_products');
            const mpFeeAmountElement = document.getElementById('mp_fee_amount');
            const finalTotalElement = document.getElementById('final_total');

            const mercadopagoAmountInput = document.getElementById('mercadopago-amount');
            const mercadopagoDescriptionInput = document.getElementById('mercadopago-description');
            // mercadopagoCheckoutForm ya está definido arriba

            // Toggle para el menú móvil
            if (menuToggle && mobileMenu) {
                menuToggle.addEventListener('click', function() {
                    mobileMenu.classList.toggle('active');
                });
            }
            
            // Función para formatear números para la interfaz de usuario
            function formatNumberForUI(number) {
                // Utiliza 'es-CO' para formato de moneda colombiana (separador de miles como punto, decimal como coma)
                return parseFloat(number).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // Función para obtener y actualizar el contador del carrito en el navbar
            function getCartCount() {
                fetch('{{ route('api.cart.count') }}', { // Usar la ruta con nombre
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
                    cartActionsContainer.style.display = cartIsEmpty ? 'none' : 'flex'; // Botones de pago/seguir comprando (principales)
                }
                if (emptyCartActionsContainer) {
                    emptyCartActionsContainer.style.display = cartIsEmpty ? 'flex' : 'none'; // Botón de seguir comprando (solo carrito vacío)
                }

                // Si hay un ítem específico actualizado en la respuesta (desde update)
                if (data.item && data.item.id) {
                    const itemSubtotalGrossElement = document.querySelector(`.item-subtotal-gross[data-id="${data.item.id}"]`);
                    if (itemSubtotalGrossElement) {
                        itemSubtotalGrossElement.textContent = formatNumberForUI(data.item.subtotal_item_gross);
                    }
                    const itemQuantityInput = document.querySelector(`.item-quantity-input[data-id="${data.item.id}"]`);
                    if (itemQuantityInput) {
                        itemQuantityInput.value = data.item.quantity;
                    }
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
                        alert(errorData.message || 'Error en la petición.');
                        return;
                    }

                    const responseData = await response.json();
                    console.log('Respuesta del servidor:', responseData);
                    
                    // Actualizar la interfaz con los nuevos totales
                    updateCartUI(responseData);

                    // Lógica para eliminar la fila si la cantidad es 0 o se eliminó
                    if (payloadData.quantity === 0 || method === 'POST' && url.includes('/remove')) {
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
            if (cartItemsBody) { // Asegúrate de que el elemento existe
                cartItemsBody.addEventListener('click', function(event) {
                    const target = event.target;
                    const itemId = target.dataset.id; // Esto es el 'id' del CartItem

                    if (!itemId) return; 

                    if (target.classList.contains('increase-quantity') || target.classList.contains('decrease-quantity')) {
                        const quantityInput = document.querySelector(`.item-quantity-input[data-id="${itemId}"]`); // Corregido: clase del input
                        let newQuantity = parseInt(quantityInput.value);

                        if (target.classList.contains('increase-quantity')) {
                            newQuantity++;
                        } else if (target.classList.contains('decrease-quantity')) {
                            newQuantity--;
                        }

                        if (newQuantity < 0) newQuantity = 0; // Evitar cantidades negativas, manejar eliminación

                        sendCartRequest('{{ route('cart.update') }}', 'POST', { id: itemId, quantity: newQuantity });
                    }
                    
                    if (target.classList.contains('remove-item')) { // Corregido: clase del botón
                        if (confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
                            sendCartRequest('{{ route('cart.remove') }}', 'POST', { id: itemId });
                        }
                    }
                });
                
                cartItemsBody.addEventListener('change', function(event) {
                    const target = event.target;
                    if (target.classList.contains('item-quantity-input')) { // Corregido: clase del input
                        const itemId = target.dataset.id; // Esto es el 'id' del CartItem
                        let newQuantity = parseInt(target.value);

                        if (isNaN(newQuantity) || newQuantity < 0) {
                            newQuantity = 1; 
                            target.value = 1; // Revertir el valor en el input
                        }

                        if (newQuantity === 0) {
                            if (!confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
                                target.value = 1; 
                                return;
                            }
                        }
                        sendCartRequest('{{ route('cart.update') }}', 'POST', { id: itemId, quantity: newQuantity });
                    }
                });
            }

            // Inicializar la UI con los datos que vienen del servidor (al cargar la página)
            updateCartUI({
                cartItems: @json($cartItems), // Pasa los ítems para la lógica de vacío
                subtotal_net_products: {{ $subtotal_net_products }},
                iva_products_amount: {{ $iva_products_amount }},
                subtotal_gross_products: {{ $subtotal_gross_products }},
                mp_fee_amount: {{ $mp_fee_amount }},
                final_total: {{ $final_total }},
                cartCount: {{ $cartCount }} // Ya pasamos cartCount desde el controlador
            });
        });
    </script>
</body>
</html>