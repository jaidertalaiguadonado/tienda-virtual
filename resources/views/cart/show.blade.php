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

<table>
    <thead>
        <tr>
            <th>Producto</th>
            <th>Precio Unitario (IVA Inc.)</th>
            <th>Cantidad</th>
            <th>Subtotal (IVA Inc.)</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cartItems as $item)
            <tr>
                <td>
                    <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}" width="50">
                    {{ $item['name'] }}
                </td>
                <td>${{ number_format($item['price_unit_gross'], 2, ',', '.') }}</td>
                <td>
                    <div class="quantity-control">
                        <button type="button" class="decrease-quantity" data-id="{{ $item['id'] }}" data-product-id="{{ $item['product_id'] }}">-</button>
                        <input type="number" value="{{ $item['quantity'] }}" min="0" class="item-quantity-input" data-id="{{ $item['id'] }}" data-product-id="{{ $item['product_id'] }}">
                        <button type="button" class="increase-quantity" data-id="{{ $item['id'] }}" data-product-id="{{ $item['product_id'] }}">+</button>
                    </div>
                </td>
                <td class="item-subtotal-gross" data-id="{{ $item['id'] }}">${{ number_format($item['subtotal_item_gross'], 2, ',', '.') }}</td>
                <td>
                    <button type="button" class="remove-item" data-id="{{ $item['id'] }}" data-product-id="{{ $item['product_id'] }}">Eliminar</button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="cart-summary">
    <p>Subtotal Productos (sin IVA): $<span id="subtotal_net_products">{{ number_format($subtotal_net_products, 2, ',', '.') }}</span></p>
    <p>IVA Productos ({{ CartController::IVA_RATE * 100 }}%): $<span id="iva_products_amount">{{ number_format($iva_products_amount, 2, ',', '.') }}</span></p>
    <p>Subtotal Productos (con IVA): $<span id="subtotal_gross_products">{{ number_format($subtotal_gross_products, 2, ',', '.') }}</span></p>
    <p>Comisión Mercado Pago: $<span id="mp_fee_amount">{{ number_format($mp_fee_amount, 2, ',', '.') }}</span></p>
    <p><strong>Total Final a Pagar: $<span id="final_total">{{ number_format($final_total, 2, ',', '.') }}</span></strong></p>
