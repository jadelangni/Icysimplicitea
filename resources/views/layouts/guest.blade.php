<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#166534">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Simplicitea POS">
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">

        <title>{{ config('app.name', 'Simplicitea') }} - Login</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700|playfair-display:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            :root {
                --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                --font-serif: 'Playfair Display', Georgia, serif;
            }

            * { font-family: var(--font-sans); }

            /* ===== LEFT PANE - Brand Visual ===== */
            .brand-pane {
                background: linear-gradient(160deg, #e8f5e9 0%, #c8e6c9 30%, #b2dfdb 60%, #e0f2f1 100%);
                position: relative;
                overflow: hidden;
            }

            .dark .brand-pane {
                background: linear-gradient(160deg, #0f1f1a 0%, #142b24 30%, #122a2a 60%, #0f1e1e 100%);
            }

            /* Animated gradient orbs on brand pane */
            .brand-pane::before,
            .brand-pane::after {
                content: '';
                position: absolute;
                border-radius: 50%;
                filter: blur(80px);
                opacity: 0.5;
                animation: orbFloat 12s ease-in-out infinite;
            }

            .brand-pane::before {
                width: 320px;
                height: 320px;
                background: radial-gradient(circle, rgba(102, 187, 106, 0.4), transparent 70%);
                top: 10%;
                left: -10%;
            }

            .brand-pane::after {
                width: 280px;
                height: 280px;
                background: radial-gradient(circle, rgba(128, 203, 196, 0.35), transparent 70%);
                bottom: 10%;
                right: -5%;
                animation-delay: -6s;
            }

            .dark .brand-pane::before {
                background: radial-gradient(circle, rgba(102, 187, 106, 0.15), transparent 70%);
            }

            .dark .brand-pane::after {
                background: radial-gradient(circle, rgba(128, 203, 196, 0.12), transparent 70%);
            }

            @keyframes orbFloat {
                0%, 100% { transform: translate(0, 0) scale(1); }
                33% { transform: translate(30px, -20px) scale(1.05); }
                66% { transform: translate(-20px, 15px) scale(0.95); }
            }

            /* Floating tea leaves decorating brand pane */
            .tea-leaf {
                position: absolute;
                pointer-events: none;
                z-index: 1;
            }

            .tea-leaf svg {
                fill: rgba(76, 175, 80, 0.07);
                animation: leafDrift 18s ease-in-out infinite;
            }

            .dark .tea-leaf svg {
                fill: rgba(102, 187, 106, 0.05);
            }

            .tea-leaf:nth-child(1) { top: 8%; left: 8%; }
            .tea-leaf:nth-child(2) { top: 60%; right: 10%; animation-delay: -4s; }
            .tea-leaf:nth-child(3) { bottom: 12%; left: 15%; animation-delay: -8s; }

            .tea-leaf:nth-child(1) svg { width: 100px; height: 100px; }
            .tea-leaf:nth-child(2) svg { width: 70px; height: 70px; }
            .tea-leaf:nth-child(3) svg { width: 85px; height: 85px; }

            @keyframes leafDrift {
                0%, 100% { transform: translateY(0) rotate(0deg); }
                25% { transform: translateY(-12px) rotate(4deg); }
                50% { transform: translateY(-4px) rotate(-2deg); }
                75% { transform: translateY(-16px) rotate(6deg); }
            }

            /* 3D logo float effect */
            .logo-3d {
                width: 182px;
                height: 182px;
                border-radius: 32px;
                background: linear-gradient(145deg, #000000, #f0f8f0);
                padding: 6px;
                box-shadow:
                    0 20px 60px rgba(76, 175, 80, 0.15),
                    0 8px 24px rgba(0, 0, 0, 0.06),
                    inset 0 -3px 6px rgba(0, 0, 0, 0.02);
                animation: logoFloat 6s ease-in-out infinite;
                position: relative;
                z-index: 2;
            }

            .dark .logo-3d {
                background: linear-gradient(145deg, #1e293b, #0f172a);
                box-shadow:
                    0 20px 60px rgba(102, 187, 106, 0.12),
                    0 8px 24px rgba(0, 0, 0, 0.3);
            }

            .logo-3d-inner {
                width: 100%;
                height: 100%;
                background: white;
                border-radius: 28px;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }

            .dark .logo-3d-inner {
                background: #1e293b;
            }

            @keyframes logoFloat {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }

            /* ===== RIGHT PANE - Login Form ===== */
            .form-pane {
                background: #000000;
            }

            .dark .form-pane {
                background: #0f172a;
            }

            /* Login card - removed card bg on right pane, form lives directly */
            .login-card {
                background: transparent;
            }

            /* Input fields */
            .mint-input {
                background: #f1f5f3;
                border: 1.5px solid #e2e8e4;
                border-radius: 12px;
                transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
                height: 48px;
                font-family: var(--font-sans);
                font-size: 14px;
            }

            .mint-input:hover {
                border-color: #c8e6c9;
                background: #edf2ee;
            }

            .mint-input:focus {
                background: #000000;
                border-color: #66bb6a;
                box-shadow: 0 0 0 3px rgba(102, 187, 106, 0.12);
                outline: none;
            }

            .dark .mint-input {
                background: rgba(30, 41, 59, 0.8);
                border: 1.5px solid rgba(102, 187, 106, 0.15);
                color: black;
            }

            .dark .mint-input:hover {
                border-color: rgba(102, 187, 106, 0.3);
                background: rgba(30, 41, 59, 0.9);
            }

            .dark .mint-input:focus {
                background: rgba(30, 41, 59, 1);
                border-color: #66bb6a;
                box-shadow: 0 0 0 3px rgba(102, 187, 106, 0.15);
            }

            .dark .mint-input::placeholder {
                color: rgba(255, 255, 255, 0.35);
            }

            /* Primary button */
            .btn-mint {
                background: linear-gradient(135deg, #4caf50 0%, #43a047 100%);
                border-radius: 12px;
                color: black;
                font-weight: 600;
                font-size: 15px;
                transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 14px rgba(76, 175, 80, 0.3);
            }

            .btn-mint:hover {
                background: linear-gradient(135deg, #43a047 0%, #388e3c 100%);
                transform: translateY(-1px);
                box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
            }

            .btn-mint:active {
                transform: translateY(0);
            }

            /* Secondary/Quick access buttons */
            .btn-secondary {
                background: #f5f9f6;
                border: 1.5px solid #e8f0ea;
                border-radius: 12px;
                transition: all 0.2s ease;
            }

            .btn-secondary:hover {
                background: #e8f5e9;
                border-color: #a5d6a7;
                transform: translateY(-1px);
            }

            .dark .btn-secondary {
                background: rgba(102, 187, 106, 0.06);
                border: 1.5px solid rgba(102, 187, 106, 0.15);
            }

            .dark .btn-secondary:hover {
                background: rgba(102, 187, 106, 0.15);
                border-color: rgba(102, 187, 106, 0.3);
            }

            /* Theme toggle pill */
            .theme-toggle {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                background: rgba(255, 255, 255, 0.7);
                border: 1.5px solid rgba(76, 175, 80, 0.15);
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.25s ease;
                backdrop-filter: blur(8px);
            }

            .theme-toggle:hover {
                background: rgba(255, 255, 255, 0.9);
                transform: scale(1.05);
                border-color: rgba(76, 175, 80, 0.3);
            }

            .dark .theme-toggle {
                background: rgba(30, 41, 59, 0.7);
                border-color: rgba(102, 187, 106, 0.2);
            }

            .dark .theme-toggle:hover {
                background: rgba(102, 187, 106, 0.15);
            }

            .theme-toggle .icon-sun { display: block; }
            .theme-toggle .icon-moon { display: none; }
            .dark .theme-toggle .icon-sun { display: none; }
            .dark .theme-toggle .icon-moon { display: block; }

            /* Center logo between panes */
            .center-logo {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                z-index: 50;
                width: 500px;
                height: 500px;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.95);
                box-shadow:
                    0 8px 32px rgba(0, 0, 0, 0.1),
                    0 4px 16px rgba(76, 175, 80, 0.15);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 40px;
                backdrop-filter: blur(10px);
                border: 3px solid rgba(255, 255, 255, 0.8);
                opacity: 0.1;
            }

            .dark .center-logo {
                background: rgba(30, 41, 59, 0.95);
                box-shadow:
                    0 8px 32px rgba(0, 0, 0, 0.3),
                    0 4px 16px rgba(102, 187, 106, 0.2);
                border-color: rgba(102, 187, 106, 0.3);
            }

            .center-logo img {
                width: 100%;
                height: 100%;
                object-fit: contain;
            }

            /* Page wrapper background */
            .page-wrapper {
                min-height: 100vh;
                background: linear-gradient(135deg, #f0f4f0 0%, #e8f5e9 50%, #e0f2f1 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 40px;
            }

            .dark .page-wrapper {
                background: linear-gradient(135deg, #0a1410 0%, #0f1f1a 50%, #0a1818 100%);
            }

            /* Outer container box with soft shadows */
            .outer-box {
                width: 100%;
                max-width: 1200px;
                min-height: 700px;
                background: rgba(255, 255, 255, 0.6);
                border-radius: 32px;
                box-shadow:
                    0 0 60px rgba(0, 0, 0, 0.08),
                    0 0 100px rgba(76, 175, 80, 0.06),
                    0 25px 50px -12px rgba(0, 0, 0, 0.1),
                    inset 0 0 0 1px rgba(255, 255, 255, 0.5);
                backdrop-filter: blur(20px);
                overflow: hidden;
                position: relative;
            }

            .dark .outer-box {
                background: rgba(15, 23, 42, 0.7);
                box-shadow:
                    0 0 60px rgba(0, 0, 0, 0.3),
                    0 0 100px rgba(102, 187, 106, 0.08),
                    0 25px 50px -12px rgba(0, 0, 0, 0.4),
                    inset 0 0 0 1px rgba(102, 187, 106, 0.1);
            }

            /* Responsive: stack on mobile */
            @media (max-width: 767px) {
                .page-wrapper {
                    padding: 8px;
                    align-items: flex-start;
                    min-height: 100dvh;
                }
                .outer-box {
                    border-radius: 20px;
                    min-height: auto;
                    overflow: visible;
                    max-width: 100%;
                }
                .dual-pane {
                    flex-direction: column !important;
                    min-height: auto !important;
                }
                .dual-pane[style] { min-height: auto !important; }
                .brand-pane {
                    min-height: 150px !important;
                    width: 100% !important;
                    border-radius: 20px 20px 0 0 !important;
                    justify-content: center !important;
                }
                .form-pane {
                    width: 100% !important;
                    border-radius: 0 0 20px 20px !important;
                    padding: 20px 14px 24px !important;
                }
                .logo-3d {
                    width: 92px;
                    height: 92px;
                    border-radius: 20px;
                    margin-bottom: 10px;
                    padding: 4px;
                }
                .logo-3d-inner { border-radius: 18px; }
                .brand-pane .flex-1 {
                    padding-left: 16px;
                    padding-right: 16px;
                }
                .brand-pane h1 {
                    font-size: 1.55rem;
                    line-height: 1.1;
                    text-align: center;
                }
                .brand-pane p {
                    font-size: 0.75rem;
                    text-align: center;
                }
                .brand-pane .pb-8 {
                    position: absolute;
                    top: 12px;
                    right: 12px;
                    padding-bottom: 0;
                }
                .form-pane > div[style] {
                    max-width: 100% !important;
                }
                .form-pane h2 {
                    font-size: 2rem;
                    line-height: 1.1;
                }
                .center-logo { display: none; }
            }

            @media (max-width: 420px) {
                .page-wrapper { padding: 6px; }
                .outer-box { border-radius: 16px; }
                .brand-pane {
                    min-height: 136px !important;
                    border-radius: 16px 16px 0 0 !important;
                }
                .form-pane {
                    border-radius: 0 0 16px 16px !important;
                    padding: 18px 12px 22px !important;
                }
                .logo-3d {
                    width: 80px;
                    height: 80px;
                    margin-bottom: 8px;
                }
                .brand-pane h1 { font-size: 1.35rem; }
                .form-pane h2 {
                    font-size: 1.75rem;
                    margin-bottom: 0.25rem;
                }
            }
        </style>
        
    </head>
    <body class="antialiased overflow-x-hidden">
        <div class="page-wrapper">
            <div class="outer-box">
                <div class="dual-pane flex flex-row h-full relative" style="min-height: 700px;">
                    {{-- ===== CENTER LOGO (between panes) ===== --}}
                    <div class="center-logo">
                        <img src="{{ asset('images/logo.png') }}" alt="Icy's Simplicitea">
                    </div>

                    {{-- ===== LEFT: Brand / 3D Visual ===== --}}
                    <div class="brand-pane w-1/2 flex flex-col justify-between items-center relative" style="border-radius: 32px 0 0 32px;">
                <!-- Tea Leaves -->
                <div class="tea-leaf"><svg viewBox="0 0 24 24"><path d="M17,8C8,10 5.9,16.17 3.82,21.34L5.71,22L6.66,19.7C7.14,19.87 7.64,20 8,20C19,20 22,3 22,3C21,5 14,5.25 9,6.25C4,7.25 2,11.5 2,13.5C2,15.5 3.75,17.25 3.75,17.25C7,8 17,8 17,8Z"/></svg></div>
                <div class="tea-leaf"><svg viewBox="0 0 24 24"><path d="M17,8C8,10 5.9,16.17 3.82,21.34L5.71,22L6.66,19.7C7.14,19.87 7.64,20 8,20C19,20 22,3 22,3C21,5 14,5.25 9,6.25C4,7.25 2,11.5 2,13.5C2,15.5 3.75,17.25 3.75,17.25C7,8 17,8 17,8Z"/></svg></div>
                <div class="tea-leaf"><svg viewBox="0 0 24 24"><path d="M17,8C8,10 5.9,16.17 3.82,21.34L5.71,22L6.66,19.7C7.14,19.87 7.64,20 8,20C19,20 22,3 22,3C21,5 14,5.25 9,6.25C4,7.25 2,11.5 2,13.5C2,15.5 3.75,17.25 3.75,17.25C7,8 17,8 17,8Z"/></svg></div>

                <!-- Brand Content (centered) -->
                <div class="flex-1 flex flex-col items-center justify-center relative z-10 px-8">
                    <!-- 3D Floating Logo -->
                    <div class="logo-3d mb-8">
                        <div class="logo-3d-inner">
                            <img src="{{ asset('images/logo.png') }}" alt="Icy's Simplicitea" class="w-full h-full object-contain p-3">
                        </div>
                    </div>

                    <!-- Brand Text -->
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-black tracking-tight mb-2" style="font-family: var(--font-serif);">
                        Icy's Simplicitea
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-wide uppercase">
                        Point of Sale System
                    </p>
                </div>

            </div>

            {{-- ===== RIGHT: Login / User Interaction ===== --}}
            <div class="form-pane w-1/2 flex flex-col justify-center items-center px-6 py-12" style="border-radius: 0 32px 32px 0;">
                <div class="w-full" style="max-width: 380px;">
                    <!-- Get Started heading -->
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-black mb-1" style="font-family: var(--font-serif);">
                        Get Started
                    </h2>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mb-8">
                        Sign in to your account to continue
                    </p>

                    <!-- Form Slot -->
                    {{ $slot }}

                    <!-- Footer -->
                    <p class="text-center text-gray-300 dark:text-gray-600 text-xs mt-10 font-medium">
                        &copy; {{ date('Y') }} Icy's Simplicitea. All rights reserved.
                    </p>
                </div>
            </div>
            </div>
            </div>
        </div>
        
    </body>
</html>
