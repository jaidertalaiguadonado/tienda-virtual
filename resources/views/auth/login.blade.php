<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Iniciar Sesión</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Aunque no usemos los componentes Blade, es posible que 'app.js' contenga inicializaciones necesarias para Laravel --}}
    @vite(['resources/js/app.js'])

    {{-- ESTILOS ESPECÍFICOS PARA ESTA PÁGINA (todo nuestro CSS personalizado) --}}
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
            --delete-button-color: #DC3545; 
            --delete-button-hover: #b91c1c; 
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
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
        }

        
        .login-card-container {
            background-color: var(--card-background);
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 2.5rem; 
            width: 100%;
            max-width: 420px; 
            margin: 2rem; 
            box-sizing: border-box; 
            border: 1px solid var(--primary-light); 
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 2rem; 
            text-align: center;
        }

        
        .form-field {
            margin-bottom: 1.5rem; 
        }

        
        .input-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        
        .text-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            color: var(--text-dark);
            background-color: #fcfcfc;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            box-sizing: border-box; 
        }

        .text-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25); 
        }

        
        .input-error {
            font-size: 0.85rem;
            color: var(--error-text);
            margin-top: 0.5rem;
        }

        
        .remember-me-label {
            display: flex;
            align-items: center;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem; 
        }

        .remember-me-checkbox {
            border: 1px solid var(--border-color);
            border-radius: 0.25rem;
            width: 1.15rem;
            height: 1.15rem;
            accent-color: var(--primary-color); 
            cursor: pointer;
        }

        .remember-me-text {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-left: 0.5rem;
        }

        
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between; 
            margin-bottom: 1rem; 
            width: 100%; 
        }

        
        .forgot-password-link {
            font-size: 0.85rem;
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.2s ease;
            white-space: nowrap;
        }

        .forgot-password-link:hover {
            color: var(--primary-light);
            text-decoration: underline;
        }

        
        .login-button {
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
            
            margin: 0; 
            width: auto;
        }

        .login-button:hover {
            background-color: var(--primary-light);
            transform: translateY(-1px);
        }

        .login-button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.3);
        }

        .login-button:active {
            background-color: var(--primary-color);
            transform: translateY(0);
        }

        
        .register-link-container {
            text-align: center;
            margin-top: 1.5rem; 
        }

        .register-link {
            font-size: 0.9rem;
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .register-link:hover {
            color: #138D9E;
            text-decoration: underline;
        }

        
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
                flex-direction: column; 
                align-items: center;   
                justify-content: center; 
                gap: 1rem;             
                width: 100%;           
            }

            .forgot-password-link {
                margin-top: 0; 
                text-align: center; 
            }
            .login-button {
                width: auto; 
                padding: 0.7rem 1.4rem;
                font-size: 0.95rem;
                margin: 0; 
            }
            .register-link-container {
                margin-top: 1rem;
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
            .login-button {
                width: 100%; 
            }
        }
    </style>
</head>
<body>
    <div class="login-card-container">
        <h2 class="login-title">
            {{ __('Iniciar Sesión') }}
        </h2>

        @if (session('status'))
            <div class="session-status" role="alert">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-field">
                <label for="email" class="input-label">{{ __('Email') }}</label>
                <input id="email" class="text-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
                @error('email')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field">
                <label for="password" class="input-label">{{ __('Contraseña') }}</label>
                <input id="password" class="text-input" type="password" name="password" required autocomplete="current-password" />
                @error('password')
                    <div class="input-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="block">
                <label for="remember_me" class="remember-me-label">
                    <input id="remember_me" type="checkbox" class="remember-me-checkbox" name="remember">
                    <span class="remember-me-text">{{ __('Recordarme') }}</span>
                </label>
            </div>

            <div class="form-actions">
                @if (Route::has('password.request'))
                    <a class="forgot-password-link" href="{{ route('password.request') }}">
                        {{ __('¿Olvidaste tu contraseña?') }}
                    </a>
                @endif

                <button type="submit" class="login-button">
                    {{ __('Iniciar Sesión') }}
                </button>
            </div>
        </form>

        {{-- Enlace para crear cuenta --}}
        @if (Route::has('register'))
            <div class="register-link-container">
                <a class="register-link" href="{{ route('register') }}">
                    {{ __('¿No tienes cuenta? Regístrate aquí.') }}
                </a>
            </div>
        @endif
    </div>
</body>
</html>