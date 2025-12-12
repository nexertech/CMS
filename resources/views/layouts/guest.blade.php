<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Feather Icons -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.29.2/dist/feather.min.js"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            color: #212529;
        }
        
        .auth-container {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            overflow: hidden;
        }
        
        .auth-header {
            background: #ffffff;
            padding: 1.5rem 2rem 1rem;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }
        
        .auth-form {
            padding: 1.5rem 2rem;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .logo {
            display: inline-block;
            width: 50px;
            height: 50px;
            background: #212529;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        
        .auth-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 0.375rem;
        }
        
        .auth-header p {
            font-size: 0.8125rem;
            color: #6c757d;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: #212529;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 0.9375rem;
            transition: all 0.2s ease;
            background: #ffffff;
            color: #212529;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #212529;
            box-shadow: 0 0 0 3px rgba(33, 37, 41, 0.05);
        }
        
        .form-input::placeholder {
            color: #adb5bd;
        }
        
        .btn-primary {
            background: #212529;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            color: #ffffff;
            font-weight: 500;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary:hover {
            background: #000000;
        }
        
        .btn-primary:active {
            transform: translateY(1px);
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.8125rem;
            margin-top: 0.5rem;
        }
        
        .text-link {
            color: #212529;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .text-link:hover {
            text-decoration: underline;
        }
        
        .form-checkbox {
            width: 1rem;
            height: 1rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .form-checkbox:checked {
            background: #212529;
            border-color: #212529;
        }
        
        .status-message {
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        
        .status-message.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .top-nav {
            background: #ffffff;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 0;
        }
        
        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .nav-logo {
            font-size: 1.125rem;
            font-weight: 600;
            color: #212529;
        }
    </style>
</head>

<body>
    <!-- Top Navigation Bar -->
    <nav class="top-nav">
        <div class="nav-content">
            <div class="nav-logo">CMS - Complaint Management System</div>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-4 sm:pt-0" style="min-height: calc(100vh - 73px); padding: 1rem 0;">
        <div class="w-full sm:max-w-md">
            <div class="auth-container">
                <div class="auth-header">
                    <div class="logo-container">
                        <div class="logo">CMS</div>
                    </div>
                    @if(request()->routeIs('login'))
                        <h1>Welcome Back</h1>
                        <p>Sign in to your account</p>
                    @elseif(request()->routeIs('register'))
                        <h1>Create Account</h1>
                        <p>Sign up to get started</p>
                    @else
                        <h1>Welcome</h1>
                        <p>Access your account</p>
                    @endif
                </div>
                
                <div class="auth-form">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
    
    <script>
        feather.replace();
    </script>
</body>
</html>
