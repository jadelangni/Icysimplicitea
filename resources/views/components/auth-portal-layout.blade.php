@props([
    'pageTitle' => 'Login',
    'appleTitle' => 'Login',
    'heroTitle' => 'Portal Access',
    'heroSubtitle' => '',
    'heroNoteTitle' => '',
    'heroNoteText' => '',
    'chipLabel' => 'Portal',
    'portalHeading' => 'Welcome back',
    'portalCopy' => '',
    'backHref' => null,
    'backAria' => 'Back',
    'dividerLabel' => '',
    'supportTitle' => '',
    'supportText' => '',
    'switchText' => '',
    'switchHref' => '#',
    'switchLinkLabel' => '',
    'themeAlign' => 'right',
])
@php
    $isThemeLeft = $themeAlign === 'left';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#005b5c">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $appleTitle }}">

    <title>{{ config('app.name', 'Simplicitea') }} - {{ $pageTitle }}</title>

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700;playfair-display:600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --auth-surface: rgba(255, 255, 255, 0.94);
            --auth-surface-soft: rgba(0, 91, 92, 0.07);
            --auth-accent: #00b140;
            --auth-accent-strong: #005b5c;
            --auth-text: #1f2937;
            --auth-muted: #6b7280;
            --auth-input: #edf5f1;
            --auth-input-border: #bad0c7;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100dvh;
            overflow-x: hidden;
            font-family: 'Inter', sans-serif;
            color: var(--auth-text);
            background:
                radial-gradient(circle at top center, rgba(0, 91, 92, 0.2), transparent 24%),
                radial-gradient(circle at 50% 18%, rgba(178, 232, 216, 0.16), transparent 34%),
                linear-gradient(180deg, #e5f3ec 0%, #d2e7dd 48%, #c0dbcf 100%);
        }

        .auth-shell {
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-panel {
            width: 100%;
            max-width: 1180px;
            min-height: min(92dvh, 880px);
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            border-radius: 34px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.45);
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.16);
            backdrop-filter: blur(18px);
        }

        .auth-hero {
            position: relative;
            padding: 28px;
            background:
                radial-gradient(circle at top left, rgba(0, 91, 92, 0.2), transparent 30%),
                radial-gradient(circle at center, rgba(178, 232, 216, 0.2), transparent 30%),
                linear-gradient(160deg, #d9ede4 0%, #bcdacd 34%, #9fc8b9 68%, #88b7a7 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .auth-hero::before {
            content: '';
            position: absolute;
            inset: 28px;
            border-radius: 28px;
            border: 1px solid rgba(0, 91, 92, 0.16);
            pointer-events: none;
        }

        .auth-topbar {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: {{ $backHref ? 'space-between' : ($isThemeLeft ? 'flex-start' : 'flex-end') }};
        }

        .topbar-action {
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            text-decoration: none;
            color: #1f2937;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(0, 91, 92, 0.16);
            backdrop-filter: blur(8px);
            transition: transform 0.2s ease, background-color 0.2s ease;
        }

        .topbar-action:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.88);
        }

        .auth-center {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 20px;
            padding: 32px 20px;
        }

        .brand-orb {
            width: 128px;
            height: 128px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at center, rgba(0, 91, 92, 0.3), rgba(0, 91, 92, 0.08) 46%, transparent 70%),
                rgba(0, 91, 92, 0.04);
            box-shadow:
                0 0 0 12px rgba(0, 91, 92, 0.05),
                0 0 0 26px rgba(0, 91, 92, 0.025),
                0 20px 50px rgba(15, 23, 42, 0.12);
        }

        .brand-orb img {
            width: 82px;
            height: 82px;
            object-fit: contain;
        }

        .auth-title {
            margin: 0;
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.1rem, 4vw, 3.35rem);
            line-height: 1;
            letter-spacing: -0.03em;
        }

        .auth-subtitle {
            margin: 0;
            max-width: 28rem;
            font-size: 1rem;
            line-height: 1.7;
            color: var(--auth-muted);
        }

        .hero-note {
            position: relative;
            z-index: 1;
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 16px 18px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.52);
            border: 1px solid rgba(76, 175, 80, 0.12);
        }

        .hero-note strong { display: block; font-size: 0.95rem; }
        .hero-note span { display: block; font-size: 0.82rem; color: var(--auth-muted); }

        .auth-form-wrap {
            background: var(--auth-surface);
            padding: 40px 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-form { width: 100%; max-width: 420px; }

        .portal-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(178, 232, 216, 0.42);
            border: 1px solid rgba(0, 91, 92, 0.22);
            color: #005b5c;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .portal-heading {
            margin: 22px 0 8px;
            font-size: clamp(1.9rem, 3.2vw, 2.7rem);
            line-height: 1.05;
            letter-spacing: -0.03em;
        }

        .portal-copy {
            margin: 0 0 28px;
            color: var(--auth-muted);
            font-size: 0.98rem;
            line-height: 1.6;
        }

        .auth-status,
        .auth-error {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 16px;
            font-size: 0.92rem;
        }

        .auth-status {
            background: rgba(59, 130, 246, 0.08);
            border: 1px solid rgba(59, 130, 246, 0.16);
            color: #1d4ed8;
        }

        .auth-error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.18);
            color: #b91c1c;
        }

        .field-group { margin-bottom: 18px; }

        .field-label {
            display: block;
            margin-bottom: 10px;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            color: #4b5563;
        }

        .field-shell { position: relative; }

        .field-icon {
            position: absolute;
            top: 50%;
            left: 16px;
            transform: translateY(-50%);
            color: #6b7280;
            pointer-events: none;
        }

        .field-input {
            width: 100%;
            height: 56px;
            border-radius: 16px;
            border: 1px solid var(--auth-input-border);
            background: var(--auth-input);
            color: var(--auth-text);
            padding: 0 16px 0 48px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        .field-input::placeholder { color: #9ca3af; }

        .field-input:focus {
            border-color: rgba(0, 91, 92, 0.45);
            box-shadow: 0 0 0 4px rgba(0, 91, 92, 0.12);
            background: #ffffff;
        }

        .field-action {
            position: absolute;
            top: 50%;
            right: 14px;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #6b7280;
            padding: 4px;
            cursor: pointer;
        }

        .meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 10px 0 22px;
            font-size: 0.9rem;
        }

        .remember-check {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #4b5563;
        }

        .remember-check input {
            width: 18px;
            height: 18px;
            margin: 0;
            accent-color: var(--auth-accent-strong);
        }

        .meta-link,
        .secondary-link {
            color: #005b5c;
            text-decoration: none;
            font-weight: 500;
        }

        .meta-link:hover,
        .secondary-link:hover { color: #004345; }

        .submit-btn,
        .qr-btn {
            width: 100%;
            border-radius: 999px;
            font-size: 1rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
        }

        .submit-btn {
            height: 58px;
            border: 0;
            background: linear-gradient(135deg, var(--auth-accent), var(--auth-accent-strong));
            color: #000000;
            box-shadow: 0 16px 30px rgba(0, 91, 92, 0.22);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
            text-decoration: none;
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 20px 34px rgba(0, 91, 92, 0.28);
        }

        .qr-btn {
            margin-top: 14px;
            height: 56px;
            border: 1px solid rgba(0, 91, 92, 0.24);
            background: rgba(178, 232, 216, 0.42);
            color: #005b5c;
            text-decoration: none;
        }

        .auth-divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 24px 0 18px;
            color: #9ca3af;
            font-size: 0.86rem;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(0, 91, 92, 0.12);
        }

        .support-box {
            padding: 16px 18px;
            border-radius: 18px;
            background: var(--auth-surface-soft);
            border: 1px solid rgba(0, 91, 92, 0.16);
            color: var(--auth-muted);
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .support-box strong {
            display: block;
            color: var(--auth-text);
            margin-bottom: 4px;
        }

        .switch-link {
            margin-top: 18px;
            text-align: center;
            font-size: 0.92rem;
            color: var(--auth-muted);
        }

        .input-error {
            margin-top: 8px;
            color: #dc2626;
            font-size: 0.82rem;
        }

        @media (max-width: 920px) {
            .auth-panel {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .auth-hero {
                min-height: 320px;
            }
        }

        @media (max-width: 640px) {
            .auth-shell { padding: 0; }

            .auth-panel {
                border-radius: 0;
                min-height: 100dvh;
                border: 0;
            }

            .auth-hero {
                min-height: 46dvh;
                padding: 18px 18px 24px;
            }

            .auth-hero::before {
                inset: 18px;
                border-radius: 24px;
            }

            .brand-orb {
                width: 104px;
                height: 104px;
            }

            .brand-orb img {
                width: 66px;
                height: 66px;
            }

            .auth-subtitle {
                font-size: 0.95rem;
                max-width: 18rem;
            }

            .hero-note {
                padding: 14px 16px;
            }

            .auth-form-wrap {
                padding: 26px 18px 30px;
            }

            .portal-chip {
                font-size: 0.68rem;
                padding: 9px 12px;
            }

            .portal-heading {
                margin-top: 18px;
                font-size: 2rem;
            }

            .portal-copy {
                margin-bottom: 22px;
                font-size: 0.92rem;
            }

            .field-input {
                height: 54px;
                border-radius: 14px;
            }

            .meta-row {
                flex-wrap: wrap;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <div class="auth-panel">
            <section class="auth-hero">
                <div class="auth-topbar">
                    @if($backHref)
                        <a href="{{ $backHref }}" class="topbar-action" aria-label="{{ $backAria }}">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @endif
                </div>

                <div class="auth-center">
                    <div class="brand-orb">
                        <img src="{{ asset('images/logo.png') }}" alt="Icy's Simplicitea">
                    </div>
                    <div>
                        <h1 class="auth-title">{{ $heroTitle }}</h1>
                        <p class="auth-subtitle">{{ $heroSubtitle }}</p>
                    </div>
                </div>

                <div class="hero-note">
                    @if(isset($heroNoteIcon))
                        {{ $heroNoteIcon }}
                    @else
                        <svg width="20" height="20" fill="none" stroke="#d6ff4b" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    @endif
                    <div>
                        <strong>{{ $heroNoteTitle }}</strong>
                        <span>{{ $heroNoteText }}</span>
                    </div>
                </div>
            </section>

            <section class="auth-form-wrap">
                <div class="auth-form">
                    <span class="portal-chip">
                        @if(isset($chipIcon))
                            {{ $chipIcon }}
                        @else
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
                            </svg>
                        @endif
                        {{ $chipLabel }}
                    </span>

                    <h2 class="portal-heading">{{ $portalHeading }}</h2>
                    <p class="portal-copy">{{ $portalCopy }}</p>

                    {{ $slot }}

                    @if($dividerLabel)
                        <div class="auth-divider">{{ $dividerLabel }}</div>
                    @endif

                    @if($supportTitle || $supportText)
                        <div class="support-box">
                            @if($supportTitle)<strong>{{ $supportTitle }}</strong>@endif
                            {{ $supportText }}
                        </div>
                    @endif

                    @if($switchText && $switchLinkLabel)
                        <div class="switch-link">
                            {{ $switchText }} <a href="{{ $switchHref }}" class="secondary-link">{{ $switchLinkLabel }}</a>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>

    @isset($scripts)
        {{ $scripts }}
    @endisset
</body>
</html>
