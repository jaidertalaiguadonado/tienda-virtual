<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Dashboard</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/js/app.js'])

    {{-- ESTILOS ESPECÍFICOS PARA ESTA PÁGINA (todo el CSS aquí) --}}
    <style>
        /* Variables CSS para colores - ¡Fácil de cambiar! */
        :root {
            --primary-color: #007BFF; /* Usando el azul de tu página principal */
            --primary-light: #66B2FF; /* Un azul más claro */
            --secondary-color: #17A2B8; /* Tu azul secundario */
            --text-dark: #212529; /* Tu color de texto oscuro */
            --text-light: #6C757D; /* Tu color de texto claro */
            --background-light: #F8F9FA; /* Tu color de fondo claro */
            --card-background: #ffffff;
            --border-color: #DEE2E6; /* Tu color de borde */
            --success-bg: #d4edda;
            --success-text: #155724;
            --error-bg: #f8d7da;
            --error-text: #721c24;
            --logout-color-text: #DC3545; /* Rojo para el texto de logout en el header (si quieres un color específico) */
            --logout-color-bg: #ffffff; /* Blanco para el fondo del botón de logout en el header */
            --logout-hover-bg: #f0f0f0; /* Gris claro para hover del botón de logout */
        }

        /* Base y tipografía */
        body {
            font-family: 'Figtree', sans-serif;
            background-color: var(--background-light);
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            line-height: 1.6;
            color: var(--text-dark);
            font-size: 16px;
        }

        /* Contenedor del encabezado de la página */
        .page-header-container {
            background-color: var(--primary-color); /* Fondo azul principal */
            border-bottom: 1px solid var(--primary-light); /* Borde azul claro */
            padding: 1.5rem 2rem; /* Añadí padding-left y padding-right por consistencia */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Sombra para el header */
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header-content {
            flex-grow: 1; /* Permite que el contenido crezca */
        }

        /* Título de la página */
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--card-background); /* Color blanco para el título */
            line-height: 1.2;
            margin: 0;
        }

        /* Contenedor principal del contenido */
        .main-content-wrapper {
            padding-top: 2rem;
            padding-bottom: 4rem;
        }

        /* Contenedor central del contenido (centra la tarjeta) */
        .content-container {
            max-width: 1280px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 2rem;
        }

        /* Contenedor de la tarjeta (el panel blanco con sombra) */
        .card-container {
            background-color: var(--card-background);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
        }

        .card-content {
            font-size: 1.15rem;
            color: var(--text-dark);
            margin-bottom: 2rem;
        }

        /* Contenedor de los botones de acción */
        .action-buttons-container {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        /* Estilo base para los botones de acción */
        .action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 2rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 1rem;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            text-decoration: none;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
            min-width: 200px;
        }

        .action-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .action-button:focus {
            outline: none;
            box-shadow: 0 0 0 5px rgba(0, 123, 255, 0.4); /* Anillo de enfoque con primary-color */
        }

        .action-button:active {
            transform: translateY(0);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }

        /* Colores específicos para los botones */
        .action-button.primary {
            background-color: var(--primary-color);
        }

        .action-button.primary:hover {
            background-color: var(--primary-light);
        }

        .action-button.secondary {
            background-color: var(--secondary-color);
        }

        .action-button.secondary:hover {
            background-color: #138D9E; /* Un tono un poco más oscuro de secondary-color */
        }

        /* Estilo para el botón de cerrar sesión en el HEADER */
        .logout-button {
            background-color: var(--logout-color-bg); /* Fondo blanco */
            color: var(--primary-color); /* Texto azul */
            padding: 0.75rem 1.2rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease, color 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .logout-button:hover {
            background-color: var(--logout-hover-bg); /* Fondo gris claro en hover */
            color: var(--primary-color); /* Mantener texto azul */
            transform: translateY(-1px);
        }

        .logout-button:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.4); /* Anillo de enfoque con primary-color */
        }

        /* Medias Queries (Responsive) */
        @media (max-width: 992px) { /* Tabletas */
            .page-header-container {
                flex-direction: column; /* Apilar título y botón */
                align-items: flex-start;
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
            .page-header-content {
                margin-bottom: 1rem; /* Espacio entre título y botón */
                width: 100%; /* Asegura que ocupe todo el ancho disponible */
            }
            .page-title {
                font-size: 1.8rem;
            }
            .card-container {
                padding: 1.5rem;
            }
            .action-button {
                padding: 0.8rem 1.8rem;
                font-size: 0.95rem;
                min-width: 180px;
            }
            .action-buttons-container {
                gap: 1rem;
            }
            .logout-button {
                width: auto; /* Mantener auto width */
                margin-top: 0; /* No hay margin-top extra aquí */
            }
        }

        @media (max-width: 768px) { /* Móviles */
            .page-header-container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .page-header-content,
            .content-container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .page-title {
                font-size: 1.6rem;
            }
            .card-container {
                padding: 1.2rem;
                border-radius: 0.75rem;
            }
            .card-content {
                font-size: 1rem;
                margin-bottom: 1.5rem;
            }
            .action-buttons-container {
                flex-direction: column; /* Apilar botones verticalmente */
                gap: 1rem;
            }
            .action-button {
                width: 100%; /* Botones de ancho completo */
                padding: 0.9rem 1.5rem;
                font-size: 0.95rem;
                min-width: unset;
            }
            .logout-button {
                width: 100%; /* Botón de logout también a ancho completo en móvil */
                margin-top: 1rem; /* Espacio extra si el header se apila */
            }
        }
    </style>
</head>
<body class="font-sans antialiased">
    <header class="page-header-container">
        <div class="page-header-content">
            <h2 class="page-title">
                {{ __('TIENDA JD') }}
            </h2>
        </div>
        {{-- Formulario para cerrar sesión --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-button">
                {{ __('Cerrar Sesión') }}
            </button>
        </form>
    </header>

    <div class="main-content-wrapper">
        <div class="content-container">
            <div class="card-container">
                <div class="card-content">
                    {{ __("¡Bienvenido a tu panel de administración! Aquí puedes gestionar tus categorías y productos.") }}
                </div>

                <div class="action-buttons-container">
                    <a href="{{ route('admin.categories.index') }}" class="action-button primary">
                        {{ __('Ver/Gestionar Categorías') }}
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="action-button secondary">
                        {{ __('Ver/Gestionar Productos') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>