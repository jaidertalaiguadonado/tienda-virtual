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
        
        :root {
            --primary-color: #007BFF; 
            --primary-light: #66B2FF; 
            --secondary-color: #17A2B8; 
            --text-dark: #212529; 
            --text-light: #6C757D; 
            --background-light: #F8F9FA; 
            --card-background: #ffffff;
            --border-color: #DEE2E6; 
            --success-bg: #d4edda;
            --success-text: #155724;
            --error-bg: #f8d7da;
            --error-text: #721c24;
            --logout-color-text: #DC3545; 
            --logout-color-bg: #ffffff; 
            --logout-hover-bg: #f0f0f0; 
        }

        
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

        
        .page-header-container {
            background-color: var(--primary-color); 
            border-bottom: 1px solid var(--primary-light); 
            padding: 1.5rem 2rem; 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); 
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header-content {
            flex-grow: 1; 
        }

        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--card-background); 
            line-height: 1.2;
            margin: 0;
        }

        
        .main-content-wrapper {
            padding-top: 2rem;
            padding-bottom: 4rem;
        }

        
        .content-container {
            max-width: 1280px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 2rem;
        }

        
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

        
        .action-buttons-container {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        
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
            box-shadow: 0 0 0 5px rgba(0, 123, 255, 0.4); 
        }

        .action-button:active {
            transform: translateY(0);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }

        
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
            background-color: #138D9E; 
        }

        
        .logout-button {
            background-color: var(--logout-color-bg); 
            color: var(--primary-color); 
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
            background-color: var(--logout-hover-bg); 
            color: var(--primary-color); 
            transform: translateY(-1px);
        }

        .logout-button:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.4); 
        }

        
        @media (max-width: 992px) { 
            .page-header-container {
                flex-direction: column; 
                align-items: flex-start;
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
            .page-header-content {
                margin-bottom: 1rem; 
                width: 100%; 
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
                width: auto; 
                margin-top: 0; 
            }
        }

        @media (max-width: 768px) { 
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
                flex-direction: column; 
                gap: 1rem;
            }
            .action-button {
                width: 100%; 
                padding: 0.9rem 1.5rem;
                font-size: 0.95rem;
                min-width: unset;
            }
            .logout-button {
                width: 100%; 
                margin-top: 1rem; 
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