</div>
        {{-- FIN: Desglose de Totales con IVA y MP --}}

        <p class="empty-cart-message" style="display: {{ empty($cartItems) ? 'block' : 'none' }};">Tu carrito está vacío. ¡Empieza a añadir productos!</p>

        <div class="cart-actions" style="display: {{ empty($cartItems) ? 'none' : 'flex' }};">
            <a href="{{ route('welcome') }}" class="continue-shopping-button">Seguir Comprando</a>

            <form action="{{ route('mercadopago.pay') }}" method="POST" id="mercadopago-checkout-form">
                @csrf
                {{-- Ahora el monto para Mercado Pago será el total final --}}
                <input type="hidden" name="total_amount" id="mercadopago-amount" value="{{ $final_total }}">
                <input type="hidden" name="description" id="mercadopago-description" value="Compra en Tienda JD">
                
                <button type="submit" class="mercadopago-pay-button">Pagar con Mercado Pago</button>
            </form>
        </div>
        {{-- El botón "Seguir Comprando" si el carrito está vacío --}}
        <div class="cart-actions" style="display: {{ empty($cartItems) ? 'flex' : 'none' }}; justify-content: center;">
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
            const cartItemsBody = document.getElementById('cart-items-body');
            const cartTable = document.querySelector('.cart-table'); 
            const cartSummary = document.querySelector('.cart-summary');
            const emptyCartMessage = document.querySelector('.empty-cart-message');
            const cartActionsContainer = document.querySelector('.cart-actions'); 

            // Nuevos elementos para el desglose de totales
            const cartProductsSubtotalNetElement = document.getElementById('cart-products-subtotal-net'); // Para subtotal NETO
            const cartIvaAmountElement = document.getElementById('cart-iva-amount');
            const cartProductsSubtotalGrossElement = document.getElementById('cart-products-subtotal-gross'); // Para subtotal BRUTO
            const cartMpFeeElement = document.getElementById('cart-mp-fee');
            const cartFinalTotalElement = document.getElementById('cart-final-total');

            const mercadopagoAmountInput = document.getElementById('mercadopago-amount');
            const mercadopagoDescriptionInput = document.getElementById('mercadopago-description');
            const mercadopagoCheckoutForm = document.getElementById('mercadopago-checkout-form');

            if (menuToggle && mobileMenu) {
                menuToggle.addEventListener('click', function() {
                    mobileMenu.classList.toggle('active');
                });
            }
            
            function formatNumberForUI(number) {
                // Utiliza 'es-CO' para formato de moneda colombiana (separador de miles como punto, decimal como coma)
                return parseFloat(number).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

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
            
            getCartCount();

            // Modificación de la función updateCartUI
            function updateCartUI(productId, newQuantity, newSubtotalItemNet, newSubtotalItemGross, newProductsSubtotalNet, newIvaAmount, newProductsSubtotalGross, newMpFeeAmount, newFinalTotal, cartCount) {
                const row = cartItemsBody.querySelector(`tr[data-product-id="${productId}"]`);
                const cartIsEmpty = cartCount === 0;

                if (newQuantity <= 0) { 
                    if (row) {
                        row.remove(); 
                    }
                } else if (row) { 
                    row.querySelector('.quantity-input').value = newQuantity;
                    // Actualizamos el subtotal del ítem en la tabla con la versión BRUTA
                    row.querySelector('.item-subtotal-value').textContent = formatNumberForUI(newSubtotalItemGross);
                }

                // Actualizar los nuevos elementos del resumen del carrito
                if (cartProductsSubtotalNetElement) {
                    cartProductsSubtotalNetElement.textContent = formatNumberForUI(newProductsSubtotalNet);
                }
                if (cartIvaAmountElement) {
                    cartIvaAmountElement.textContent = formatNumberForUI(newIvaAmount);
                }
                if (cartProductsSubtotalGrossElement) { // Nuevo elemento para el subtotal BRUTO de productos
                    cartProductsSubtotalGrossElement.textContent = formatNumberForUI(newProductsSubtotalGross);
                }
                if (cartMpFeeElement) {
                    cartMpFeeElement.textContent = formatNumberForUI(newMpFeeAmount);
                }
                if (cartFinalTotalElement) {
                    cartFinalTotalElement.textContent = formatNumberForUI(newFinalTotal);
                }

                if (mercadopagoAmountInput) {
                    mercadopagoAmountInput.value = newFinalTotal; 
                }

                if (cartIsEmpty) {
                    cartItemsBody.innerHTML = ''; // Limpiar el cuerpo de la tabla

                    if (cartTable) { 
                        cartTable.style.display = 'none';
                    }
                    if (cartSummary) { 
                        cartSummary.style.display = 'none';
                    }
                    if (emptyCartMessage) { 
                        emptyCartMessage.style.display = 'block';
                    }
                    
                    if (cartActionsContainer) {
                        cartActionsContainer.style.display = 'none'; // Ocultar los botones de pago
                    }
                    
                    // Mostrar el botón de "Seguir Comprando" que está fuera del contenedor principal si el carrito está vacío
                    document.querySelector('.cart-container > .cart-actions:last-of-type').style.display = 'flex';

                } else {
                    if (cartTable) { 
                        cartTable.style.display = 'table'; 
                    }
                    if (cartSummary) { 
                        cartSummary.style.display = 'flex'; // Cambiado a flex para el nuevo estilo
                    }
                    if (emptyCartMessage) { 
                        emptyCartMessage.style.display = 'none';
                    }
                    
                    if (cartActionsContainer) {
                        cartActionsContainer.style.display = 'flex'; // Mostrar los botones de pago
                    }
                    
                    // Ocultar el botón de "Seguir Comprando" si el carrito no está vacío
                    document.querySelector('.cart-container > .cart-actions:last-of-type').style.display = 'none';
                }
            }
            
            cartItemsBody.addEventListener('click', function(event) {
                const target = event.target;
                const productId = target.dataset.productId; // Esto ahora es $item['id']

                if (!productId) return; 

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
                            cart_item_id: productId, // Se envía el ID que obtuvimos de data-product-id
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
                        updateCartUI(
                            productId, 
                            data.item.quantity, 
                            data.item.subtotal_item_net, // Nuevo: subtotal_item_net
                            data.item.subtotal_item_gross, // Nuevo: subtotal_item_gross
                            data.subtotal_net_products, // Nuevo: subtotal_net_products
                            data.iva_products_amount, // Nuevo: iva_products_amount
                            data.subtotal_gross_products, // Nuevo: subtotal_gross_products
                            data.mp_fee_amount, 
                            data.final_total, 
                            data.cartCount
                        );
                        getCartCount(); 
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Hubo un error al actualizar la cantidad: ' + error.message);
                    });
                }
                
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
                        body: JSON.stringify({ cart_item_id: productId }) // Se envía el ID que obtuvimos de data-product-id
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw new Error(err.message || 'Error al eliminar el producto.'); });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Producto eliminado:', data);
                        updateCartUI(
                            productId, 
                            0, // Cantidad 0 para indicar que se eliminó
                            0, // Subtotal de item NETO 0
                            0, // Subtotal de item BRUTO 0
                            data.subtotal_net_products, // Nuevo: subtotal_net_products
                            data.iva_products_amount, // Nuevo: iva_products_amount
                            data.subtotal_gross_products, // Nuevo: subtotal_gross_products
                            data.mp_fee_amount, 
                            data.final_total, 
                            data.cartCount
                        ); 
                        getCartCount(); 
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Hubo un error al eliminar el producto: ' + error.message);
                    });
                }
            });
            
            cartItemsBody.addEventListener('change', function(event) {
                const target = event.target;
                if (target.classList.contains('quantity-input')) {
                    const productId = target.dataset.productId; // Esto ahora es $item['id']
                    let newQuantity = parseInt(target.value);

                    if (isNaN(newQuantity) || newQuantity < 0) {
                        newQuantity = 1; 
                        target.value = 1;
                    }

                    if (newQuantity === 0) {
                        if (!confirm('¿Estás seguro de que quieres eliminar este producto del carrito?')) {
                            target.value = 1; 
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
                            cart_item_id: productId, // Se envía el ID que obtuvimos de data-product-id
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
                        updateCartUI(
                            productId, 
                            data.item.quantity, 
                            data.item.subtotal_item_net, // Nuevo: subtotal_item_net
                            data.item.subtotal_item_gross, // Nuevo: subtotal_item_gross
                            data.subtotal_net_products, // Nuevo: subtotal_net_products
                            data.iva_products_amount, // Nuevo: iva_products_amount
                            data.subtotal_gross_products, // Nuevo: subtotal_gross_products
                            data.mp_fee_amount, 
                            data.final_total, 
                            data.cartCount
                        );
                        getCartCount(); 
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Hubo un error al actualizar la cantidad: ' + error.message);
                    });
                }
            });

            // Inicializar la UI con los datos que vienen del servidor (al cargar la página)
            updateCartUI(
                null, // No es para un producto específico al iniciar
                null, 
                null, 
                null, // newSubtotalItemGross no se usa aquí
                {{ $subtotal_net_products }}, // Subtotal NETO de productos del backend
                {{ $iva_products_amount }}, // IVA de productos del backend
                {{ $subtotal_gross_products }}, // Subtotal BRUTO de productos del backend
                {{ $mp_fee_amount }}, // Comisión MP del backend
                {{ $final_total }}, // Total final del backend
                {{ empty($cartItems) ? 0 : $cartItems->sum('quantity') }} // Conteos del backend
            );
        });
    </script>
</body>
</html>