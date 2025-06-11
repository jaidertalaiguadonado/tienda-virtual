<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Editar Categoría</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/js/app.js'])

    
    <style>
        
        :root {
            --primary-color: #6a67f1; 
            --primary-light: #8b89f8;
            --secondary-color: #fca311; 
            --text-dark: #333;
            --text-light: #555;
            --background-light: #f8f9fa;
            --card-background: #ffffff;
            --border-color: #e0e0e0;
            --success-bg: #d4edda;
            --success-text: #155724;
            --error-bg: #f8d7da;
            --error-text: #721c24;
            --danger-color: #dc3545; 
            --danger-light: #e65f6c;
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

        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
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
        }

        
        .form-field {
            margin-top: 1.5rem; 
        }
        .form-field:first-child {
            margin-top: 0; 
        }

        
        .form-label {
            display: block;
            font-size: 0.95rem; 
            font-weight: 500; 
            color: var(--text-dark);
            margin-bottom: 0.5rem; 
        }

        
        .form-input,
        .form-textarea {
            display: block;
            width: 100%;
            border: 1px solid var(--border-color); 
            border-radius: 0.5rem; 
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); 
            padding: 0.75rem 1rem; 
            font-size: 1rem;
            line-height: 1.5;
            color: var(--text-dark);
            background-color: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-input:focus,
        .form-textarea:focus {
            border-color: var(--primary-color); 
            box-shadow: 0 0 0 3px rgba(106, 103, 241, 0.25); 
            outline: none;
        }

        
        .form-textarea {
            resize: vertical; 
        }

        
        .validation-error {
            font-size: 0.85rem; 
            color: var(--error-text); 
            margin-top: 0.5rem; 
        }

        
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end; 
            margin-top: 2rem; 
        }

        
        .cancel-button {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem; 
            background-color: #6c757d; 
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.85rem; 
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-decoration: none;
            margin-right: 1rem; 
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
        }

        .cancel-button:hover {
            background-color: #5a6268; 
            transform: translateY(-2px);
        }

        .cancel-button:focus {
            outline: none;
            background-color: #5a6268; 
            box-shadow: 0 0 0 4px rgba(108, 117, 125, 0.3); 
        }

        .cancel-button:active {
            background-color: #495057; 
            transform: translateY(0);
        }

        
        .primary-button {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary-color); 
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
            background-color: var(--primary-light); 
            transform: translateY(-2px);
        }

        .primary-button:focus {
            outline: none;
            background-color: var(--primary-light); 
            box-shadow: 0 0 0 4px rgba(106, 103, 241, 0.4);
        }

        .primary-button:active {
            background-color: var(--primary-color); 
            transform: translateY(0);
        }

        
        @media (max-width: 992px) { 
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
            .form-input, .form-textarea {
                padding: 0.6rem 0.8rem;
            }
            .primary-button, .cancel-button {
                padding: 0.6rem 1.2rem;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 768px) { 
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
                flex-direction: column; 
                align-items: stretch; 
                gap: 1rem; 
            }
            .cancel-button {
                margin-right: 0; 
            }
            .primary-button, .cancel-button {
                width: 100%; 
                justify-content: center; 
                padding: 0.8rem 1rem;
            }
        }

        @media (max-width: 480px) { 
            .form-label {
                font-size: 0.9rem;
            }
            .form-input, .form-textarea {
                font-size: 0.9rem;
            }
            .validation-error {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body class="font-sans antialiased">
    <header class="page-header-container">
        <div class="page-header-content">
            <h2 class="page-title">
                {{ __('Editar Categoría: ') . $category->name }}
            </h2>
        </div>
    </header>

    <div class="main-content-wrapper">
        <div class="content-container">
            <div class="card-container">
                <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-field">
                        <label for="name" class="form-label">{{ __('Nombre de la Categoría') }}</label>
                        <input id="name" class="form-input" type="text" name="name" value="{{ old('name', $category->name) }}" required autofocus />
                        @error('name')
                            <div class="validation-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="description" class="form-label">{{ __('Descripción (Opcional)') }}</label>
                        <textarea id="description" name="description" rows="4" class="form-textarea">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <div class="validation-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('admin.categories.index') }}" class="cancel-button">
                            {{ __('Cancelar') }}
                        </a>
                        <button type="submit" class="primary-button">
                            {{ __('Actualizar Categoría') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>