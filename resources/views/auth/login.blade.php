<!DOCTYPE html>
<html class="light" lang="tr">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Kayıt & Giriş - L'ART DE L'ONGLE</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
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
                borderRadius: {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
                },
                spacing: {
                    "margin-mobile": "16px",
                    "margin-desktop": "64px",
                    "sm": "12px",
                    "gutter": "24px",
                    "md": "24px",
                    "lg": "48px",
                    "base": "8px",
                    "xl": "80px",
                    "xs": "4px"
                },
                fontFamily: {
                    "label-caps": ["Inter"],
                    "headline-md": ["Playfair Display"],
                    "headline-sm": ["Playfair Display"],
                    "display-lg": ["Playfair Display"],
                    "body-lg": ["Inter"],
                    "display-lg-mobile": ["Playfair Display"],
                    "body-md": ["Inter"]
                },
                fontSize: {
                    "label-caps": ["12px", { lineHeight: "1.2", letterSpacing: "0.1em", fontWeight: "600" }],
                    "headline-md": ["32px", { lineHeight: "1.3", fontWeight: "600" }],
                    "headline-sm": ["24px", { lineHeight: "1.4", fontWeight: "600" }],
                    "display-lg": ["48px", { lineHeight: "1.2", letterSpacing: "-0.02em", fontWeight: "700" }],
                    "body-lg": ["18px", { lineHeight: "1.6", fontWeight: "400" }],
                    "display-lg-mobile": ["32px", { lineHeight: "1.2", fontWeight: "700" }],
                    "body-md": ["16px", { lineHeight: "1.6", fontWeight: "400" }]
                }
            }
        }
    }
</script>
<style>
    .glass-panel {
        background: rgba(252, 249, 248, 0.8);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }
    .soft-input {
        transition: border-color 0.3s ease, background-color 0.3s ease;
    }
    .soft-input:focus {
        outline: none;
        border-bottom-color: #7a5555;
        background-color: #f6f3f2;
    }
    body {
        min-height: max(884px, 100dvh);
    }
</style>
</head>
<body class="bg-background text-on-background min-h-screen flex flex-col antialiased relative">
<!-- Background Texture -->
<div class="absolute inset-0 z-0 pointer-events-none opacity-20 bg-cover bg-center" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDymVWrlxWMQWGaXktS19BUZg2dr61lPTfW0GgC48IBR-W8OfekB1UNQUcRN3gcspOMIx2VLpw3MIfqY4LGELYdYWJFtxP7oNmlp1ezie1k46XhgUCrHhKHiaYITlhp9bLSj7bhzJDWcrG-jJU1_HX1tmYyS7aJolL5TBPwKOpLhCsmxStxhtnqnvCuXd-MjwMo-Utv_CdUOhh8V7rtKTGi-FfcdmkDmQ1F-VCLYYMLBJ6TN-j6wN6YLvoYEvq_pEAZ9aDHCDuePKH2');"></div>

<!-- Top Navigation -->
<header class="w-full flex justify-between items-center px-margin-mobile h-16 z-10 glass-panel border-b border-surface-container/50">
    <a href="{{ url('/') }}" class="text-on-surface-variant hover:opacity-80 transition-opacity p-2 -ml-2">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">arrow_back</span>
    </a>
    <h1 class="font-headline-sm text-headline-sm tracking-widest text-primary">L'ART DE L'ONGLE</h1>
    <div class="w-10"></div>
</header>

<!-- Main Content -->
<main class="flex-grow flex flex-col justify-center px-margin-mobile py-lg z-10 relative">
    <div class="w-full max-w-md mx-auto space-y-xl">
        <div class="text-center space-y-sm">
            <h2 class="font-display-lg-mobile text-display-lg-mobile text-primary">Welcome Back</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Sign in to continue your journey of elegance.</p>
        </div>

        @if($errors->any())
            <div class="bg-error-container text-on-error-container p-4 rounded-xl text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-md">
            @csrf
            <div class="space-y-sm">
                <label class="font-label-caps text-label-caps text-secondary block pl-1">Email Address</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-tertiary-container" style="font-variation-settings: 'FILL' 0;">mail</span>
                    <input name="email" value="{{ old('email') }}" required class="w-full bg-surface-container-low border-0 border-b-2 border-surface-variant soft-input font-body-md text-body-md px-4 py-3 pl-10 rounded-t-lg text-on-surface placeholder-tertiary-container" placeholder="hello@example.com" type="email"/>
                </div>
            </div>
            
            <div class="space-y-sm">
                <label class="font-label-caps text-label-caps text-secondary block pl-1">Password</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-tertiary-container" style="font-variation-settings: 'FILL' 0;">lock</span>
                    <input name="password" required class="w-full bg-surface-container-low border-0 border-b-2 border-surface-variant soft-input font-body-md text-body-md px-4 py-3 pl-10 rounded-t-lg text-on-surface placeholder-tertiary-container" placeholder="••••••••" type="password"/>
                </div>
                <div class="flex justify-between items-center pt-1">
                    <label class="flex items-center space-x-2 text-sm text-secondary">
                        <input type="checkbox" name="remember" class="rounded border-surface-variant text-primary focus:ring-primary h-4 w-4">
                        <span>Beni Hatırla</span>
                    </label>
                    <a class="font-label-caps text-label-caps text-primary hover:text-primary-container transition-colors" href="#">Forgot Password?</a>
                </div>
            </div>

            <div class="pt-sm">
                <button type="submit" class="w-full bg-gradient-to-r from-primary to-primary-container text-on-primary font-label-caps text-label-caps py-4 rounded-full shadow-[0px_20px_40px_rgba(200,155,155,0.15)] hover:opacity-90 transition-opacity flex items-center justify-center gap-2">
                    Sign In
                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 0;">arrow_forward</span>
                </button>
            </div>
        </form>

        <!-- Divider -->
        <div class="relative flex items-center py-md">
            <div class="flex-grow border-t border-surface-variant"></div>
            <span class="flex-shrink-0 px-4 font-label-caps text-label-caps text-tertiary">OR</span>
            <div class="flex-grow border-t border-surface-variant"></div>
        </div>

        <!-- Social Logins -->
        <div class="space-y-sm">
            <button class="w-full bg-surface-container-low border border-surface-variant text-on-surface font-label-caps text-label-caps py-3 rounded-full hover:bg-surface-container transition-colors flex items-center justify-center gap-3">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"></path><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path></svg>
                Continue with Google
            </button>
        </div>
    </div>
</main>
</body>
</html>
