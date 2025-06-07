<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Registrarse</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/js/app.js'])

    {{-- ESTILOS ESPECÍFICOS PARA ESTA PÁGINA (reutilizando los del login) --}}
    <style>
        /* Variables CSS para colores - ¡Fácil de cambiar! */
        :root {
            --primary-color: #007BFF; /* Azul principal */
            --primary-light: #66B2FF; /* Azul claro para hover */
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
            display: flex; /* Para centrar el contenido */
            justify-content: center; /* Centrar horizontalmente */
            align-items: center; /* Centrar verticalmente */
            min-height: 100vh; /* Ocupar toda la altura de la ventana */
        }

        /* Contenedor principal de la tarjeta */
        .login-card-container { /* Renombrado para generalizar si se usa en registro también */
            background-color: var(--card-background);
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 2.5rem; /* Más padding interno */
            width: 100%;
            max-width: 420px; /* Ancho máximo para el formulario */
            margin: 2rem; /* Margen para pantallas más pequeñas */
            box-sizing: border-box; /* Incluir padding en el ancho */
            border: 1px solid var(--primary-light); /* Borde de 1px con un azul claro */
        }

        .login-title { /* Renombrado para generalizar */
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 2rem; /* Espacio debajo del título */
            text-align: center;
        }

        /* Estilo para los divs de campo */
        .form-field {
            margin-bottom: 1.5rem; /* Espacio entre campos */
        }

        /* Estilo para las etiquetas de los inputs */
        .input-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        /* Estilo para los inputs de texto */
        .text-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            color: var(--text-dark);
            background-color: #fcfcfc;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            box-sizing: border-box; /* Asegura que padding no aumente el ancho total */
        }

        .text-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25); /* Sombra de foco con primary-color */
        }

        /* Estilo para los mensajes de error */
        .input-error {
            font-size: 0.85rem;
            color: var(--error-text);
            margin-top: 0.5rem;
        }

        /* Contenedor de botones y enlaces */
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between; /* Mantenemos para desktop */
            margin-top: 2rem; /* Espacio antes de los botones/enlaces */
            width: 100%; /* Asegurar que ocupe todo el ancho disponible */
        }

        /* Estilo para el enlace "Already registered?" */
        .already-registered-link { /* Clase específica para este enlace */
            font-size: 0.85rem;
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.2s ease;
            white-space: nowrap;
        }

        .already-registered-link:hover {
            color: var(--primary-light);
            text-decoration: underline;
        }

        /* Estilo para el botón de Registro */
        .register-button { /* Clase específica para el botón de registro */
            display: inline-flex;
            align-items: center;
            padding: 0.6rem 1.2rem;
            background-color: var(--primary-color);
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: background-color 0.3s ease, transform 0.2s ease;
            cursor: pointer;
            box-shadow: 0 3px 8px rgba(0, 123, 255, 0.2);
            margin: 0; /* Aseguramos que no tenga márgenes o anchos fijos no deseados */
            width: auto;
        }

        .register-button:hover {
            background-color: var(--primary-light);
            transform: translateY(-1px);
        }

        .register-button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.3);
        }

        .register-button:active {
            background-color: var(--primary-color);
            transform: translateY(0);
        }

        /* Mensajes de sesión (si aplica a registro) */
        .session-status {
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            background-color: var(--success-bg);
            color: var(--success-text);
            border: 1px solid #c3e6cb;
            text-align: center;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .login-card-container {
                margin: 1.5rem;
                padding: 2rem;
            }
            .login-title {
                font-size: 1.8rem;
                margin-bottom: 1.5rem;
            }
            
            .form-actions {
                flex-direction: column; /* Apilar enlace y botón */
                align-items: center;   /* Centra horizontalmente los elementos Hijos */
                justify-content: center; /* Centra si hay espacio extra vertical */
                gap: 1rem;             /* Controla el espacio entre el enlace y el botón cuando están apilados */
                width: 100%;           /* Asegurar que el contenedor ocupe todo el ancho disponible */
            }

            .already-registered-link {
                margin-top: 0; /* Reseteamos márgenes para que 'gap' controle el espacio */
                text-align: center; /* Asegura que el texto dentro del enlace también se centre */
            }
            .register-button {
                width: auto; /* Dejar que el contenido defina el ancho */
                padding: 0.7rem 1.4rem;
                font-size: 0.95rem;
                margin: 0; /* Asegurar que no tenga márgenes laterales */
            }
        }

        @media (max-width: 480px) {
            .login-card-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            .login-title {
                font-size: 1.5rem;
            }
            .register-button {
                width: 100%; /* Botón de ancho completo en móviles muy pequeños */
            }
        }
    </style>
</head>
<body>
    <div class="login-card-container">
        <h2 class="login-title">
            {{ __('Registrarse') }}
        </h2>

        @if (session('status'))
            <div class="session-status" role="alert">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-field">
                <label for="name" class="input-label">{{ __('Nombre') }}</label>
                <input id="name" class="text-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" />
                @error('name')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="email" class="input-label">{{ __('Email') }}</label>
                <input id="email" class="text-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
                @error('email')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="password" class="input-label">{{ __('Contraseña') }}</label>
                <input id="password" class="text-input" type="password" name="password" required autocomplete="new-password" />
                @error('password')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="password_confirmation" class="input-label">{{ __('Confirmar Contraseña') }}</label>
                <input id="password_confirmation" class="text-input" type="password" name="password_confirmation" required autocomplete="new-password" />
                @error('password_confirmation')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <a class="already-registered-link" href="{{ route('login') }}">
                    {{ __('¿Ya estás registrado?') }}
                </a>

                <button type="submit" class="register-button">
                    {{ __('Registrarse') }}
                </button>
            </div>
        </form>
    </div>
</body>
</html>