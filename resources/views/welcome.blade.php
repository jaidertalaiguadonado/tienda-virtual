<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Tienda JD') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">

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

        .navbar {
            background-color: var(--card-background);
            border-bottom: 1px solid var(--border-color);
            padding: 1.2rem 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
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
            z-index: 1000;
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

        .hero-section {
            background-color: var(--primary-color);
            color: var(--button-text);
            padding: 4rem 2rem;
            text-align: center;
            border-bottom-left-radius: 2rem;
            border-bottom-right-radius: 2rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            margin-bottom: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.1;
            max-width: 800px;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 2rem;
            opacity: 0.9;
            max-width: 700px;
        }

        .hero-button {
            display: inline-block;
            background-color: var(--secondary-color);
            color: var(--button-text);
            padding: 0.9rem 2.5rem;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 1.1rem;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .hero-button:hover {
            background-color: #138D9E;
            transform: translateY(-2px);
        }

        .products-section {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
            margin-bottom: 4rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 3rem;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background-color: var(--card-background);
            border-radius: 1rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .product-card-image-container {
            width: 100%;
            height: 250px;
            overflow: hidden;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .product-card-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            display: block;
        }

        .product-card-body {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-card-category {
            font-size: 0.9rem;
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .product-card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.75rem;
            line-height: 1.3;
        }

        .product-card-description {
            font-size: 0.95rem;
            color: var(--text-light);
            margin-bottom: 1rem;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-card-price {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--secondary-color);
            margin-top: 1rem;
            margin-bottom: 0.75rem;
        }

        .product-card-stock {
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            padding: 0.3rem 0.5rem;
            border-radius: 0.3rem;
            margin-top: auto;
        }

        .stock-available {
            background-color: var(--stock-available);
            color: var(--button-text);
        }

        .stock-unavailable {
            background-color: var(--stock-unavailable);
            color: var(--button-text);
        }

        .footer {
            background-color: var(--primary-color);
            color: var(--button-text);
            padding: 1.5rem 2rem;
            text-align: center;
            font-size: 0.9rem;
            border-top-left-radius: 2rem;
            border-top-right-radius: 2rem;
        }

        .footer p {
            margin: 0;
            opacity: 0.8;
        }

        @media (max-width: 1024px) {
            .hero-title {
                font-size: 3rem;
            }
            .hero-subtitle {
                font-size: 1.3rem;
            }
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1.5rem;
            }
            .product-card-image-container {
                height: 200px;
            }
            .product-card-title {
                font-size: 1.4rem;
            }
            .product-card-price {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                padding: 1rem 1rem;
                flex-wrap: wrap;
            }
            .navbar-brand {
                margin-bottom: 0;
            }
            .navbar-links {
                display: none;
            }
            .menu-toggle {
                display: block;
            }
            .hero-section {
                padding: 3rem 1rem;
                border-bottom-left-radius: 1rem;
                border-bottom-right-radius: 1rem;
            }
            .hero-title {
                font-size: 2.5rem;
            }
            .hero-subtitle {
                font-size: 1.1rem;
            }
            .hero-button {
                padding: 0.7rem 1.5rem;
                font-size: 1rem;
            }
            .products-section {
                padding: 0 1rem;
            }
            .section-title {
                font-size: 2.2rem;
                margin-bottom: 2rem;
            }
            .product-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            .product-card-image-container {
                height: 220px;
            }
            .product-card-body {
                padding: 1.2rem;
            }
            .product-card-title {
                font-size: 1.3rem;
            }
            .product-card-price {
                font-size: 1.5rem;
            }
            .footer {
                padding: 1.5rem 1rem;
                border-top-left-radius: 1rem;
                border-top-right-radius: 1rem;
            }
            .logout-button-navbar {
                width: calc(100% - 2rem);
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 2rem;
            }
            .hero-subtitle {
                font-size: 1rem;
            }
            .navbar-brand {
                font-size: 1.5rem;
            }
            .product-card-title {
                font-size: 1.2rem;
            }
            .product-card-price {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body class="font-sans antialiased">
    <nav class="navbar">
        <a href="{{ route('welcome') }}" class="navbar-brand">{{ config('app.name', 'Mi Tienda') }}</a>
        <button class="menu-toggle" aria-label="Toggle navigation menu">
            ☰
        </button>
        <div class="navbar-links">
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

    <section class="hero-section">
        <h1 class="hero-title">Descubre Nuestros Productos Únicos</h1>
        <p class="hero-subtitle">Explora una colección cuidadosamente seleccionada para ti.</p>
        <a href="#products-section" class="hero-button">Ver Productos</a>
    </section>

    <section id="products-section" class="products-section">
        <h2 class="section-title">Nuestros Últimos Productos</h2>
        <div class="product-grid">
            @forelse ($products as $product)
                <div class="product-card">
                    <div class="product-card-image-container">
                        @if ($product->image_path)
                            {{-- CAMBIO REALIZADO AQUÍ: Se usa directamente la URL de la imagen --}}
                            <img src="{{ $product->image_path }}" alt="{{ $product->name }}" class="product-card-image">
                        @else
                            <img src="https://via.placeholder.com/400x300?text=Sin+Imagen" alt="Sin Imagen" class="product-card-image">
                        @endif
                    </div>
                    <div class="product-card-body">
                        <div>
                            @if ($product->category)
                                <p class="product-card-category">{{ $product->category->name }}</p>
                            @endif
                            <h3 class="product-card-title">{{ $product->name }}</h3>
                            <p class="product-card-description">{{ $product->description }}</p>
                        </div>
                        <p class="product-card-price">${{ number_format($product->price, 2) }}</p>

                        <div class="product-card-actions">
                            @if ($product->is_in_stock ?? true)
                                <span class="product-card-stock stock-available">En Stock</span>
                            @else
                                <span class="product-card-stock stock-unavailable">Agotado</span>
                            @endif
                        </div>

                    </div>
                </div>
            @empty
                <p style="grid-column: 1 / -1; text-align: center; color: var(--text-light); font-size: 1.2rem;">No hay productos disponibles en este momento.</p>
            @endforelse
        </div>
    </section>

    <footer class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Mi Tienda') }}. Todos los derechos reservados.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const mobileMenu = document.querySelector('.navbar-links-mobile');

            if (menuToggle && mobileMenu) {
                menuToggle.addEventListener('click', function() {
                    mobileMenu.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>