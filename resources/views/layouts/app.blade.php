<!DOCTYPE html>
<html class="light" lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', "L'ART DE L'ONGLE")</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path fill='%237a5555' d='M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'/></svg>">
    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet"/>

    {{-- Material Symbols --}}
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- HTMX for SPA transitions --}}
    @once
        <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    @endonce

    {{-- Shared Styles --}}
    <style>
        :root {
            --spacing-margin-mobile: 12px;
            --spacing-margin-desktop: 64px;
            --spacing-xs: 3px;
            --spacing-base: 6px;
            --spacing-sm: 9px;
            --spacing-md: 18px;
            --spacing-lg: 32px;
            --spacing-xl: 54px;
            --spacing-gutter: 16px;

            --fs-label-caps: 11px;
            --fs-body-md: 14px;
            --fs-body-lg: 16px;
            --fs-headline-sm: 17px;
            --fs-headline-md: 21px;
            --fs-display-lg: 30px;
            --fs-display-lg-mobile: 21px;
        }

        @media (min-width: 640px) {
            :root {
                --spacing-margin-mobile: 16px;
                --spacing-xs: 4px;
                --spacing-base: 8px;
                --spacing-sm: 12px;
                --spacing-md: 24px;
                --spacing-lg: 48px;
                --spacing-xl: 80px;
                --spacing-gutter: 24px;

                --fs-label-caps: 12px;
                --fs-body-md: 16px;
                --fs-body-lg: 18px;
                --fs-headline-sm: 20px;
                --fs-headline-md: 26px;
                --fs-display-lg: 40px;
                --fs-display-lg-mobile: 26px;
            }
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined.filled {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        body {
            background-color: #fcf9f8;
            color: #1b1c1c;
            min-height: max(884px, 100dvh);
        }
        [x-cloak] {
            display: none !important;
        }
    </style>

    {{-- Page-specific styles --}}
    @stack('styles')
</head>
<body class="bg-background text-on-background font-body-md antialiased min-h-screen flex flex-col relative selection:bg-primary-container selection:text-on-primary-container pb-20 sm:pb-24 md:pb-0" hx-boost="true">

    {{-- TopAppBar --}}
    <header class="docked full-width top-0 sticky z-50 bg-[#fdfaf8] flex justify-center items-center w-full px-margin-mobile h-12 sm:h-16 md:px-margin-desktop border-b border-surface-container-highest">
        <h1 class="font-headline-sm text-headline-sm tracking-widest text-[#7a5555] font-medium text-center">
            {{ request()->is('panel/*') ? (auth()->user()->salon_name ?? "L'ART DE L'ONGLE") : (($nailTech->salon_name ?? null) ?: "L'ART DE L'ONGLE") }}
        </h1>
    </header>

    {{-- Desktop Navigation Bar (Sadece Tırnakçı Paneli İçin, Masaüstü) --}}
    @if(request()->is('panel/*'))
    <nav class="hidden md:flex justify-center items-center gap-10 py-3 bg-[#fdfaf8] border-b border-surface-container-highest w-full sticky top-16 z-40 shadow-sm">
        <a href="{{ route('panel.preview') }}" class="flex items-center gap-2 {{ request()->routeIs('panel.preview') ? 'text-[#7a5555] font-bold' : 'text-on-surface-variant hover:text-[#7a5555] transition-colors' }}">
            <span class="material-symbols-outlined" style="font-size: 20px; @if(request()->routeIs('panel.preview')) font-variation-settings: 'FILL' 1; @endif">home</span>
            <span class="font-label-caps tracking-wider text-xs">Önizleme</span>
        </a>
        
        <a href="{{ route('panel.appointments') }}" class="flex items-center gap-2 {{ request()->routeIs('panel.appointments') ? 'text-[#7a5555] font-bold' : 'text-on-surface-variant hover:text-[#7a5555] transition-colors' }}">
            <span class="material-symbols-outlined" style="font-size: 20px; @if(request()->routeIs('panel.appointments')) font-variation-settings: 'FILL' 1; @endif">calendar_today</span>
            <span class="font-label-caps tracking-wider text-xs">Randevular</span>
        </a>
        
        <a href="{{ route('panel.book') }}" class="flex items-center gap-2 {{ request()->routeIs('panel.book') ? 'text-[#7a5555] font-bold' : 'text-on-surface-variant hover:text-[#7a5555] transition-colors' }}">
            <span class="material-symbols-outlined" style="font-size: 20px; @if(request()->routeIs('panel.book')) font-variation-settings: 'FILL' 1; @endif">explore</span>
            <span class="font-label-caps tracking-wider text-xs">Fiyatlarım</span>
        </a>
        
        <a href="{{ route('panel.profile') }}" class="flex items-center gap-2 {{ request()->routeIs('panel.profile') ? 'text-[#7a5555] font-bold' : 'text-on-surface-variant hover:text-[#7a5555] transition-colors' }}">
            <span class="material-symbols-outlined" style="font-size: 20px; @if(request()->routeIs('panel.profile')) font-variation-settings: 'FILL' 1; @endif">person</span>
            <span class="font-label-caps tracking-wider text-xs">Profil</span>
        </a>
    </nav>
    @endif


    {{-- Main Content --}}
    @yield('content')

    {{-- BottomNavBar (Mobile Only - Sadece Tırnakçı Paneli İçin) --}}
    @if(request()->is('panel/*'))
    <nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-4 py-1.5 pb-safe bg-surface/90 dark:bg-surface-container/90 backdrop-blur-sm rounded-t-xl shadow-[0px_-4px_20px_rgba(0,0,0,0.05)] border-t border-outline-variant/30 md:hidden">
        {{-- Home (Preview) --}}
        <a href="{{ route('panel.preview') }}" class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('panel.preview') ? 'text-primary font-bold bg-primary-container/40 dark:bg-primary-container/40 rounded-full px-3.5 py-0.5 scale-105 transition-transform duration-300 ease-out' : 'text-on-surface-variant font-medium opacity-90 hover:text-primary hover:opacity-100 transition-colors' }}">
            <span class="material-symbols-outlined" data-icon="home" style="font-size: 22px; @if(request()->routeIs('panel.preview')) font-variation-settings: 'FILL' 1; @endif">home</span>
            <span class="font-label-caps text-label-caps text-[10px]">Önizleme</span>
        </a>
        
        {{-- Discover/Bookings --}}
        <a href="{{ route('panel.appointments') }}" class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('panel.appointments') ? 'text-primary font-bold bg-primary-container/40 dark:bg-primary-container/40 rounded-full px-3.5 py-0.5 scale-105 transition-transform duration-300 ease-out' : 'text-on-surface-variant font-medium opacity-90 hover:text-primary hover:opacity-100 transition-colors' }}">
            <span class="material-symbols-outlined" data-icon="calendar_today" style="font-size: 22px; @if(request()->routeIs('panel.appointments')) font-variation-settings: 'FILL' 1; @endif">calendar_today</span>
            <span class="font-label-caps text-label-caps text-[10px]">Randevular</span>
        </a>
        
        {{-- Fiyatlarım / Hizmetlerim --}}
        <a href="{{ route('panel.book') }}" class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('panel.book') ? 'text-primary font-bold bg-primary-container/40 dark:bg-primary-container/40 rounded-full px-3.5 py-0.5 scale-105 transition-transform duration-300 ease-out' : 'text-on-surface-variant font-medium opacity-90 hover:text-primary hover:opacity-100 transition-colors' }}">
            <span class="material-symbols-outlined" data-icon="explore" style="font-size: 22px; @if(request()->routeIs('panel.book')) font-variation-settings: 'FILL' 1; @endif">explore</span>
            <span class="font-label-caps text-label-caps text-[10px]">Fiyatlarım</span>
        </a>
        
        {{-- Profile --}}
        <a href="{{ route('panel.profile') }}" class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('panel.profile') ? 'text-primary font-bold bg-primary-container/40 dark:bg-primary-container/40 rounded-full px-3.5 py-0.5 scale-105 transition-transform duration-300 ease-out' : 'text-on-surface-variant font-medium opacity-90 hover:text-primary hover:opacity-100 transition-colors' }}">
            <span class="material-symbols-outlined" data-icon="person" style="font-size: 22px; @if(request()->routeIs('panel.profile')) font-variation-settings: 'FILL' 1; @endif">person</span>
            <span class="font-label-caps text-label-caps text-[10px]">Profil</span>
        </a>
    </nav>
    @endif

    {{-- Page-specific scripts --}}
    @stack('scripts')
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // Native fetch interceptor to always add CSRF token
            if (csrfToken) {
                const originalFetch = window.fetch;
                window.fetch = async function () {
                    let [resource, config] = arguments;
                    if(config === undefined) {
                        config = {};
                    }
                    if(config.headers === undefined) {
                        config.headers = {};
                    }
                    
                    if(config.method && !['GET', 'HEAD'].includes(config.method.toUpperCase())) {
                        // Check if headers is Headers object
                        if (config.headers instanceof Headers) {
                            config.headers.append('X-CSRF-TOKEN', csrfToken);
                            if (!config.headers.has('Accept')) {
                                config.headers.append('Accept', 'application/json');
                            }
                        } else {
                            config.headers['X-CSRF-TOKEN'] = csrfToken;
                            config.headers['Accept'] = 'application/json';
                        }
                    }
                    return originalFetch(resource, config);
                };
            }
        });
        
        // Re-initialize Alpine on HTMX swaps
        document.body.addEventListener('htmx:afterSwap', function(event) {
            if (typeof Alpine !== 'undefined') {
                Alpine.initTree(document.body);
            }
        });
    </script>
</body>
</html>
