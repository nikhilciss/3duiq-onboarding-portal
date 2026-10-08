<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', '3duiq Onboarding Portal') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        html, body {
            background-color: #060913;
            color: #f1f5f9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            height: 100%;
            margin: 0;
        }
        #app {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .navbar-custom {
            background: rgba(10, 15, 30, 0.95) !important;
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(56, 189, 248, 0.15);
            padding: 12px 0;
            z-index: 100;
        }
        .brand-logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none !important;
        }
        .brand-badge {
            background: linear-gradient(135deg, #0284c7 0%, #6366f1 100%);
            color: #ffffff;
            font-weight: 800;
            font-size: 1.1rem;
            padding: 4px 12px;
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.4);
            letter-spacing: 0.5px;
        }
        .brand-text {
            font-weight: 700;
            font-size: 1.15rem;
            color: #ffffff;
        }
        .brand-text span {
            color: #38bdf8;
        }
        .nav-link-custom {
            color: #94a3b8 !important;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 6px 14px !important;
        }
        .nav-link-custom:hover {
            color: #38bdf8 !important;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark navbar-custom shadow-sm">
            <div class="container-fluid px-4">
                <a class="brand-logo-container" href="{{ url('/') }}">
                    <div class="brand-badge">3duiq</div>
                    <div class="brand-text">Onboarding <span>Portal</span></div>
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto align-items-center">
                        @guest
                            <li class="nav-item">
                                <a class="nav-link nav-link-custom" href="{{ route('login') }}">Admin Login</a>
                            </li>
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle text-light font-weight-bold" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    👤 {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-right bg-dark border-secondary shadow-lg" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item text-light" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        🚪 Logout
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main style="flex: 1; display: flex; flex-direction: column;">
            @yield('content')
        </main>
    </div>
</body>
</html>
