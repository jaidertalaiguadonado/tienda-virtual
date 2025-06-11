<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Pedido #{{ $order->id }} - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
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

        /* General Content Area */
        .container {
            max-width: 1000px;
            margin: 3rem auto;
            padding: 2rem;
            background-color: var(--card-background);
            border-radius: 1rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            flex-grow: 1;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 2.5rem;
            text-align: center;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 768px) {
            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .info-section {
            background-color: var(--background-light);
            padding: 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border-color);
        }

        .info-section h4 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .info-section p {
            margin-bottom: 0.5rem;
            font-size: 1rem;
            color: var(--text-dark);
        }

        .info-section small {
            color: var(--text-light);
            font-size: 0.85rem;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            text-align: left;
        }

        .data-table th, .data-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .data-table th {
            background-color: var(--background-light);
            font-weight: 700;
            color: var(--text-dark);
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .data-table td {
            font-size: 1rem;
            color: var(--text-dark);
        }

        .back-button {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background-color: var(--secondary-color);
            color: var(--button-text);
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .back-button:hover {
            background-color: #138D9E; /* Darker shade of secondary-color */
            transform: translateY(-1px);
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
            .container {
                margin: 2rem 1rem;
                padding: 1.5rem;
            }
            .page-title {
                font-size: 2rem;
                margin-bottom: 1.5rem;
            }

            .data-table, .data-table tbody, .data-table tr, .data-table td, .data-table th {
                display: block;
                width: 100%;
            }

            .data-table thead {
                display: none;
            }

            .data-table tr {
                margin-bottom: 1rem;
                border: 1px solid var(--border-color);
                border-radius: 0.5rem;
                flex-direction: column;
                align-items: flex-start;
                padding: 1rem;
                box-sizing: border-box;
            }

            .data-table td {
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

            .data-table td:before {
                content: attr(data-label);
                font-weight: 700;
                text-transform: uppercase;
                color: var(--text-light);
                font-size: 0.75rem;
                min-width: 80px;
                flex-shrink: 0;
            }

            /* Hides label for first column on mobile (e.g., Product Name) */
            .data-table td:first-child:before {
                content: "";
                display: none;
            }
            .data-table td:first-child {
                border-bottom: 1px solid var(--border-color);
                padding-bottom: 0.8rem;
                margin-bottom: 0.5rem;
            }

            .footer {
                padding: 1.5rem 1rem;
                border-top-left-radius: 1rem;
                border-top-right-radius: 1rem;
                margin-top: 3rem;
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
            .page-title {
                font-size: 1.6rem;
                margin-bottom: 0.8rem;
            }
            .info-grid {
                gap: 1rem;
            }
            .info-section {
                padding: 1rem;
            }
            .info-section h4 {
                font-size: 1.2rem;
                margin-bottom: 0.8rem;
            }
            .info-section p {
                font-size: 0.9rem;
            }
            .data-table tr {
                padding: 0.8rem;
            }
            .data-table td {
                padding: 0.3rem 0;
                gap: 0.3rem;
            }
            .data-table td:before {
                font-size: 0.7rem;
                min-width: 65px;
            }
            .data-table td:first-child {
                padding-bottom: 0.6rem;
                margin-bottom: 0.4rem;
            }
            .footer {
                padding: 1rem 0.8rem;
                margin-top: 2rem;
            }
            .back-button {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="{{ route('dashboard') }}" class="navbar-brand">Admin Panel</a>
        <button class="menu-toggle" aria-label="Toggle navigation">&#9776;</button>
        <div class="navbar-links" id="navbarLinks">
            <a href="{{ route('dashboard') }}" class="navbar-link">Dashboard</a>
            <a href="{{ route('admin.categories.index') }}" class="navbar-link">Categorías</a>
            <a href="{{ route('admin.products.index') }}" class="navbar-link">Productos</a>
            <a href="{{ route('admin.orders.index') }}" class="navbar-link">Pedidos</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-button-navbar">Cerrar Sesión</button>
            </form>
        </div>
        <div class="navbar-links-mobile" id="navbarLinksMobile">
            <a href="{{ route('dashboard') }}" class="navbar-link">Dashboard</a>
            <a href="{{ route('admin.categories.index') }}" class="navbar-link">Categorías</a>
            <a href="{{ route('admin.products.index') }}" class="navbar-link">Productos</a>
            <a href="{{ route('admin.orders.index') }}" class="navbar-link">Pedidos</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-button-navbar">Cerrar Sesión</button>
            </form>
        </div>
    </nav>

    <div class="container">
        <h1 class="page-title">Detalles del Pedido #{{ $order->id }}</h1>

        <div class="info-grid">
            <div class="info-section">
                <h4>Información del Pedido</h4>
                <p><strong>ID de Pedido:</strong> {{ $order->id }}</p>
                <p><strong>Estado:</strong> {{ $order->status }}</p>
                <p><strong>Total:</strong> ${{ number_format($order->total, 0, ',', '.') }}</p>
                <p><strong>Fecha de Pedido:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="info-section">
                <h4>Información del Cliente</h4>
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

        <h3 class="page-title" style="font-size: 1.8rem; text-align: left; margin-bottom: 1.5rem;">Productos del Pedido</h3>
        @if ($order->items->isEmpty())
            <p>No hay productos en este pedido.</p>
        @else
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td data-label="Producto:">{{ $item->product->name ?? 'Producto no disponible' }}</td>
                                <td data-label="Cantidad:">{{ $item->quantity }}</td>
                                <td data-label="Precio Unitario:">${{ number_format($item->price, 0, ',', '.') }}</td>
                                <td data-label="Subtotal:">${{ number_format($item->quantity * $item->price, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <a href="{{ route('admin.orders.index') }}" class="back-button">Volver al Listado de Pedidos</a>
    </div>

    <footer class="footer">
        <p>&copy; {{ date('Y') }} Tu Empresa. Todos los derechos reservados.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const navbarLinksMobile = document.getElementById('navbarLinksMobile');

            if (menuToggle && navbarLinksMobile) {
                menuToggle.addEventListener('click', function() {
                    navbarLinksMobile.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>