<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Administración de Categorías</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/js/app.js'])

    {{-- ESTILOS ESPECÍFICOS PARA ESTA PÁGINA (todo el CSS aquí) --}}
    <style>
        /* Variables CSS para colores - ¡Fácil de cambiar! */
        :root {
            --primary-color: #6a67f1; /* Azul violeta */
            --primary-light: #8b89f8;
            --secondary-color: #fca311; /* Naranja vibrante */
            --text-dark: #333;
            --text-light: #555;
            --background-light: #f8f9fa;
            --card-background: #ffffff;
            --border-color: #e0e0e0;
            --success-bg: #d4edda;
            --success-text: #155724;
            --error-bg: #f8d7da;
            --error-text: #721c24;
            --danger-color: #dc3545; /* Para botones de eliminar/cancelar */
            --danger-light: #e65f6c;
            --edit-color: #007bff; /* Azul para editar */
            --edit-light: #0056b3;
            --delete-color: #dc3545; /* Rojo para eliminar */
            --delete-light: #c82333;
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
            background-color: var(--card-background);
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 2.5rem;
        }

        .page-header-content {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Título de la página */
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
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
        }

        /* Botón "Crear Nueva Categoría" */
        .create-button-container {
            margin-bottom: 1.5rem; /* mb-4 */
            display: flex;
            justify-content: flex-end; /* justify-end */
        }

        .create-button {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem; /* px-4 py-2 */
            background-color: var(--primary-color); /* bg-gray-800 */
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.85rem; /* text-xs */
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-decoration: none; /* Asegura que no se vea como un enlace subrayado */
            box-shadow: 0 4px 10px rgba(106, 103, 241, 0.2);
            white-space: nowrap;
        }

        .create-button:hover {
            background-color: var(--primary-light); /* hover:bg-gray-700 */
            transform: translateY(-2px);
        }

        .create-button:focus {
            outline: none;
            background-color: var(--primary-light); /* focus:bg-gray-700 */
            box-shadow: 0 0 0 4px rgba(106, 103, 241, 0.4); /* focus:ring-indigo-500 */
        }

        .create-button:active {
            background-color: var(--primary-color); /* active:bg-gray-900 */
            transform: translateY(0);
        }

        /* Mensajes de sesión (éxito/error) */
        .session-message {
            padding: 1rem 1.5rem; /* px-4 py-3 */
            border-width: 1px;
            border-radius: 0.5rem; /* rounded */
            margin-bottom: 1.5rem; /* mb-4 */
            position: relative;
            font-size: 0.95rem;
        }

        .session-message.success {
            background-color: var(--success-bg);
            border-color: #28a745; /* un poco más oscuro que el color de texto para el borde */
            color: var(--success-text);
        }

        .session-message.error {
            background-color: var(--error-bg);
            border-color: #dc3545; /* un poco más oscuro que el color de texto para el borde */
            color: var(--error-text);
        }

        /* Tabla */
        .table-container {
            overflow-x: auto; /* Para tablas que exceden el ancho en pantallas pequeñas */
            border-radius: 0.75rem; /* Rounded corners for the table */
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .data-table {
            min-width: 100%;
            border-collapse: collapse; /* Eliminar el espacio entre celdas */
        }

        .data-table thead {
            background-color: #f3f4f6; /* bg-gray-50 - un poco más oscuro que el fondo para el encabezado */
        }

        .data-table th {
            padding: 1rem 1.5rem; /* px-6 py-3 */
            text-align: left;
            font-size: 0.8rem; /* text-xs */
            font-weight: 600; /* font-medium */
            color: var(--text-light); /* text-gray-500 */
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border-color); /* Separador inferior en el encabezado */
        }

        .data-table tbody tr {
            background-color: var(--card-background); /* bg-white */
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #fdfdfd; /* Ligeramente diferente para filas pares */
        }

        .data-table td {
            padding: 1rem 1.5rem; /* px-6 py-4 */
            white-space: nowrap;
            font-size: 0.95rem;
            color: var(--text-dark); /* text-gray-900 */
            border-bottom: 1px solid var(--border-color); /* Divisor de fila */
        }

        .data-table tbody tr:last-child td {
            border-bottom: none; /* No border for the last row */
        }

        /* Enlace de Editar */
        .action-link.edit {
            color: var(--edit-color); /* text-indigo-600 */
            transition: color 0.2s ease;
            text-decoration: none;
            font-weight: 500;
        }

        .action-link.edit:hover {
            color: var(--edit-light); /* hover:text-indigo-900 */
            text-decoration: underline;
        }

        /* Botón/Formulario de Eliminar */
        .action-form.delete {
            display: inline; /* Para que esté en la misma línea que "Editar" */
        }

        .action-button.delete {
            background: none;
            border: none;
            padding: 0;
            font-size: 0.95rem;
            color: var(--delete-color); /* text-red-600 */
            cursor: pointer;
            transition: color 0.2s ease;
            font-weight: 500;
            text-decoration: none; /* Asegura que no se vea como un enlace subrayado por defecto */
        }

        .action-button.delete:hover {
            color: var(--delete-light); /* hover:text-red-900 */
            text-decoration: underline;
        }

        /* Celda de acciones (alinear a la derecha y con espacio entre botones) */
        .actions-cell {
            text-align: right; /* text-right */
            display: flex; /* Usar flexbox para alinear los botones */
            gap: 0.75rem; /* mr-2 para el enlace, ahora se usa gap */
            justify-content: flex-end; /* Alinear a la derecha */
        }

        /* Mensaje de tabla vacía */
        .empty-table-message {
            padding: 1rem 1.5rem; /* px-6 py-4 */
            text-align: center;
            color: var(--text-light); /* text-gray-500 */
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
            .card-container {
                padding: 1.5rem;
            }
            .create-button {
                padding: 0.6rem 1.2rem;
                font-size: 0.8rem;
            }
            .data-table th, .data-table td {
                padding: 0.8rem 1.2rem;
                font-size: 0.9rem;
            }
            .actions-cell {
                gap: 0.5rem;
            }
        }

        @media (max-width: 768px) { /* Móviles */
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
            .create-button-container {
                justify-content: center; /* Centrar el botón "Crear" */
            }
            .create-button {
                width: 100%; /* Botón de ancho completo */
                justify-content: center;
            }
            /* La tabla usará overflow-x-auto, pero las celdas se compactarán */
            .data-table th, .data-table td {
                padding: 0.6rem 0.8rem;
                font-size: 0.85rem;
            }
            .actions-cell {
                flex-direction: column; /* Apilar botones de acción */
                align-items: flex-end; /* Alinear a la derecha */
                gap: 0.3rem; /* Espacio más pequeño entre botones apilados */
            }
            .action-link.edit, .action-button.delete {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body class="font-sans antialiased">
    <header class="page-header-container">
        <div class="page-header-content">
            <h2 class="page-title">
                {{ __('Administración de Categorías') }}
            </h2>
        </div>
    </header>

    <div class="main-content-wrapper">
        <div class="content-container">
            <div class="card-container">
                <div class="create-button-container">
                    <a href="{{ route('admin.categories.create') }}" class="create-button">
                        {{ __('Crear Nueva Categoría') }}
                    </a>
                </div>

                @if (session('success'))
                    <div class="session-message success" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="session-message error" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Slug</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->slug }}</td>
                                    <td class="actions-cell">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="action-link edit">Editar</a>
                                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="action-form delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-button delete" onclick="return confirm('¿Estás seguro de eliminar esta categoría? Esto también eliminará todos los productos asociados.')">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-table-message">No hay categorías disponibles.</td>
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