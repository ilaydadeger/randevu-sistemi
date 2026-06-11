<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Yönetici Paneli - L'ART DE L'ONGLE</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    {{-- Alpine JS for Modals --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {{-- SweetAlert2 for Toast --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-container-low": "#f6f3f2",
                        "primary-fixed": "#ffdad9",
                        "on-secondary-fixed": "#1f1b14",
                        "secondary-fixed": "#ebe1d6",
                        "outline": "#827473",
                        "surface-variant": "#e5e2e1",
                        "surface-container-high": "#eae7e7",
                        "surface-container": "#f0eded",
                        "background": "#fcf9f8",
                        "primary-fixed-dim": "#ebbbbb",
                        "on-secondary": "#ffffff",
                        "error": "#ba1a1a",
                        "surface": "#fcf9f8",
                        "primary-container": "#c89b9b",
                        "surface-container-lowest": "#ffffff",
                        "on-surface": "#1b1c1c",
                        "on-surface-variant": "#504444",
                        "error-container": "#ffdad6",
                        "primary": "#7a5555",
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "fontFamily": {
                        "headline-md": ["Playfair Display"],
                        "body-lg": ["Inter"],
                        "headline-sm": ["Playfair Display"],
                        "display-lg": ["Playfair Display"],
                        "label-caps": ["Inter"],
                        "body-md": ["Inter"],
                        "display-lg-mobile": ["Playfair Display"]
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .icon-fill {
            font-variation-settings: 'FILL' 1;
        }
        .glass-modal {
            background: rgba(252, 249, 248, 0.95);
            backdrop-filter: blur(20px);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col md:flex-row antialiased" x-data="{ createModalOpen: false, editModalOpen: false, deleteModalOpen: false, selectedUser: {} }">

<!-- SideNav (Desktop) -->
<nav class="hidden md:flex flex-col w-64 bg-surface-container-low border-r border-surface-variant min-h-screen p-6 sticky top-0">
    <div class="mb-12">
        <h1 class="font-headline-sm text-2xl tracking-widest text-primary">L'ART</h1>
        <p class="font-label-caps text-xs text-on-surface-variant tracking-[0.2em]">SÜPER ADMİN</p>
    </div>
    <ul class="space-y-3 flex-grow">
        <li>
            <a class="flex items-center gap-3 px-4 py-3 rounded-full bg-primary-container/30 text-primary font-bold transition-colors" href="#">
                <span class="material-symbols-outlined icon-fill">groups</span>
                <span class="font-label-caps text-xs">Tırnakçılarım</span>
            </a>
        </li>
    </ul>
    <div class="mt-auto">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="flex items-center gap-3 px-4 py-3 rounded-full text-error hover:bg-error-container/20 transition-colors w-full text-left" type="submit">
                <span class="material-symbols-outlined">logout</span>
                <span class="font-label-caps text-xs">Çıkış Yap</span>
            </button>
        </form>
    </div>
</nav>

<!-- Main Content -->
<main class="flex-grow p-4 md:p-12 bg-background pb-32">
    <header class="mb-10">
        <h2 class="font-headline-md text-3xl text-on-surface mb-2">Uzman Yönetimi</h2>
        <p class="text-on-surface-variant">Sisteme kayıtlı tırnak uzmanlarını buradan yönetebilirsiniz.</p>
    </header>

    <div class="max-w-4xl">
        <!-- Header & Add Button -->
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-headline-sm text-xl text-on-surface">Tırnakçılarım ({{ $nailTechs->count() }})</h3>
            <button @click="createModalOpen = true" class="bg-gradient-to-r from-primary to-[#8a6565] text-white px-5 py-2.5 rounded-full font-label-caps text-xs flex items-center gap-2 shadow-md hover:opacity-90 transition-opacity">
                <span class="material-symbols-outlined text-sm">add</span>
                YENİ EKLE
            </button>
        </div>

        <!-- Artists List -->
        <div class="space-y-4">
            @forelse($nailTechs as $tech)
            <div class="bg-surface-container-lowest rounded-2xl p-5 flex items-center gap-4 border border-outline/20 hover:shadow-sm transition-shadow group">
                <div class="w-14 h-14 rounded-full bg-secondary-fixed flex items-center justify-center shrink-0 border border-outline/10">
                    <span class="material-symbols-outlined text-on-secondary-fixed text-2xl">face_3</span>
                </div>
                <div class="flex-grow min-w-0">
                    <h4 class="font-headline-sm text-lg text-on-surface truncate">{{ $tech->name }}</h4>
                    <p class="text-sm text-on-surface-variant truncate">{{ $tech->email }}</p>
                    @if($tech->slug)
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs text-primary truncate font-mono">{{ config('app.url') }}/{{ $tech->slug }}</span>
                        <button
                            onclick="navigator.clipboard.writeText('{{ config('app.url') }}/{{ $tech->slug }}').then(() => { this.innerHTML='<span class=\'material-symbols-outlined text-sm text-green-600\'>check</span>'; setTimeout(() => { this.innerHTML='<span class=\'material-symbols-outlined text-sm\'>content_copy</span>'; }, 1500); })"
                            class="shrink-0 text-on-surface-variant hover:text-primary transition-colors"
                            title="Linki kopyala">
                            <span class="material-symbols-outlined text-sm">content_copy</span>
                        </button>
                        <a href="{{ config('app.url') }}/{{ $tech->slug }}" target="_blank" class="shrink-0 text-on-surface-variant hover:text-primary transition-colors" title="Sayfayı aç">
                            <span class="material-symbols-outlined text-sm">open_in_new</span>
                        </a>
                    </div>
                    @endif
                </div>
                <div class="flex gap-2">
                    <button @click="selectedUser = { id: {{ $tech->id }}, name: '{{ addslashes($tech->name) }}', email: '{{ addslashes($tech->email) }}' }; editModalOpen = true" class="p-2.5 text-on-surface-variant hover:text-primary transition-colors rounded-full hover:bg-surface-container-high outline-none focus:ring-2 focus:ring-primary/50">
                        <span class="material-symbols-outlined text-xl">edit</span>
                    </button>
                    <button @click="selectedUser = { id: {{ $tech->id }}, name: '{{ addslashes($tech->name) }}' }; deleteModalOpen = true" class="p-2.5 text-on-surface-variant hover:text-error transition-colors rounded-full hover:bg-error-container/30 outline-none focus:ring-2 focus:ring-error/50">
                        <span class="material-symbols-outlined text-xl">delete</span>
                    </button>
                </div>
            </div>
            @empty
            <div class="text-center py-10 bg-surface-container-low rounded-2xl border border-dashed border-outline/30 text-on-surface-variant">
                Henüz kayıtlı bir uzman bulunmuyor.
            </div>
            @endforelse
        </div>
    </div>
</main>

{{-- MODALS --}}

{{-- Create Modal --}}
<div x-cloak x-show="createModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <div x-show="createModalOpen" x-transition.opacity class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm" @click="createModalOpen = false"></div>
    <div x-show="createModalOpen" x-transition.scale.origin.bottom class="relative glass-modal rounded-3xl w-full max-w-md p-8 shadow-2xl border border-white/20">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-headline-sm text-2xl text-on-surface">Yeni Uzman Ekle</h3>
            <button @click="createModalOpen = false" class="text-on-surface-variant hover:text-error transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="{{ route('admin.store') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="font-label-caps text-xs text-on-surface-variant block mb-1 ml-2">AD SOYAD</label>
                <input type="text" name="name" required class="w-full bg-surface-container-lowest border border-outline/30 focus:border-primary focus:ring-1 focus:ring-primary px-4 py-3 text-on-surface rounded-xl transition-colors">
            </div>
            <div>
                <label class="font-label-caps text-xs text-on-surface-variant block mb-1 ml-2">E-POSTA</label>
                <input type="email" name="email" required class="w-full bg-surface-container-lowest border border-outline/30 focus:border-primary focus:ring-1 focus:ring-primary px-4 py-3 text-on-surface rounded-xl transition-colors">
            </div>
            <div>
                <label class="font-label-caps text-xs text-on-surface-variant block mb-1 ml-2">ŞİFRE</label>
                <input type="password" name="password" required minlength="8" class="w-full bg-surface-container-lowest border border-outline/30 focus:border-primary focus:ring-1 focus:ring-primary px-4 py-3 text-on-surface rounded-xl transition-colors">
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-primary to-[#8a6565] text-white font-label-caps text-xs py-4 rounded-xl shadow-lg mt-4 hover:opacity-90 transition-opacity">
                KAYDET
            </button>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div x-cloak x-show="editModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <div x-show="editModalOpen" x-transition.opacity class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm" @click="editModalOpen = false"></div>
    <div x-show="editModalOpen" x-transition.scale.origin.bottom class="relative glass-modal rounded-3xl w-full max-w-md p-8 shadow-2xl border border-white/20">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-headline-sm text-2xl text-on-surface">Uzmanı Düzenle</h3>
            <button @click="editModalOpen = false" class="text-on-surface-variant hover:text-error transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form :action="`/admin/${selectedUser.id}`" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="font-label-caps text-xs text-on-surface-variant block mb-1 ml-2">AD SOYAD</label>
                <input type="text" name="name" x-model="selectedUser.name" required class="w-full bg-surface-container-lowest border border-outline/30 focus:border-primary focus:ring-1 focus:ring-primary px-4 py-3 text-on-surface rounded-xl transition-colors">
            </div>
            <div>
                <label class="font-label-caps text-xs text-on-surface-variant block mb-1 ml-2">E-POSTA</label>
                <input type="email" name="email" x-model="selectedUser.email" required class="w-full bg-surface-container-lowest border border-outline/30 focus:border-primary focus:ring-1 focus:ring-primary px-4 py-3 text-on-surface rounded-xl transition-colors">
            </div>
            <div>
                <label class="font-label-caps text-xs text-on-surface-variant block mb-1 ml-2">YENİ ŞİFRE (Opsiyonel)</label>
                <input type="password" name="password" placeholder="Değiştirmek istemiyorsanız boş bırakın" minlength="8" class="w-full bg-surface-container-lowest border border-outline/30 focus:border-primary focus:ring-1 focus:ring-primary px-4 py-3 text-on-surface rounded-xl transition-colors">
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-primary to-[#8a6565] text-white font-label-caps text-xs py-4 rounded-xl shadow-lg mt-4 hover:opacity-90 transition-opacity">
                GÜNCELLE
            </button>
        </form>
    </div>
</div>

{{-- Delete Modal --}}
<div x-cloak x-show="deleteModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <div x-show="deleteModalOpen" x-transition.opacity class="absolute inset-0 bg-on-surface/60 backdrop-blur-sm" @click="deleteModalOpen = false"></div>
    <div x-show="deleteModalOpen" x-transition.scale.origin.bottom class="relative bg-surface-container-lowest rounded-3xl w-full max-w-[24rem] p-8 shadow-2xl border border-error/20 text-center">
        <div class="w-20 h-20 bg-error-container/30 rounded-full flex items-center justify-center mx-auto mb-5 text-error">
            <span class="material-symbols-outlined text-4xl">warning</span>
        </div>
        <h3 class="font-headline-sm text-xl text-on-surface mb-2">Emin Misiniz?</h3>
        <p class="text-sm text-on-surface-variant mb-8"><strong x-text="selectedUser.name"></strong> isimli uzmanı kalıcı olarak silmek üzeresiniz. Bu işlem geri alınamaz.</p>
        
        <form :action="`/admin/${selectedUser.id}/delete`" method="POST" class="flex gap-3">
            @csrf
            <button type="button" @click="deleteModalOpen = false" class="flex-1 bg-surface-container text-on-surface-variant font-label-caps text-xs py-3 rounded-xl hover:bg-surface-container-high transition-colors">
                İPTAL
            </button>
            <button type="submit" class="flex-1 bg-error text-white font-label-caps text-xs py-3 rounded-xl shadow-md hover:bg-error/90 transition-colors">
                EVET, SİL
            </button>
        </form>
    </div>
</div>

{{-- Toast Notifications --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: {
                popup: 'rounded-xl shadow-lg border border-outline/10 bg-surface-container-lowest text-on-surface font-body-md'
            }
        });

        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}",
                iconColor: '#7a5555'
            });
        @endif

        @if($errors->any())
            Toast.fire({
                icon: 'error',
                title: "{{ $errors->first() }}",
                iconColor: '#ba1a1a'
            });
        @endif
    });
</script>

</body>
</html>
