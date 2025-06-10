<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Tienda JD') }} - Carrito</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">

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
    height: 100%; /* Fundamental para que flexbox funcione en el body */
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
    flex-direction: column; /* Apila los hijos verticalmente */
    min-height: 100vh; /* Asegura que ocupe al menos la altura de la ventana */
}

a {
    text-decoration: none;
    color: inherit;
}

/* Navbar Styles (copied from welcome.blade.php for consistency) */
.navbar {
    background-color: var(--card-background);
    border-bottom: 1px solid var(--border-color);
    padding: 1.2rem 2rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 1000; /* Asegura que el navbar esté por encima de otros elementos */
    flex-shrink: 0; /* Evita que el navbar se encoja */
}

.navbar-brand {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--primary-color);
}

.menu-toggle {
    display: none; /* Oculto por defecto en desktop */
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
    display: none; /* Oculto por defecto, se mostrará con JS */
    flex-direction: column;
    background-color: var(--card-background);
    position: absolute;
    top: 100%; /* Justo debajo del navbar */
    left: 0;
    width: 100%;
    border-top: 1px solid var(--border-color);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    z-index: 999; /* Ligeramente debajo del navbar principal */
    padding: 1rem 0;
}

.navbar-links-mobile.active {
    display: flex; /* Se muestra cuando la clase 'active' está presente */
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
    color: white;
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
    flex-grow: 1; /* Permite que este contenedor ocupe todo el espacio disponible */
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

.cart-summary {
    text-align: right;
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--text-dark);
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 2px solid var(--border-color);
}

#cart-total {
    color: var(--primary-color);
    margin-left: 1rem;
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
    justify-content: center; /* Centra los botones horizontalmente */
    align-items: center;
    margin-top: 2rem;
    flex-wrap: wrap;
    gap: 1.5rem; /* Aumenta el espacio entre botones */
}

/* Asegura que los botones ocupen el mismo ancho posible */
.cart-actions .continue-shopping-button,
.cart-actions .mercadopago-pay-button,
.cart-actions form { /* El formulario también debe ser flexible */
    flex: 1; /* Permite que crezcan y compartan el espacio */
    min-width: 250px; /* Tamaño mínimo para cada botón/formulario */
    max-width: 45%; /* Limita el ancho para que no se estiren demasiado en pantallas grandes */
    box-sizing: border-box; /* Incluye padding y border en el width */
}

/* Para el formulario de Mercado Pago, asegúrate de que el botón dentro se estire */
.cart-actions form button {
    width: 100%; /* El botón ocupa todo el ancho del formulario */
}

