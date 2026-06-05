<!DOCTYPE html>
<html class="light" lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', "L'ART DE L'ONGLE")</title>

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet"/>

    {{-- Material Symbols --}}
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

    {{-- Tailwind Config --}}
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "error": "#ba1a1a",
                        "on-tertiary": "#ffffff",
                        "inverse-primary": "#ebbbbb",
                        "on-primary-container": "#533333",
                        "on-secondary-fixed-variant": "#4c463e",
                        "secondary-fixed-dim": "#cec5bb",
                        "on-tertiary-fixed-variant": "#4b4642",
                        "on-primary-fixed-variant": "#603e3e",
                        "surface-container-high": "#eae7e7",
                        "background": "#fcf9f8",
                        "on-surface-variant": "#504444",
                        "surface-container": "#f0eded",
                        "on-primary": "#ffffff",
                        "on-background": "#1b1c1c",
                        "secondary-fixed": "#ebe1d6",
                        "on-secondary-container": "#6a635b",
                        "tertiary-fixed": "#eae1dc",
                        "outline": "#827473",
                        "surface-tint": "#7a5555",
                        "on-surface": "#1b1c1c",
                        "primary-fixed": "#ffdad9",
                        "on-tertiary-container": "#403b38",
                        "tertiary-container": "#aca5a0",
                        "error-container": "#ffdad6",
                        "tertiary": "#635d5a",
                        "surface-container-low": "#f6f3f2",
                        "on-primary-fixed": "#2e1415",
                        "outline-variant": "#d4c2c2",
                        "tertiary-fixed-dim": "#cdc5c0",
                        "secondary-container": "#ebe1d6",
                        "inverse-on-surface": "#f3f0ef",
                        "on-error": "#ffffff",
                        "surface-container-lowest": "#ffffff",
                        "on-tertiary-fixed": "#1f1b18",
                        "surface-container-highest": "#e5e2e1",
                        "surface": "#fcf9f8",
                        "primary": "#7a5555",
                        "surface-dim": "#dcd9d9",
                        "surface-bright": "#fcf9f8",
                        "inverse-surface": "#303030",
                        "secondary": "#645d55",
                        "primary-fixed-dim": "#ebbbbb",
                        "on-error-container": "#93000a",
                        "on-secondary-fixed": "#1f1b14",
                        "on-secondary": "#ffffff",
                        "primary-container": "#c89b9b",
                        "surface-variant": "#e5e2e1"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "margin-mobile": "var(--spacing-margin-mobile)",
                        "margin-desktop": "var(--spacing-margin-desktop)",
                        "sm": "var(--spacing-sm)",
                        "gutter": "var(--spacing-gutter)",
                        "md": "var(--spacing-md)",
                        "lg": "var(--spacing-lg)",
                        "base": "var(--spacing-base)",
                        "xl": "var(--spacing-xl)",
                        "xs": "var(--spacing-xs)"
                    },
                    "fontFamily": {
                        "label-caps": ["Inter"],
                        "headline-md": ["Outfit", "sans-serif"],
                        "headline-sm": ["Outfit", "sans-serif"],
                        "display-lg": ["Outfit", "sans-serif"],
                        "body-lg": ["Inter"],
                        "display-lg-mobile": ["Outfit", "sans-serif"],
                        "body-md": ["Inter"]
                    },
                    "fontSize": {
                        "label-caps": ["var(--fs-label-caps)", { "lineHeight": "1.2", "letterSpacing": "0.1em", "fontWeight": "600" }],
                        "headline-md": ["var(--fs-headline-md)", { "lineHeight": "1.3", "fontWeight": "600" }],
                        "headline-sm": ["var(--fs-headline-sm)", { "lineHeight": "1.4", "fontWeight": "600" }],
                        "display-lg": ["var(--fs-display-lg)", { "lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "body-lg": ["var(--fs-body-lg)", { "lineHeight": "1.6", "fontWeight": "400" }],
                        "display-lg-mobile": ["var(--fs-display-lg-mobile)", { "lineHeight": "1.2", "fontWeight": "700" }],
                        "body-md": ["var(--fs-body-md)", { "lineHeight": "1.6", "fontWeight": "400" }]
                    }
                }
            }
        }
    </script>

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
<body class="bg-background text-on-background font-body-md antialiased min-h-screen flex flex-col relative selection:bg-primary-container selection:text-on-primary-container pb-20 sm:pb-24 md:pb-0">

    {{-- TopAppBar --}}
    <header class="docked full-width top-0 sticky z-50 bg-surface/70 dark:bg-surface-dim/70 backdrop-blur-md shadow-sm flex justify-between items-center w-full px-margin-mobile h-12 sm:h-16 md:px-margin-desktop">
        <div class="w-10"></div>
        <h1 class="font-headline-sm text-headline-sm tracking-widest text-primary dark:text-primary-fixed-dim">
            {{ request()->is('panel/*') ? (auth()->user()->salon_name ?? "L'ART DE L'ONGLE") : (($nailTech->salon_name ?? null) ?: "L'ART DE L'ONGLE") }}
        </h1>
        <div class="w-10"></div>
    </header>

    {{-- Main Content --}}
    @yield('content')

    {{-- BottomNavBar (Mobile Only - Sadece Tırnakçı Paneli İçin) --}}
    @if(request()->is('panel/*'))
    <nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-4 py-1.5 pb-safe bg-surface/80 dark:bg-surface-container/80 backdrop-blur-xl rounded-t-xl shadow-[0px_-4px_20px_rgba(200,155,155,0.08)] border-t border-surface-container/50 md:hidden">
        {{-- Home (Preview) --}}
        <a href="{{ route('panel.preview') }}" class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('panel.preview') ? 'text-primary dark:text-primary-fixed-dim bg-primary-container/30 dark:bg-primary-container/20 rounded-full px-3.5 py-0.5 scale-105 transition-transform duration-300 ease-out' : 'text-secondary dark:text-secondary-fixed-dim opacity-60 hover:text-primary transition-colors' }}">
            <span class="material-symbols-outlined" data-icon="home" style="font-size: 20px; @if(request()->routeIs('panel.preview')) font-variation-settings: 'FILL' 1; @endif">home</span>
            <span class="font-label-caps text-label-caps text-[9px]">Önizleme</span>
        </a>
        
        {{-- Discover/Bookings --}}
        <a href="{{ route('panel.appointments') }}" class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('panel.appointments') ? 'text-primary dark:text-primary-fixed-dim bg-primary-container/30 dark:bg-primary-container/20 rounded-full px-3.5 py-0.5 scale-105 transition-transform duration-300 ease-out' : 'text-secondary dark:text-secondary-fixed-dim opacity-60 hover:text-primary transition-colors' }}">
            <span class="material-symbols-outlined" data-icon="calendar_today" style="font-size: 20px; @if(request()->routeIs('panel.appointments')) font-variation-settings: 'FILL' 1; @endif">calendar_today</span>
            <span class="font-label-caps text-label-caps text-[9px]">Randevular</span>
        </a>
        
        {{-- Fiyatlarım / Hizmetlerim --}}
        <a href="{{ route('panel.book') }}" class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('panel.book') ? 'text-primary dark:text-primary-fixed-dim bg-primary-container/30 dark:bg-primary-container/20 rounded-full px-3.5 py-0.5 scale-105 transition-transform duration-300 ease-out' : 'text-secondary dark:text-secondary-fixed-dim opacity-60 hover:text-primary transition-colors' }}">
            <span class="material-symbols-outlined" data-icon="explore" style="font-size: 20px; @if(request()->routeIs('panel.book')) font-variation-settings: 'FILL' 1; @endif">explore</span>
            <span class="font-label-caps text-label-caps text-[9px]">Fiyatlarım</span>
        </a>
        
        {{-- Profile --}}
        <a href="{{ route('panel.profile') }}" class="flex flex-col items-center justify-center gap-0.5 {{ request()->routeIs('panel.profile') ? 'text-primary dark:text-primary-fixed-dim bg-primary-container/30 dark:bg-primary-container/20 rounded-full px-3.5 py-0.5 scale-105 transition-transform duration-300 ease-out' : 'text-secondary dark:text-secondary-fixed-dim opacity-60 hover:text-primary transition-colors' }}">
            <span class="material-symbols-outlined" data-icon="person" style="font-size: 20px; @if(request()->routeIs('panel.profile')) font-variation-settings: 'FILL' 1; @endif">person</span>
            <span class="font-label-caps text-label-caps text-[9px]">Profil</span>
        </a>
    </nav>
    @endif

    {{-- Page-specific scripts --}}
    @stack('scripts')
</body>
</html>
