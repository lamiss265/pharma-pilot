<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.login') }} - {{ __('messages.app_name') }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.6.6/css/flag-icons.min.css"/>
    
    <style>
        :root {
            /* Pink Theme Colors */
            --primary-pink: #FFD6E0;
            --secondary-pink: #FFB7C5;
            --cream-white: #FFF9FB;
            --lavender: #E6E6FA;
            --rose-gold: #E0BFB8;
            --delicate-gold: #F0D9B5;
            --text-dark: #4A4A4A;
            --text-muted: #6B7280;
            --bg-overlay: rgba(255, 214, 224, 0.1);
            --card-bg: rgba(255, 249, 251, 0.95);
            
            /* Shadow and Effects */
            --shadow-soft: 0 4px 20px rgba(255, 182, 193, 0.15);
            --shadow-hover: 0 8px 30px rgba(255, 182, 193, 0.25);
            --blur-effect: blur(10px);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            transition: var(--transition);
        }
        
        body {
            font-family: 'Poppins', 'Nunito', sans-serif;
            background: var(--cream-white);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255, 214, 224, 0.4) 0%, transparent 60%),
                radial-gradient(circle at 80% 20%, rgba(230, 230, 250, 0.4) 0%, transparent 60%),
                radial-gradient(circle at 40% 40%, rgba(255, 183, 197, 0.3) 0%, transparent 60%);
            z-index: -1;
            pointer-events: none;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 3rem;
            background: var(--card-bg);
            backdrop-filter: var(--blur-effect);
            border: 1px solid var(--bg-overlay);
            border-radius: 25px;
            box-shadow: var(--shadow-hover);
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 214, 224, 0.1), transparent);
            animation: shimmer 3s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
            position: relative;
            z-index: 1;
        }
        
        .login-header .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .login-header .brand i {
            font-size: 2.5rem;
            color: var(--rose-gold);
            margin-right: 0.5rem;
        }
        
        .login-header h1 {
            color: var(--text-dark);
            font-weight: 700;
            font-size: 2rem;
            margin: 0;
            background: linear-gradient(135deg, var(--secondary-pink), var(--rose-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .login-header p {
            color: var(--text-muted);
            font-size: 1.1rem;
            margin: 0.5rem 0 0 0;
            font-weight: 400;
        }
        
        .language-switcher {
            position: absolute;
            top: 25px;
            right: 25px;
            z-index: 10;
        }
        
        .language-switcher .dropdown-toggle {
            background: var(--card-bg);
            backdrop-filter: var(--blur-effect);
            border: 1px solid var(--bg-overlay);
            border-radius: 20px;
            padding: 0.5rem 1rem;
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            box-shadow: var(--shadow-soft);
        }
        
        .language-switcher .dropdown-toggle:hover {
            background: var(--bg-overlay);
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }
        
        .language-switcher .dropdown-menu {
            background: var(--card-bg);
            backdrop-filter: var(--blur-effect);
            border: 1px solid var(--bg-overlay);
            border-radius: 15px;
            box-shadow: var(--shadow-hover);
            padding: 0.5rem;
        }
        
        .language-switcher .dropdown-item {
            border-radius: 10px;
            margin: 0.25rem 0;
            padding: 0.5rem 1rem;
            color: var(--text-dark);
            transition: var(--transition);
        }
        
        .language-switcher .dropdown-item:hover {
            background: var(--bg-overlay);
            color: var(--text-dark);
            transform: translateX(5px);
        }
        
        .form-label {
            color: var(--text-dark);
            font-weight: 500;
            margin-bottom: 0.75rem;
        }
        
        .form-control {
            background: var(--card-bg);
            border: 2px solid var(--bg-overlay);
            border-radius: 15px;
            padding: 0.875rem 1.25rem;
            color: var(--text-dark);
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .form-control:focus {
            background: var(--card-bg);
            border-color: var(--rose-gold);
            box-shadow: 0 0 0 0.2rem rgba(224, 191, 184, 0.25);
            color: var(--text-dark);
        }
        
        .form-control::placeholder {
            color: var(--text-muted);
            opacity: 0.8;
        }
        
        .form-check-input {
            background-color: var(--card-bg);
            border: 2px solid var(--bg-overlay);
            border-radius: 8px;
        }
        
        .form-check-input:checked {
            background-color: var(--rose-gold);
            border-color: var(--rose-gold);
        }
        
        .form-check-input:focus {
            border-color: var(--rose-gold);
            box-shadow: 0 0 0 0.2rem rgba(224, 191, 184, 0.25);
        }
        
        .form-check-label {
            color: var(--text-dark);
            font-weight: 400;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-pink), var(--rose-gold));
            border: none;
            border-radius: 20px;
            padding: 0.875rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--text-dark);
            box-shadow: var(--shadow-soft);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--rose-gold), var(--delicate-gold));
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            color: var(--text-dark);
        }
        
        .btn-primary:focus {
            background: linear-gradient(135deg, var(--secondary-pink), var(--rose-gold));
            box-shadow: 0 0 0 0.2rem rgba(224, 191, 184, 0.25);
            color: var(--text-dark);
        }
        
        .alert {
            border: none;
            border-radius: 15px;
            backdrop-filter: var(--blur-effect);
            box-shadow: var(--shadow-soft);
            position: relative;
            z-index: 1;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, rgba(255, 179, 186, 0.9), var(--card-bg));
            color: var(--text-dark);
            border-left: 4px solid #FF6B7A;
        }
        
        .invalid-feedback {
            color: #FF6B7A;
            font-weight: 500;
        }
        
        .is-invalid {
            border-color: #FF6B7A !important;
        }
        
        .is-invalid:focus {
            border-color: #FF6B7A !important;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 122, 0.25) !important;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                margin: 1rem;
                padding: 2rem;
            }
            
            .language-switcher {
                top: 15px;
                right: 15px;
            }
            
            .login-header h1 {
                font-size: 1.75rem;
            }
        }
        
        /* Loading Animation */
        .btn-primary:disabled {
            opacity: 0.8;
            transform: none;
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 2px;
        }
    </style>
</head>
<body>
    <div class="language-switcher dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-globe"></i> {{ strtoupper(app()->getLocale()) }}
        </button>
        <ul class="dropdown-menu" aria-labelledby="languageDropdown">
            <li><a class="dropdown-item" href="?locale=en"><span class="fi fi-us"></span> English</a></li>
            <li><a class="dropdown-item" href="?locale=fr"><span class="fi fi-fr"></span> Français</a></li>
            <li><a class="dropdown-item" href="?locale=ar"><span class="fi fi-sa"></span> العربية</a></li>
        </ul>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-container">
                    <div class="login-header">
                        <h1>{{ __('messages.app_name') }}</h1>
                        <p>{{ __('messages.login') }}</p>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('messages.email') }}</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('messages.password') }}</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                {{ __('messages.remember_me') }}
                            </label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                {{ __('messages.login') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 