.continue-shopping-button,
.mercadopago-pay-button {
    display: inline-block; /* Para mantener padding y transform */
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

.continue-shopping-button {
    background-color: var(--secondary-color);
    color: var(--button-text);
}

.continue-shopping-button:hover {
    background-color: #138D9E;
    transform: translateY(-2px);
}

/* Estilos específicos para el botón de Mercado Pago */
.mercadopago-pay-button {
    background-color: var(--mercadopago-button-color);
    color: var(--mercadopago-text-color);
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
    margin-top: 4rem; /* Espacio antes del footer */
    flex-shrink: 0; /* Evita que el footer se encoja */
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
        margin-right: auto; /* Empuja el toggle a la derecha */
    }
    .navbar-links {
        display: none;
    }
    .menu-toggle {
        display: block;
    }

    .cart-container {
        margin: 2rem 1rem;
        padding: 1.5rem; /* Ajusta el padding interno */
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
        margin-bottom: 1rem; /* Ajusta el espacio entre "tarjetas" */
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between; /* Distribuye elementos horizontalmente */
        align-items: flex-start; /* Alinea al inicio de la "tarjeta" */
        padding: 0.8rem;
    }

    .cart-table td {
        border-bottom: none;
        text-align: right;
        padding: 0.5rem 0.5rem; /* Ajusta el padding de las celdas */
        position: relative;
        flex-basis: 48%; /* Dos columnas por fila */
    }

    .cart-table td:before {
        content: attr(data-label);
        position: absolute;
        left: 0.5rem; /* Ajusta la posición del label */
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-light);
        font-size: 0.7rem;
        top: 0.5rem;
    }

    .cart-table td:first-child {
        text-align: left;
        flex-basis: 100%;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.8rem;
        margin-bottom: 0.5rem; /* Pequeño margen para separar de los otros datos */
        display: flex;
        align-items: center;
        padding-top: 0.5rem;
    }

    .cart-table td:first-child:before {
        content: ""; /* Oculta el label para el nombre del producto */
    }

    .cart-item-image {
        width: 50px;
        height: 50px;
        margin-right: 0.6rem;
    }

    .cart-item-name {
        flex-grow: 1; /* Permite que el nombre ocupe el espacio restante */
        word-wrap: break-word; /* Rompe palabras largas */
        overflow-wrap: break-word; /* Asegura que palabras muy largas se rompan */
    }

    .quantity-controls {
        justify-content: flex-end; /* Alinea los controles de cantidad a la derecha */
        width: 100%;
        margin-top: 0.5rem;
        flex-basis: 100%; /* Asegura que ocupe una línea completa en la "tarjeta" */
    }
    .quantity-input {
        width: 50px;
    }

    .remove-button {
        width: 100%; /* Ocupa todo el ancho disponible */
        margin-top: 0.8rem;
        flex-basis: 100%; /* Asegura que ocupe una línea completa en la "tarjeta" */
        align-self: flex-end; /* Alinea el botón eliminar a la derecha */
    }

    .cart-summary {
        font-size: 1.5rem;
        text-align: center;
        padding-top: 1rem;
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
        margin-top: 3rem; /* Ajusta el margen superior del footer en móvil */
    }

    .cart-actions {
        flex-direction: column; /* Apila los botones de acción */
        align-items: center; /* Centra los botones apilados */
        gap: 1rem; /* Reduce el espacio entre botones apilados */
    }

    .cart-actions .continue-shopping-button,
    .cart-actions .mercadopago-pay-button,
    .cart-actions form {
        width: 100%; /* Ocupan todo el ancho disponible */
        max-width: 300px; /* Limita el ancho máximo para que no sean demasiado grandes */
        margin: 0 auto; /* Centra individualmente */
    }
}

