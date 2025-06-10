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

body {
    font-family: 'Open Sans', sans-serif;
    background-color: var(--background-light);
    margin: 0;
    padding: 0;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    line-height: 1.6;
    color: var(--text-dark);
    font-size: 16px;
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

/* Cart Specific Styles */
.cart-container {
    max-width: 1000px;
    margin: 3rem auto;
    padding: 2rem;
    background-color: var(--card-background);
    border-radius: 1rem;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
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
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    flex-wrap: wrap;
    gap: 1.5rem;
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
    flex-grow: 1; /* Permite que los botones crezcan para llenar el espacio */
    min-width: 200px; /* Asegura un tamaño mínimo para el botón */
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


/* Footer Styles (copied from welcome.blade.php for consistency) */
.footer {
    background-color: var(--primary-color);
    color: var(--button-text);
    padding: 1.5rem 2rem;
    text-align: center;
    font-size: 0.9rem;
    border-top-left-radius: 2rem;
    border-top-right-radius: 2rem;
    margin-top: 4rem;
}

.footer p {
    margin: 0;
    opacity: 0.8;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .navbar {
        padding: 1rem 1rem;
        flex-wrap: wrap; /* Permite que los elementos se envuelvan si es necesario */
        /* Eliminamos flex-direction: row y align-items: center ya que ya es el valor predeterminado y es bueno */
    }
    .navbar-brand {
        margin-bottom: 0; /* Asegura que no haya margen inferior extra */
    }
    .navbar-links {
        display: none; /* Oculta los enlaces de navegación tradicionales */
    }
    .menu-toggle {
        display: block; /* Muestra el botón de menú hamburguesa */
    }
    /* No es necesario redefinir .navbar-links-mobile .logout-button-navbar aquí,
       ya que su definición general es adecuada y se aplica cuando se activa. */

    .cart-container {
        margin: 2rem 1rem; /* Reduce los márgenes laterales en pantallas pequeñas */
        padding: 1rem; /* Reduce el padding interno */
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
        display: none; /* Oculta los encabezados de la tabla en pantallas pequeñas */
    }

    .cart-table tr {
        margin-bottom: 1.5rem; /* Aumenta un poco el espacio entre elementos del carrito */
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        padding: 0.8rem; /* Añade padding a cada fila como si fuera una tarjeta */
    }

    .cart-table td {
        border-bottom: none; /* Elimina los bordes inferiores entre celdas */
        text-align: right; /* Alinea el valor a la derecha */
        padding: 0.5rem 0.8rem; /* Ajusta el padding de las celdas */
        position: relative;
        flex-basis: 48%; /* Dos columnas por fila */
    }

    .cart-table td:before {
        content: attr(data-label);
        position: absolute; /* Para alinear el label a la izquierda de la celda */
        left: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-light);
        font-size: 0.75rem; /* Ajusta el tamaño de la fuente del label */
        top: 0.5rem; /* Ajusta la posición vertical del label */
    }

    /* Specific overrides for first column (product name/image) to take full width */
    .cart-table td:first-child {
        text-align: left;
        flex-basis: 100%; /* Toma todo el ancho */
        border-bottom: 1px solid var(--border-color); /* Retiene el borde inferior para separar la info del producto */
        padding-bottom: 1rem;
        display: flex; /* Para alinear imagen y nombre */
        align-items: center;
        padding-top: 0.5rem; /* Ajusta padding superior */
    }

    .cart-table td:first-child:before {
        content: ""; /* Oculta el label para el nombre del producto */
    }

    .cart-item-image {
        width: 50px;
        height: 50px;
        margin-right: 0.8rem; /* Aumenta un poco el espacio */
    }

    .quantity-controls {
        justify-content: flex-end; /* Alinea los controles de cantidad a la derecha */
        width: 100%; /* Asegura que ocupe todo el ancho de su celda */
        margin-top: 0.5rem; /* Espacio superior para separar del label */
        flex-basis: 100%; /* Asegura que ocupe una línea completa en la "tarjeta" */
    }
    .quantity-input {
        width: 50px; /* Ajusta el ancho del input de cantidad */
    }


    .remove-button {
        width: 100%; /* Ocupa todo el ancho disponible */
        margin-top: 0.8rem; /* Espacio superior para separarlo de los controles de cantidad */
        flex-basis: 100%; /* Asegura que ocupe una línea completa en la "tarjeta" */
    }

    .cart-summary {
        font-size: 1.5rem;
        text-align: center; /* Centra el total en móvil */
        padding-top: 1rem; /* Reduce el padding superior */
    }

    #cart-total {
        display: block; /* Muestra el total en su propia línea */
        margin-left: 0; /* Elimina margen izquierdo */
        margin-top: 0.5rem; /* Añade margen superior */
    }

    .empty-cart-message {
        font-size: 1.2rem;
    }

    .footer {
        padding: 1.5rem 1rem;
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
    }

    .cart-actions {
        flex-direction: column; /* Apila los botones de acción */
        align-items: stretch; /* Estira los botones para ocupar el ancho completo */
        gap: 1rem; /* Reduce el espacio entre botones apilados */
    }

    .continue-shopping-button,
    .mercadopago-pay-button {
        width: 100%; /* Asegura que los botones ocupen todo el ancho */
        max-width: 300px; /* Opcional: limita el ancho máximo en pantallas muy grandes */
        margin: 0 auto; /* Centra los botones apilados */
    }
}

@media (max-width: 480px) {
    /* Ajustes adicionales para pantallas muy pequeñas (ej. smartphones antiguos) */
    body {
        font-size: 15px; /* Ligeramente más pequeña la fuente base */
    }

    .navbar {
        padding: 0.8rem 0.8rem; /* Más pequeño el padding del navbar */
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
        padding: 0.5rem 0.8rem;
    }

    .cart-table td:before {
        position: static; /* Coloca el label antes del contenido */
        display: inline-block; /* Ocupa su propio espacio en la línea */
        margin-right: 0.5rem; /* Espacio entre el label y el valor */
        font-size: 0.7rem; /* Reduce aún más el tamaño del label */
    }

    .cart-table td:first-child {
        padding-bottom: 0.8rem;
    }

    .cart-item-image {
        width: 45px;
        height: 45px;
        margin-right: 0.5rem;
    }

    .quantity-controls {
        justify-content: flex-start; /* Alinea los controles de cantidad a la izquierda */
        flex-wrap: wrap; /* Permite que los botones y el input de cantidad se envuelvan */
        gap: 0.2rem; /* Reduce el espacio entre elementos de cantidad */
    }
    .quantity-input {
        width: 40px; /* Ajusta el ancho del input de cantidad para muy pequeñas */
        padding: 0.3rem; /* Reduce el padding del input */
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
    }

    .cart-summary {
        font-size: 1.3rem;
        padding-top: 1rem;
    }

    .footer {
        padding: 1rem 0.8rem;
    }

    .continue-shopping-button,
    .mercadopago-pay-button {
        padding: 0.6rem 1.5rem; /* Ajusta el padding de los botones de acción */
        font-size: 0.9rem; /* Reduce el tamaño de la fuente de los botones de acción */
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