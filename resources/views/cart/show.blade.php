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


/* Variables CSS */
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
    --remove-button-color: #DC3545;
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
    color: var(--primary-color);
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
}

.remove-button {
    background-color: var(--remove-button-color);
    color: var(--button-text);
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
    background-color: var(--background-light); /* Usar variable para consistencia */
    border: 1px solid var(--border-color); /* Usar variable para consistencia */
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
    border-bottom: 1px dashed #e0e0e0; /* Considerar una variable si este color se repite */
}

.cart-summary div:last-child {
    border-bottom: none;
    font-weight: bold;
    font-size: 1.3rem;
    color: var(--success-color); /* Usar variable para el color verde */
}

/* Old #cart-total style - likely not needed with new summary structure */
/* #cart-total {
    color: var(--primary-color);
    margin-left: 1rem;
} */


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
    flex: 1;
    min-width: 250px;
    max-width: 45%;
    box-sizing: border-box;
}

.cart-actions form button {
    width: 100%;
}

.continue-shopping-button {
    background-color: var(--secondary-color); /* Usar variable para consistencia */
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
    background-color: var(--secondary-color); /* Ya está en la variable, solo necesitamos el hover */
    filter: brightness(0.9); /* Una forma de oscurecerlo sin otra variable */
    transform: translateY(-2px);
}

.mercadopago-pay-button {
    background-color: var(--mercadopago-button-color); /* Usar variable */
    color: var(--mercadopago-text-color); /* Usar variable */
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
    background-color: var(--mercadopago-button-hover); /* Usar variable */
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
        text-align: center; /* This might override the flex justification, but for mobile it might be desired for the whole summary block */
        padding-top: 1rem;
    }

    /* Keep the default flex behavior for summary items */
    .cart-summary div {
        justify-content: space-between; /* Ensure content is still justified */
        text-align: left; /* Ensure text labels are left-aligned */
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
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
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

        <table class="cart-table" style="display: {{ empty($cartItems) ? 'none' : 'table' }};">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="cart-items-body">
                @foreach ($cartItems as $item)
                    {{-- Usamos $item['id'] que debe ser el cart_item_id para autenticados o product_id para invitados --}}
                    <tr data-product-id="{{ $item['id'] }}">
                        <td data-label="Producto:">
                            {{-- Asegúrate que 'image' o 'image_path' contenga la URL correcta --}}
                            <img src="{{ $item['image'] ?? 'https://via.placeholder.com/60' }}" alt="{{ $item['name'] }}" class="cart-item-image">
                            <span class="cart-item-name">{{ $item['name'] }}</span>
                        </td>
                        {{-- Usamos 'price' ya que en formattedCartItems lo establecemos como 'price_at_addition' o 'price' --}}
                        <td data-label="Precio:">$<span class="item-price">{{ number_format($item['price'], 2, ',', '.') }}</span></td>
                        <td data-label="Cantidad:">
                            <div class="quantity-controls">
                                {{-- Usamos $item['id'] aquí también --}}
                                <button class="quantity-button decrease-quantity" data-product-id="{{ $item['id'] }}">-</button>
                                {{-- Usamos $item['id'] aquí también --}}
                                <input type="number" class="quantity-input" value="{{ $item['quantity'] }}" min="1" data-product-id="{{ $item['id'] }}">
                                {{-- Usamos $item['id'] aquí también --}}
                                <button class="quantity-button increase-quantity" data-product-id="{{ $item['id'] }}">+</button>
                            </div>
                        </td>
                        {{-- Usamos 'subtotal_item' que debe ser calculado en formattedCartItems --}}
                        <td data-label="Subtotal:" class="item-subtotal">${{ number_format($item['subtotal_item'], 2, ',', '.') }}</td>
                        <td data-label="Acción:">
                            {{-- Usamos $item['id'] aquí también --}}
                            <button class="remove-button" data-product-id="{{ $item['id'] }}">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- INICIO: Desglose de Totales con IVA y MP --}}
        <div class="cart-summary" style="display: {{ empty($cartItems) ? 'none' : 'flex' }};"> {{-- Cambiado a flex --}}
            <div>Subtotal Productos: $<span id="cart-products-subtotal">{{ number_format($subtotal, 2, ',', '.') }}</span></div>
            <div>IVA (19%): $<span id="cart-iva-amount">{{ number_format($iva_amount, 2, ',', '.') }}</span></div>
            <div>Comisión Mercado Pago (3.29%): $<span id="cart-mp-fee">{{ number_format($mp_fee_amount, 2, ',', '.') }}</span></div>
            <div>
                Total a pagar: $<span id="cart-final-total">{{ number_format($final_total, 2, ',', '.') }}</span>
            </div>
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
            const cartProductsSubtotalElement = document.getElementById('cart-products-subtotal');
            const cartIvaAmountElement = document.getElementById('cart-iva-amount');
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
            function updateCartUI(productId, newQuantity, newSubtotalItem, newProductsSubtotal, newTaxAmount, newMpFeeAmount, newFinalTotal, cartCount) {
                const row = cartItemsBody.querySelector(`tr[data-product-id="${productId}"]`);
                const cartIsEmpty = cartCount === 0;

                if (newQuantity <= 0) { 
                    if (row) {
                        row.remove(); 
                    }
                } else if (row) { 
                    row.querySelector('.quantity-input').value = newQuantity;
                    row.querySelector('.item-subtotal').textContent = '$' + formatNumberForUI(newSubtotalItem);
                }

                // Actualizar los nuevos elementos del resumen del carrito
                if (cartProductsSubtotalElement) {
                    cartProductsSubtotalElement.textContent = formatNumberForUI(newProductsSubtotal);
                }
                if (cartIvaAmountElement) {
                    cartIvaAmountElement.textContent = formatNumberForUI(newTaxAmount);
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
                            data.item.subtotal_item, 
                            data.subtotal, 
                            data.iva_amount, 
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
                            0, // Subtotal de item 0
                            data.subtotal, 
                            data.iva_amount, 
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
                            data.item.subtotal_item, 
                            data.subtotal, 
                            data.iva_amount, 
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
                {{ $subtotal }}, // Subtotal de productos del backend
                {{ $iva_amount }}, // IVA del backend
                {{ $mp_fee_amount }}, // Comisión MP del backend
                {{ $final_total }}, // Total final del backend
                {{ empty($cartItems) ? 0 : $cartItems->sum('quantity') }} // Conteos del backend
            );
        });
    </script>
</body>
</html>