@media (max-width: 480px) {
    body {
        font-size: 15px;
    }

    .navbar {
        padding: 0.8rem 0.8rem;
    }

    .navbar-brand {
        font-size: 1.5rem;
    }

    .cart-title {
        font-size: 1.8rem;
        margin-bottom: 1rem;
    }

    .cart-table td {
        flex-basis: 100%; /* Cada celda toma su propia línea en pantallas muy pequeñas */
        text-align: left; /* Alinea el contenido a la izquierda */
        padding: 0.3rem 0; /* Reduce padding para un diseño más compacto */
        position: static; /* Importante para que ::before se comporte como inline */
    }

    .cart-table td:before {
        position: static; /* Coloca el label antes del contenido */
        display: inline-block; /* Ocupa su propio espacio en la línea */
        margin-right: 0.5rem; /* Espacio entre el label y el valor */
        font-size: 0.7rem; /* Reduce aún más el tamaño del label */
        color: var(--text-light); /* Asegura que el color sea consistente */
        min-width: 60px; /* Puede ser útil para alinear labels */
    }

    .cart-table td:first-child {
        padding-bottom: 0.8rem;
        border-bottom: 1px solid var(--border-color); /* Mantén el separador para el producto */
        margin-bottom: 0.5rem;
    }

    .cart-table td:first-child:before {
        content: none; /* Oculta el label para el nombre del producto */
    }

    .cart-item-image {
        width: 45px;
        height: 45px;
        margin-right: 0.5rem;
    }

    .quantity-controls {
        justify-content: flex-start; /* Alinea los controles de cantidad a la izquierda */
        flex-wrap: wrap; /* Permite que los botones y el input de cantidad se envuelvan */
        gap: 0.2rem;
    }
    .quantity-input {
        width: 40px;
        padding: 0.3rem;
    }
    .quantity-button {
        width: 25px;
        height: 25px;
        font-size: 1rem;
    }

    .remove-button {
        margin-top: 0.5rem;
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
        align-self: flex-start; /* Alinea a la izquierda si flex-direction es column */
    }
    .cart-table td[data-label="Acción:"] {
        padding-top: 0.5rem;
        border-top: 1px dashed var(--border-color); /* Separador visual sutil */
        margin-top: 0.5rem;
        flex-basis: 100%;
    }

    .cart-summary {
        font-size: 1.3rem;
        padding-top: 1rem;
    }

    .footer {
        padding: 1rem 0.8rem;
        margin-top: 2rem; /* Reduce el margen superior del footer en pantallas muy pequeñas */
    }

    .cart-actions .continue-shopping-button,
    .cart-actions .mercadopago-pay-button,
    .cart-actions form {
        padding: 0.6rem 1.5rem;
        font-size: 0.9rem;
        max-width: 260px; /* Un poco más reducido para móviles muy pequeños */
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

        {{-- La tabla y el mensaje de carrito vacío siempre existen en el DOM.
             Su visibilidad inicial se controla con `style` y luego con JS. --}}
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
                    <tr data-product-id="{{ $item['product_id'] ?? $item['id'] }}">
                        <td data-label="Producto:">
                            <img src="{{ $item['image_path'] ?? $item['image'] ?? 'https://via.placeholder.com/60x60?text=Sin+Imagen' }}" alt="{{ $item['name'] }}" class="cart-item-image">
                            <span class="cart-item-name">{{ $item['product']->name ?? $item['name'] }}</span>
                        </td>
                        <td data-label="Precio:">$<span class="item-price">{{ number_format(($item['price_at_addition'] ?? $item['price']), 2, ',', '.') }}</span></td>
                        <td data-label="Cantidad:">
                            <div class="quantity-controls">
                                <button class="quantity-button decrease-quantity" data-product-id="{{ $item['product_id'] ?? $item['id'] }}">-</button>
                                <input type="number" class="quantity-input" value="{{ $item['quantity'] }}" min="1" data-product-id="{{ $item['product_id'] ?? $item['id'] }}">
                                <button class="quantity-button increase-quantity" data-product-id="{{ $item['product_id'] ?? $item['id'] }}">+</button>
                            </div>
                        </td>
                        <td data-label="Subtotal:" class="item-subtotal">${{ number_format((($item['price_at_addition'] ?? $item['price']) * $item['quantity']), 2, ',', '.') }}</td>
                        <td data-label="Acción:">
                            <button class="remove-button" data-product-id="{{ $item['product_id'] ?? $item['id'] }}">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="cart-summary" style="display: {{ empty($cartItems) ? 'none' : 'block' }};">
            Total: $<span id="cart-total">{{ number_format($total, 2, ',', '.') }}</span>
        </div>

        <p class="empty-cart-message" style="display: {{ empty($cartItems) ? 'block' : 'none' }};">Tu carrito está vacío. ¡Empieza a añadir productos!</p>

        {{-- Contenedor para los botones de acción del carrito --}}
        <div class="cart-actions" style="display: {{ empty($cartItems) ? 'none' : 'flex' }};">
            <a href="{{ route('welcome') }}" class="continue-shopping-button">Seguir Comprando</a>

            {{-- FORMULARIO PARA PAGAR CON MERCADO PAGO --}}
            <form action="{{ route('mercadopago.pay') }}" method="POST" id="mercadopago-checkout-form">
                @csrf
                {{-- Estos valores serán establecidos por JavaScript para asegurar que sean los correctos --}}
                <input type="hidden" name="amount" id="mercadopago-amount" value="{{ $total }}">
                <input type="hidden" name="description" id="mercadopago-description" value="Compra en Tienda JD">
                {{-- ¡ATENCIÓN!: Si tienes un ID de orden creado en tu base de datos antes del pago, puedes pasarlo aquí: --}}
                {{-- <input type="hidden" name="order_id" value="{{ $order->id }}"> --}}

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
            updateCartUI(null, null, null, {{ $total }}, {{ empty($cartItems) ? 0 : count($cartItems) }});
        });
    </script>
</body>
</html>