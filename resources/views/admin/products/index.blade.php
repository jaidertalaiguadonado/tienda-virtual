<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Administración de Productos</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/js/app.js'])

    {{-- ESTILOS ESPECÍFICOS PARA ESTA PÁGINA (todo el CSS aquí) --}}
    <style>
        /* Variables CSS para colores - ¡Fácil de cambiar! */
        :root {
            --primary-color: #007BFF; /* Azul principal */
            --primary-light: #66B2FF; /* Azul claro */
            --secondary-color: #17A2B8; /* Azul cian secundario */
            --text-dark: #212529; /* Negro para texto principal */
            --text-light: #6C757D; /* Gris para texto secundario */
            --background-light: #F8F9FA; /* Fondo muy claro */
            --card-background: #ffffff; /* Fondo de tarjetas */
            --border-color: #DEE2E6; /* Color de borde general */
            --success-bg: #d4edda; /* Fondo de éxito (verde claro) */
            --success-text: #155724; /* Texto de éxito (verde oscuro) */
            --error-bg: #f8d7da; /* Fondo de error (rojo claro) */
            --error-text: #721c24; /* Texto de error (rojo oscuro) */
            --delete-button-color: #DC3545; /* Rojo para botón eliminar */
            --delete-button-hover: #b91c1c; /* Rojo más oscuro para hover */
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
            border-bottom: 1px solid var(--primary-light);
            padding: 1.5rem 2rem; /* Más padding en los lados */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 2.5rem;
        }

        .page-header-content {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0; /* Ya tiene padding el contenedor padre */
        }

        /* Título de la página (e.g., Administración de Productos) */
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
            padding: 0 2rem; /* Más padding en los lados */
        }

        /* Contenedor de la tarjeta (el panel blanco con sombra) */
        .card-container {
            background-color: var(--card-background);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-radius: 1rem;
            padding: 2rem;
        }

        /* Fila del botón "Crear Nuevo Producto" */
        .button-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 2rem;
        }

        .create-product-button {
            display: inline-flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            background-color: var(--secondary-color); /* Usar el color secundario */
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-decoration: none;
            white-space: nowrap;
            box-shadow: 0 4px 10px rgba(23, 162, 184, 0.2); /* Sombra con el color secundario */
        }

        .create-product-button:hover {
            background-color: #138D9E; /* Tono más oscuro de secondary-color */
            transform: translateY(-2px);
        }

        .create-product-button:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(23, 162, 184, 0.4); /* Anillo de foco con secondary-color */
        }

        .create-product-button:active {
            background-color: var(--secondary-color);
            transform: translateY(0);
        }

        /* Alertas de sesión (éxito/error) */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            position: relative;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            line-height: 1.4;
        }

        .success-alert {
            background-color: var(--success-bg);
            border: 1px solid #c3e6cb;
            color: var(--success-text);
        }

        .error-alert {
            background-color: var(--error-bg);
            border: 1px solid #f5c6cb;
            color: var(--error-text);
        }

        /* Contenedor de la tabla (para responsive) */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 0.75rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .products-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: var(--card-background);
            border-radius: 0.75rem;
        }

        .products-table thead {
            background-color: #f0f2f5;
        }

        .products-table th {
            padding: 1rem 1.5rem;
            text-align: left;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border-bottom: 1px solid var(--border-color);
            border-top: 1px solid var(--border-color);
        }

        .products-table th:first-child { border-top-left-radius: 0.75rem; }
        .products-table th:last-child { border-top-right-radius: 0.75rem; }

        .products-table tbody tr:last-child td {
            border-bottom: none;
        }
        .products-table td {
            padding: 1.2rem 1.5rem;
            white-space: nowrap;
            color: var(--text-dark);
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s ease;
        }

        .products-table tbody tr:hover {
            background-color: #fefefe;
        }

        /* Estilos específicos para la imagen del producto en la tabla */
        .product-image {
            height: 3.5rem;
            width: 3.5rem;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid var(--primary-light);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .no-image-placeholder {
            height: 3.5rem;
            width: 3.5rem;
            background-color: #e0e0e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            font-size: 0.75rem;
            flex-shrink: 0;
            border: 2px dashed var(--border-color);
        }

        /* Badge de estado (Activo/Inactivo) */
        .status-badge {
            padding: 0.3rem 0.8rem;
            display: inline-flex;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 1.5rem;
            text-transform: capitalize;
        }

        .status-badge.active {
            background-color: #e6ffed;
            color: #28a745;
        }

        .status-badge.inactive {
            background-color: #ffebe6;
            color: #dc3545;
        }

        /* Botones de acción en la tabla */
        .action-buttons {
            text-align: right;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            align-items: center;
        }

        .action-link {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.2s ease, transform 0.2s ease;
            white-space: nowrap;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
        }

        .action-link:hover {
            color: var(--primary-light);
            transform: translateY(-1px);
        }

        .inline-form {
            display: inline-block;
            margin: 0;
            padding: 0;
        }

        .action-button { /* Este es el botón de Eliminar */
            background: none;
            border: none;
            padding: 0.2rem 0.4rem;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            color: var(--delete-button-color); /* Rojo de peligro */
            transition: color 0.2s ease, transform 0.2s ease;
            white-space: nowrap;
            border-radius: 0.25rem;
        }

        .action-button:hover {
            color: var(--delete-button-hover); /* Rojo más oscuro */
            transform: translateY(-1px);
        }

        /* Estilo para cuando no hay productos */
        .no-products {
            padding: 1.5rem;
            text-align: center;
            color: var(--text-light);
            font-style: italic;
        }

        /* Medias Queries (Responsive) */
        @media (max-width: 992px) { /* Tabletas */
            .page-header-content,
            .content-container {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
            .page-title {
                font-size: 1.8rem;
            }
            .create-product-button {
                padding: 0.7rem 1.2rem;
                font-size: 0.9rem;
            }
            .products-table th,
            .products-table td {
                padding: 1rem 1rem;
                font-size: 0.85rem;
            }
            .product-image, .no-image-placeholder {
                height: 3rem;
                width: 3rem;
            }
            .action-buttons {
                flex-direction: column; /* Apilar para pantallas más pequeñas */
                align-items: flex-end;
                gap: 0.5rem;
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
                padding: 1.5rem;
                border-radius: 0.75rem;
            }
            .button-row {
                margin-bottom: 1.5rem;
                justify-content: center; /* Centrar botón en móviles */
            }
            .create-product-button {
                width: 100%; /* Botón ancho completo */
                justify-content: center;
                padding: 0.9rem 1rem;
                font-size: 1rem;
            }
            .products-table {
                font-size: 0.8rem;
            }
            .products-table th,
            .products-table td {
                padding: 0.8rem 0.8rem;
            }
            .product-image, .no-image-placeholder {
                height: 2.5rem;
                width: 2.5rem;
            }
            .action-buttons {
                flex-direction: row; /* Volver a fila si hay espacio */
                justify-content: space-around; /* Distribuir uniformemente */
                flex-wrap: wrap; /* Permitir que se envuelvan */
                gap: 0.5rem; /* Ajustar el gap */
            }
        }

        @media (max-width: 480px) { /* Móviles pequeños */
            .action-buttons {
                flex-direction: column; /* Apilar nuevamente en pantallas muy pequeñas */
                align-items: center;
            }
        }
    </style>
</head>
<body class="font-sans antialiased">
    <header class="page-header-container">
        <div class="page-header-content">
            <h2 class="page-title">
                {{ __('Administración de Productos') }}
            </h2>
        </div>
    </header>

    <div class="main-content-wrapper">
        <div class="content-container">
            <div class="card-container">
                <div class="button-row">
                    <a href="{{ route('admin.products.create') }}" class="create-product-button">
                        {{ __('Crear Nuevo Producto') }}
                    </a>
                </div>

                @if (session('success'))
                    <div class="alert success-alert" role="alert">
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert error-alert" role="alert">
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Imagen</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Categoría</th>
                                <th scope="col">Precio</th>
                                <th scope="col">Stock</th>
                                <th scope="col">Activo</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>
                                        {{-- Aquí el cambio importante: asumimos que image_path guarda la ruta relativa desde storage/app/public --}}
                                        @if ($product->image_path)
                                            <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="product-image">
                                        @else
                                            <div class="no-image-placeholder">No Img</div>
                                        @endif
                                    </td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                                    <td>${{ number_format($product->price, 2) }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td>
                                        @if ($product->is_active)
                                            <span class="status-badge active">Sí</span>
                                        @else
                                            <span class="status-badge inactive">No</span>
                                        @endif
                                    </td>
                                    <td class="action-buttons">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="action-link edit-link">Editar</a>
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-button delete-button" onclick="return confirm('¿Estás seguro de eliminar este producto?')">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="no-products">No hay productos disponibles.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>