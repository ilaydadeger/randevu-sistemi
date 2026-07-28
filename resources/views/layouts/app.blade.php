<!DOCTYPE html>
<html class="light" lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', "L'ART DE L'ONGLE")</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path fill='%237b5068' d='M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'/></svg>">
    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet"/>

    {{-- Material Symbols --}}
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        html { overflow-x: hidden; max-width: 100%; }
        body {
            background-color: #FDFBFB;
            color: #1e293b;
            min-height: 100dvh;
            overflow-x: hidden;
            max-width: 100%;
        }
        .bottom-nav-safe { padding-bottom: calc(8px + env(safe-area-inset-bottom)); }
        [x-cloak] { display: none !important; }
    </style>

    {{-- Page-specific styles --}}
    @stack('styles')
</head>
<body class="bg-[#FDFBFB] text-slate-800 antialiased min-h-screen w-full flex flex-col relative">

    {{-- TopAppBar --}}
    <header class="sticky top-0 z-20 w-full bg-gradient-to-r from-[#EADDD9] via-[#EAE1E3] to-[#E3D5DB] py-4 px-5 shadow-sm shadow-rose-900/5 backdrop-blur-md">
        <h1 class="text-xl font-medium tracking-wide text-[#5C4D53] flex items-center justify-center gap-1.5" style="font-family: 'Playfair Display', serif;">
            {{ request()->is('panel/*') ? (auth()->user()->salon_name ?? "L'ART DE L'ONGLE") : (($nailTech->salon_name ?? null) ?: "L'ART DE L'ONGLE") }}
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
        </h1>
    </header>

    {{-- Desktop Navigation Bar (Sadece Tırnakçı Paneli İçin, Masaüstü) --}}
    @if(request()->is('panel/*'))
    <nav class="hidden md:flex justify-center items-center gap-10 py-3 bg-white border-b border-[#F2EAEB] w-full sticky top-[56px] z-40 shadow-sm">
        <a href="{{ route('panel.preview') }}" class="flex items-center gap-2 {{ request()->routeIs('panel.preview') ? 'text-[#B3939B] font-bold' : 'text-slate-400 hover:text-[#B3939B] transition-colors' }}">
            <span class="material-symbols-outlined" style="font-size: 20px; @if(request()->routeIs('panel.preview')) font-variation-settings: 'FILL' 1; @endif">home</span>
            <span class="text-xs tracking-wider">Önizleme</span>
        </a>
        <a href="{{ route('panel.appointments') }}" class="flex items-center gap-2 {{ request()->routeIs('panel.appointments') ? 'text-[#B3939B] font-bold' : 'text-slate-400 hover:text-[#B3939B] transition-colors' }}">
            <span class="material-symbols-outlined" style="font-size: 20px; @if(request()->routeIs('panel.appointments')) font-variation-settings: 'FILL' 1; @endif">calendar_today</span>
            <span class="text-xs tracking-wider">Randevular</span>
        </a>
        <a href="{{ route('panel.book') }}" class="flex items-center gap-2 {{ request()->routeIs('panel.book') ? 'text-[#B3939B] font-bold' : 'text-slate-400 hover:text-[#B3939B] transition-colors' }}">
            <span class="material-symbols-outlined" style="font-size: 20px; @if(request()->routeIs('panel.book')) font-variation-settings: 'FILL' 1; @endif">explore</span>
            <span class="text-xs tracking-wider">Fiyatlarım</span>
        </a>
        <a href="{{ route('panel.profile') }}" class="flex items-center gap-2 {{ request()->routeIs('panel.profile') ? 'text-[#B3939B] font-bold' : 'text-slate-400 hover:text-[#B3939B] transition-colors' }}">
            <span class="material-symbols-outlined" style="font-size: 20px; @if(request()->routeIs('panel.profile')) font-variation-settings: 'FILL' 1; @endif">person</span>
            <span class="text-xs tracking-wider">Profil</span>
        </a>
    </nav>
    @endif

    {{-- Main Content --}}
    @yield('content')

    {{-- BottomNavBar (Mobile Only - Sadece Tırnakçı Paneli İçin) --}}
    @if(request()->is('panel/*'))
    <nav class="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-xl border-t border-[#F2EAEB] pt-2 px-6 z-30 md:hidden"
         style="padding-bottom: calc(16px + env(safe-area-inset-bottom));">
        <div class="flex justify-between items-center max-w-lg mx-auto">
            <a href="{{ route('panel.preview') }}" class="flex flex-col items-center gap-1.5 p-2 {{ request()->routeIs('panel.preview') ? 'text-[#B3939B]' : 'text-slate-400 hover:text-[#B3939B] transition-colors' }}">
                <span class="material-symbols-outlined" style="font-size: 24px; @if(request()->routeIs('panel.preview')) font-variation-settings: 'FILL' 1; @endif">home</span>
                <span class="text-[10px] {{ request()->routeIs('panel.preview') ? 'font-semibold' : 'font-medium' }} tracking-wide">Önizleme</span>
            </a>
            <a href="{{ route('panel.appointments') }}" class="flex flex-col items-center gap-1.5 p-2 {{ request()->routeIs('panel.appointments') ? 'text-[#B3939B]' : 'text-slate-400 hover:text-[#B3939B] transition-colors' }}">
                <span class="material-symbols-outlined" style="font-size: 24px; @if(request()->routeIs('panel.appointments')) font-variation-settings: 'FILL' 1; @endif">calendar_today</span>
                <span class="text-[10px] {{ request()->routeIs('panel.appointments') ? 'font-semibold' : 'font-medium' }} tracking-wide">Randevular</span>
            </a>
            <a href="{{ route('panel.book') }}" class="flex flex-col items-center gap-1.5 p-2 {{ request()->routeIs('panel.book') ? 'text-[#B3939B]' : 'text-slate-400 hover:text-[#B3939B] transition-colors' }}">
                <span class="material-symbols-outlined" style="font-size: 24px; @if(request()->routeIs('panel.book')) font-variation-settings: 'FILL' 1; @endif">explore</span>
                <span class="text-[10px] {{ request()->routeIs('panel.book') ? 'font-semibold' : 'font-medium' }} tracking-wide">Fiyatlarım</span>
            </a>
            <a href="{{ route('panel.profile') }}" class="flex flex-col items-center gap-1.5 p-2 {{ request()->routeIs('panel.profile') ? 'text-[#B3939B]' : 'text-slate-400 hover:text-[#B3939B] transition-colors' }}">
                <span class="material-symbols-outlined" style="font-size: 24px; @if(request()->routeIs('panel.profile')) font-variation-settings: 'FILL' 1; @endif">person</span>
                <span class="text-[10px] {{ request()->routeIs('panel.profile') ? 'font-semibold' : 'font-medium' }} tracking-wide">Profil</span>
            </a>
        </div>
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
                    if(config === undefined) config = {};
                    if(config.headers === undefined) config.headers = {};

                    if(config.method && !['GET', 'HEAD'].includes(config.method.toUpperCase())) {
                        if (config.headers instanceof Headers) {
                            config.headers.append('X-CSRF-TOKEN', csrfToken);
                            if (!config.headers.has('Accept')) config.headers.append('Accept', 'application/json');
                        } else {
                            config.headers['X-CSRF-TOKEN'] = csrfToken;
                            config.headers['Accept'] = 'application/json';
                        }
                    }
                    return originalFetch(resource, config);
                };
            }
        });
    </script>
</body>
</html>
