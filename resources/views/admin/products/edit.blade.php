<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Editar Producto</title>

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

        /* Contenedor de un campo de formulario */
        .form-field {
            margin-top: 1.5rem; /* mt-4 */
        }
        .form-field:first-child {
            margin-top: 0; /* Primer campo no tiene margen superior */
        }


        /* Labels de formulario */
        .form-label {
            display: block;
            font-size: 0.95rem; /* text-sm */
            font-weight: 500; /* font-medium */
            color: var(--text-dark);
            margin-bottom: 0.5rem; /* Pequeño espacio debajo del label */
        }

        /* Inputs de texto, número y select */
        .form-input,
        .form-select,
        .form-textarea {
            display: block;
            width: 100%;
            border: 1px solid var(--border-color); /* border-gray-300 */
            border-radius: 0.5rem; /* rounded-md */
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* shadow-sm */
            padding: 0.75rem 1rem; /* px-3 py-2 */
            font-size: 1rem;
            line-height: 1.5;
            color: var(--text-dark);
            background-color: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            border-color: var(--primary-color); /* focus:border-indigo-500 */
            box-shadow: 0 0 0 3px rgba(106, 103, 241, 0.25); /* focus:ring-indigo-500 */
            outline: none;
        }

        /* Textarea */
        .form-textarea {
            resize: vertical; /* Permitir redimensionar verticalmente */
        }

        /* Error de validación */
        .validation-error {
            font-size: 0.85rem; /* text-sm */
            color: var(--error-text); /* text-red-600 */
            margin-top: 0.5rem; /* mt-2 */
        }

        /* Contenedor de la imagen actual */
        .current-image-container {
            margin-top: 1.5rem;
        }

        .current-image {
            height: 6rem; /* h-24 */
            width: 6rem; /* w-24 */
            object-fit: cover;
            border-radius: 0.75rem; /* rounded-lg */
            margin-top: 0.75rem; /* mt-2 */
            border: 2px solid var(--primary-light);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Input de tipo file */
        .file-input {
            display: block;
            width: 100%;
            margin-top: 0.75rem; /* mt-1 */
            font-size: 0.95rem; /* text-sm */
            color: var(--text-dark); /* text-gray-900 */
            border: 1px solid var(--border-color); /* border border-gray-300 */
            border-radius: 0.5rem; /* rounded-lg */
            cursor: pointer;
            background-color: #f9fafb; /* bg-gray-50 */
            padding: 0.75rem 1rem; /* Ajuste manual para que se vea bien */
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .file-input:hover {
            background-color: #f3f4f6;
        }
        .file-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(106, 103, 241, 0.25);
        }
        .file-input::-webkit-file-upload-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .file-input::-webkit-file-upload-button:hover {
            background-color: var(--primary-light);
        }

        .file-input-help-text {
            margin-top: 0.25rem; /* mt-1 */
            font-size: 0.85rem; /* text-sm */
            color: var(--text-light); /* text-gray-500 */
        }

        /* Checkbox de Activo */
        .checkbox-container {
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
        }

        .form-checkbox {
            border-radius: 0.25rem; /* rounded */
            border: 1px solid var(--border-color); /* border-gray-300 */
            color: var(--primary-color); /* text-indigo-600 */
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* shadow-sm */
            transition: box-shadow 0.2s ease;
            width: 1.25rem; /* Ajuste para un checkbox moderno */
            height: 1.25rem;
            flex-shrink: 0; /* Evita que se encoja */
            cursor: pointer;
        }
        .form-checkbox:focus {
            box-shadow: 0 0 0 3px rgba(106, 103, 241, 0.25); /* focus:ring-indigo-500 */
            outline: none;
        }

        .checkbox-label {
            margin-left: 0.5rem; /* ml-2 */
            font-size: 0.95rem;
            color: var(--text-dark);
            cursor: pointer;
        }

        /* Contenedor de botones de acción al final del formulario */
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end; /* justify-end */
            margin-top: 2rem; /* mt-4 */
        }

        /* Botón Cancelar */
        .cancel-button {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem; /* px-4 py-2 */
            background-color: #6c757d; /* bg-gray-600 */
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.85rem; /* text-xs */
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-decoration: none;
            margin-right: 1rem; /* mr-4 */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
        }

        .cancel-button:hover {
            background-color: #5a6268; /* hover:bg-gray-500 */
            transform: translateY(-2px);
        }

        .cancel-button:focus {
            outline: none;
            background-color: #5a6268; /* focus:bg-gray-500 */
            box-shadow: 0 0 0 4px rgba(108, 117, 125, 0.3); /* focus:ring-indigo-500 etc. */
        }

        .cancel-button:active {
            background-color: #495057; /* active:bg-gray-700 */
            transform: translateY(0);
        }

        /* Botón Primario (Actualizar Producto) */
        .primary-button {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary-color); /* bg-gray-800 */
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.85rem;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(106, 103, 241, 0.2);
            white-space: nowrap;
        }

        .primary-button:hover {
            background-color: var(--primary-light); /* hover:bg-gray-700 */
            transform: translateY(-2px);
        }

        .primary-button:focus {
            outline: none;
            background-color: var(--primary-light); /* focus:bg-gray-700 */
            box-shadow: 0 0 0 4px rgba(106, 103, 241, 0.4);
        }

        .primary-button:active {
            background-color: var(--primary-color); /* active:bg-gray-900 */
            transform: translateY(0);
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
            .form-input, .form-select, .form-textarea {
                padding: 0.6rem 0.8rem;
            }
            .primary-button, .cancel-button {
                padding: 0.6rem 1.2rem;
                font-size: 0.8rem;
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
            .form-actions {
                flex-direction: column; /* Apilar botones */
                align-items: stretch; /* Estirar para ocupar el ancho completo */
                gap: 1rem; /* Espacio entre botones apilados */
            }
            .cancel-button {
                margin-right: 0; /* Eliminar margen derecho cuando está apilado */
            }
            .primary-button, .cancel-button {
                width: 100%; /* Botones de ancho completo */
                justify-content: center; /* Centrar texto */
                padding: 0.8rem 1rem;
            }
        }

        @media (max-width: 480px) { /* Móviles pequeños */
            .form-label {
                font-size: 0.9rem;
            }
            .form-input, .form-select, .form-textarea, .file-input {
                font-size: 0.9rem;
            }
            .validation-error, .file-input-help-text {
                font-size: 0.8rem;
            }
            .current-image {
                height: 5rem;
                width: 5rem;
            }
        }
    </style>
</head>
<body class="font-sans antialiased">
    <header class="page-header-container">
        <div class="page-header-content">
            <h2 class="page-title">
                {{ __('Editar Producto: ') . $product->name }}
            </h2>
        </div>
    </header>

    <div class="main-content-wrapper">
        <div class="content-container">
            <div class="card-container">
                <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-field">
                        <label for="category_id" class="form-label">{{ __('Categoría') }}</label>
                        <select id="category_id" name="category_id" class="form-select" required>
                            <option value="">Selecciona una categoría</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="validation-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="name" class="form-label">{{ __('Nombre del Producto') }}</label>
                        <input id="name" class="form-input" type="text" name="name" value="{{ old('name', $product->name) }}" required />
                        @error('name')
                            <div class="validation-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="description" class="form-label">{{ __('Descripción (Opcional)') }}</label>
                        <textarea id="description" name="description" rows="4" class="form-textarea">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="validation-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="price" class="form-label">{{ __('Precio') }}</label>
                        <input id="price" class="form-input" type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required />
                        @error('price')
                            <div class="validation-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="stock" class="form-label">{{ __('Stock') }}</label>
                        <input id="stock" class="form-input" type="number" name="stock" value="{{ old('stock', $product->stock) }}" required />
                        @error('stock')
                            <div class="validation-error">{{ $message }}</div>
                        @enderror
                    </div>

                    @if ($product->image)
                        <div class="current-image-container form-field">
                            <label class="form-label">{{ __('Imagen Actual') }}</label>
                            <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="current-image">
                        </div>
                    @endif

                    <div class="form-field">
                        <label for="image" class="form-label">{{ __('Nueva Imagen del Producto (Opcional)') }}</label>
                        <input id="image" class="file-input" type="file" name="image" accept="image/*">
                        <p class="file-input-help-text">Deja este campo vacío para mantener la imagen actual.</p>
                        @error('image')
                            <div class="validation-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="checkbox-container form-field">
                        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-checkbox" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <label for="is_active" class="checkbox-label">{{ __('Activo') }}</label>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('admin.products.index') }}" class="cancel-button">
                            {{ __('Cancelar') }}
                        </a>
                        <button type="submit" class="primary-button">
                            {{ __('Actualizar Producto') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>