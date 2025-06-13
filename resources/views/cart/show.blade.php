<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Mi Tienda') }}</title>
    @vite(['resources/js/app.js'])

    <style>
:root {
    --primary-color: #007BFF;
    --primary-light: #66B2FF;
    --secondary-color: #17A2B8;
    --text-dark: #212529;
    --text-light: #6C757D;
    --background-light: #F8F9FA;
    --card-background: #ffffff;
    --border-color: #DEE2E6;
    --button-text: #ffffff;
    --logout-color: #DC3545;
    --logout-light: #E65F6C;
    --stock-available: #28A745;
    --stock-unavailable: #DC3545;
    --remove-button-color: #DC3545; /* ¡Ya estaba definido como rojo! */
    --remove-button-hover: #E65F6C;
    --quantity-button-color: #007BFF;
    --quantity-button-hover: #66B2FF;
    --success-color: #28a745;
    --error-color: #dc3545;
    --info-color: #17a2b8;

    /* Nuevas variables para el botón de Mercado Pago */
    --mercadopago-button-color: #009EE3; /* Azul de Mercado Pago */
    --mercadopago-button-hover: #008ACD;
    --mercadopago-text-color: #ffffff;
}

html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Open Sans', sans-serif;
    background-color: var(--background-light);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    line-height: 1.6;
    color: var(--text-dark);
    font-size: 16px;

    /* Sticky Footer - Flexbox layout */
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

a {
    text-decoration: none;
    color: inherit;
}

/* Navbar Styles */
.navbar {
    background-color: var(--card-background);
    border-bottom: 1px solid var(--border-color);
    padding: 1.2rem 2rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 1000;
    flex-shrink: 0;
}

.navbar-brand {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--primary-color);
}

.menu-toggle {
    display: none;
    font-size: 2rem;
    background: none;
    border: none;
    color: var(--text-dark);
    cursor: pointer;
    padding: 0;
    line-height: 1;
}

.navbar-links {
    display: flex;
    gap: 1.5rem;
    align-items: center;
}

.navbar-links-mobile {
    display: none;
    flex-direction: column;
    background-color: var(--card-background);
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    border-top: 1px solid var(--border-color);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    z-index: 999;
    padding: 1rem 0;
}

.navbar-links-mobile.active {
    display: flex;
}

.navbar-links-mobile .navbar-link,
.navbar-links-mobile .logout-button-navbar {
    padding: 0.8rem 2rem;
    text-align: center;
    width: auto;
    margin: 0.2rem 1rem;
}

.navbar-links-mobile .logout-button-navbar {
    width: calc(100% - 2rem);
}

.navbar-link {
    color: var(--text-dark);
    font-weight: 600;
    transition: color 0.3s ease;
}

.navbar-link:hover {
    color: var(--primary-color);
}

.logout-button-navbar {
    background-color: var(--logout-color);
    color: var(--button-text);
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.logout-button-navbar:hover {
    background-color: var(--logout-light);
    transform: translateY(-1px);
}

.logout-button-navbar:focus {
    outline: none;
    box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.4);
}

/* Main Content Area */
.cart-container {
    max-width: 1000px;
    margin: 3rem auto;
    padding: 2rem;
    background-color: var(--card-background);
    border-radius: 1rem;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    flex-grow: 1;
}

.cart-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--primary-color); /* CAMBIO: Color azul para el título */
    margin-bottom: 2.5rem;
    text-align: center;
}

.cart-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2rem;
    text-align: left;
}

.cart-table th, .cart-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}

.cart-table th {
    background-color: var(--background-light);
    font-weight: 700;
    color: var(--text-dark);
    text-transform: uppercase;
    font-size: 0.9rem;
}

.cart-table td {
    font-size: 1rem;
    color: var(--text-dark);
}

/* CAMBIO: Aplicar color azul a la celda del precio unitario */
.cart-table td[data-label="Precio Unitario:"] {
    color: var(--primary-color);
    font-weight: 600; /* Añadir bold para que resalte más */
}


.cart-item-image {
    width: 60px;
    height: 60px;
    object-fit: contain;
    border-radius: 0.5rem;
    vertical-align: middle;
    margin-right: 1rem;
    border: 1px solid var(--border-color);
}

.cart-item-name {
    font-weight: 600;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quantity-button {
    background-color: var(--primary-color);
    color: var(--button-text);
    border: none;
    border-radius: 0.3rem;
    width: 30px;
    height: 30px;
    font-size: 1.2rem;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.2s ease;
    display: flex;
    justify-content: center;
    align-items: center;
}

.quantity-button:hover {
    background-color: var(--primary-light);
}

.quantity-button:disabled {
    background-color: var(--text-light);
    cursor: not-allowed;
    opacity: 0.7;
}

.quantity-input {
    width: 60px;
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: 0.3rem;
    text-align: center;
    font-size: 1rem;
    -moz-appearance: textfield; /* Para Firefox */
}
/* Ocultar flechas en inputs tipo number para Chrome, Safari, Edge, Opera */
.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}


.remove-button {
    /* Ya estaba definido para usar --remove-button-color que es rojo y button-text que es blanco */
    background-color: var(--remove-button-color); /* Esto es #DC3545 (rojo) */
    color: var(--button-text); /* Esto es #ffffff (blanco) */
    border: none;
    border-radius: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.remove-button:hover {
    background-color: var(--remove-button-hover);
    transform: translateY(-1px);
}

/* Desglose de Totales */
.cart-summary {
    background-color: var(--background-light);
    border: 1px solid var(--border-color);
    padding: 1.5rem;
    border-radius: 8px;
    margin-top: 2rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    font-size: 1.1rem;
}

.cart-summary div {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 0.5rem;
    border-bottom: 1px dashed #e0e0e0;
}

/* CAMBIO: Aplicar color azul a la última línea del resumen (Total Final a Pagar) */
.cart-summary div:last-child {
    border-bottom: none;
    font-weight: bold;
    font-size: 1.3rem;
    color: var(--primary-color); /* CAMBIO: Usar primary-color para el total final */
}


.empty-cart-message {
    font-size: 1.5rem;
    color: var(--text-light);
    margin-top: 3rem;
    margin-bottom: 3rem;
    text-align: center;
}

.cart-actions {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 2rem;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.cart-actions .continue-shopping-button,
.cart-actions .mercadopago-pay-button,
.cart-actions form {
    /* No se aplican flex: 1, min-width, max-width, box-sizing para estos elementos si los vas a quitar o estilizar de forma diferente */
}

.cart-actions form button {
    width: 100%; /* Esto podría necesitar ajustarse si no usas los botones principales */
}

.continue-shopping-button {
    background-color: var(--secondary-color);
    color: var(--button-text);
    display: inline-block;
    padding: 0.8rem 2rem;
    border-radius: 0.75rem;
    font-weight: 700;
    font-size: 1rem;
    transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    text-align: center;
    border: none;
    cursor: pointer;
}

.continue-shopping-button:hover {
    background-color: var(--secondary-color);
    filter: brightness(0.9);
    transform: translateY(-2px);
}

.mercadopago-pay-button {
    background-color: var(--mercadopago-button-color);
    color: var(--mercadopago-text-color);
    display: inline-block;
    padding: 0.8rem 2rem;
    border-radius: 0.75rem;
    font-weight: 700;
    font-size: 1rem;
    transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    text-align: center;
    border: none;
    cursor: pointer;
}

.mercadopago-pay-button:hover {
    background-color: var(--mercadopago-button-hover);
    transform: translateY(-2px);
}

/* Footer Styles */
.footer {
    background-color: var(--primary-color);
    color: var(--button-text);
    padding: 1.5rem 2rem;
    text-align: center;
    font-size: 0.9rem;
    border-top-left-radius: 2rem;
    border-top-right-radius: 2rem;
    margin-top: 4rem;
    flex-shrink: 0;
}

.footer p {
    margin: 0;
    opacity: 0.8;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .navbar {
        padding: 1rem 1rem;
        flex-wrap: wrap;
    }
    .navbar-brand {
        font-size: 1.6rem;
        margin-right: auto;
    }
    .navbar-links {
        display: none;
    }
    .menu-toggle {
        display: block;
    }

    .cart-container {
        margin: 2rem 1rem;
        padding: 1.5rem;
    }

    .cart-title {
        font-size: 2rem;
        margin-bottom: 1.5rem;
    }

    .cart-table, .cart-table tbody, .cart-table tr, .cart-table td, .cart-table th {
        display: block;
        width: 100%;
    }

    .cart-table thead {
        display: none;
    }

    .cart-table tr {
        margin-bottom: 1rem;
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        flex-direction: column;
        align-items: flex-start;
        padding: 1rem;
        box-sizing: border-box;
    }

    .cart-table td {
        border-bottom: none;
        text-align: left;
        padding: 0.4rem 0;
        position: relative;
        width: 100%;
        display: flex;
        justify-content: flex-start;
        align-items: center;
        gap: 0.5rem;
        box-sizing: border-box;
    }

    .cart-table td:before {
        content: attr(data-label);
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-light);
        font-size: 0.75rem;
        min-width: 80px;
        flex-shrink: 0;
    }

    .cart-table td:first-child {
        text-align: left;
        flex-basis: 100%;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.8rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        padding-top: 0.5rem;
        justify-content: flex-start;
        gap: 0;
    }

    .cart-table td:first-child:before {
        content: "";
        display: none;
    }

    .cart-item-image {
        width: 60px;
        height: 60px;
        margin-right: 1rem;
    }

    .cart-item-name {
        flex-grow: 1;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .quantity-controls {
        justify-content: flex-start;
        width: auto;
        margin-top: 0.5rem;
        flex-basis: auto;
        flex-grow: 1;
    }

    .quantity-input {
        width: 50px;
        padding: 0.4rem;
        font-size: 1rem;
    }
    .quantity-button {
        width: 30px;
        height: 30px;
        font-size: 1.2rem;
    }

    .remove-button {
        width: 100%;
        margin-top: 0.8rem;
        align-self: center;
        padding: 0.6rem 1rem;
        font-size: 0.95rem;
    }
    .cart-table td[data-label="Acción:"] {
        border-top: 1px dashed var(--border-color);
        margin-top: 1rem;
        padding-top: 1rem;
        justify-content: center;
    }
    .cart-table td[data-label="Acción:"]:before {
        display: none;
    }

    .cart-summary {
        font-size: 1.5rem;
        text-align: center;
        padding-top: 1rem;
    }

    /* Keep the default flex behavior for summary items */
    .cart-summary div {
        justify-content: space-between;
        text-align: left;
    }


    #cart-total {
        display: block;
        margin-left: 0;
        margin-top: 0.5rem;
    }

    .empty-cart-message {
        font-size: 1.2rem;
    }

    .footer {
        padding: 1.5rem 1rem;
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
        margin-top: 3rem;
    }

    .cart-actions {
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .cart-actions .continue-shopping-button,
    .cart-actions .mercadopago-pay-button,
    .cart-actions form {
        padding: 0.6rem 1.2rem;
        font-size: 0.9rem;
        max-width: 250px;
    }
}

@media (max-width: 480px) {
    body {
        font-size: 14px;
    }

    .navbar {
        padding: 0.8rem 0.8rem;
    }

    .navbar-brand {
        font-size: 1.4rem;
    }

    .cart-title {
        font-size: 1.6rem;
        margin-bottom: 0.8rem;
    }

    .cart-table tr {
        padding: 0.8rem;
    }

    .cart-table td {
        padding: 0.3rem 0;
        gap: 0.3rem;
    }

    .cart-table td:before {
        font-size: 0.7rem;
        min-width: 65px;
    }

    .cart-table td:first-child {
        padding-bottom: 0.6rem;
        margin-bottom: 0.4rem;
    }

    .cart-item-image {
        width: 45px;
        height: 45px;
        margin-right: 0.8rem;
    }

    .quantity-controls {
        gap: 0.2rem;
    }
    .quantity-input {
        width: 40px;
        padding: 0.25rem;
        font-size: 0.9rem;
    }
    .quantity-button {
        width: 28px;
        height: 28px;
        font-size: 1.1rem;
    }

    .remove-button {
        padding: 0.5rem 0.8rem;
        font-size: 0.85rem;
    }
    /*solucion pcp */

    .cart-table td[data-label="Acción:"] {
        margin-top: 0.8rem;
        padding-top: 0.8rem;
    }

    .cart-table td[data-label="Acción:"]:before {
        display: none;
    }

    .cart-summary {
        font-size: 1.2rem;
        padding-top: 0.8rem;
    }

    .footer {
        padding: 1rem 0.8rem;
        margin-top: 2rem;
    }

    .cart-actions .continue-shopping-button,
    .cart-actions .mercadopago-pay-button,
    .cart-actions form {
        padding: 0.6rem 1.2rem;
        font-size: 0.9rem;
        max-width: 250px;
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
                    <tr data-id="{{ $item['id'] }}"> {{-- data-id es el ID del CartItem (logueado) o product_id (invitado) --}}
                        <td data-label="Producto:">
                            <img src="{{ asset($item['image_url']) }}" alt="{{ $item['name'] }}" class="cart-item-image">
                            <span class="cart-item-name">{{ $item['name'] }}</span>
                        </td>
                        {{-- Utiliza $item['subtotal_item_gross'] / $item['quantity'] para el precio unitario bruto --}}
                        <td data-label="Precio Unitario:">${{ number_format($item['subtotal_item_gross'] / $item['quantity'], 2, ',', '.') }}</td>
                        <td data-label="Cantidad:">
                            <div class="quantity-controls">
                                <button type="button" class="quantity-button decrease-quantity" data-id="{{ $item['id'] }}">-</button>
                                <input type="number" value="{{ $item['quantity'] }}" min="0" class="quantity-input item-quantity-input" data-id="{{ $item['id'] }}">
                                <button type="button" class="quantity-button increase-quantity" data-id="{{ $item['id'] }}">+</button>
                            </div>
                        </td>
                        <td class="item-subtotal-gross" data-id="{{ $item['id'] }}" data-label="Subtotal:">${{ number_format($item['subtotal_item_gross'], 2, ',', '.') }}</td>
                        <td data-label="Acción:">
                            <button type="button" class="remove-button" data-id="{{ $item['id'] }}">Eliminar</button>
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
                        // Si el carrito está vacío, updateCartUI ya lo maneja.
                        // Si un item específico fue eliminado (cantidad 0), remover su fila.
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
                    
                    if (target.classList.contains('remove-item')) {
                        // No usar confirm() directamente. Si necesitas confirmación, usa un modal.
                        // Por ahora, solo envía la petición. El backend maneja el resultado.
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

                        // Si la cantidad cambia a 0, se procesa como eliminación
                        if (newQuantity === 0) {
                            // No usar confirm() directamente. Si necesitas confirmación, usa un modal.
                            // Por ahora, solo envía la petición. El backend maneja el resultado.
                        }
                        sendCartRequest('{{ route('cart.update') }}', 'POST', { id: itemId, quantity: newQuantity });
                    }
                });
            }

            // Inicializar la UI con los datos que vienen del servidor (al cargar la página)
            // Aseguramos que los valores sean números.
            updateCartUI({
                cartItems: @json($cartItems),
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