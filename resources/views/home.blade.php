@extends('layouts.app')

@section('title', "Ana Sayfa - " . ($nailTech->salon_name ?? "L'ART DE L'ONGLE"))

@section('content')
    @php
        $uploadedImages = [];
        if ($nailTech) {
            if ($nailTech->portfolio_image_1) {
                $uploadedImages[] = str_starts_with($nailTech->portfolio_image_1, 'http') ? $nailTech->portfolio_image_1 : asset('storage/' . $nailTech->portfolio_image_1);
            }
            if ($nailTech->portfolio_image_2) {
                $uploadedImages[] = str_starts_with($nailTech->portfolio_image_2, 'http') ? $nailTech->portfolio_image_2 : asset('storage/' . $nailTech->portfolio_image_2);
            }
            if ($nailTech->portfolio_image_3) {
                $uploadedImages[] = str_starts_with($nailTech->portfolio_image_3, 'http') ? $nailTech->portfolio_image_3 : asset('storage/' . $nailTech->portfolio_image_3);
            }
        }

        // Fetch blocked slots and occupied slots directly inside the blade
        $blockedSlots = [];
        $occupiedSlots = [];
        if ($nailTech) {
            $scheduleBlocks = clone $nailTech->scheduleBlocks;
            foreach ($scheduleBlocks as $block) {
                $blockedSlots[$block->blocked_date . '_' . substr($block->blocked_time, 0, 5)] = true;
            }

            $appointments = $nailTech->appointments
                ->whereIn('status', ['pending', 'approved'])
                ->where('appointment_date', '>=', today()->toDateString())
                ->values();

            foreach ($appointments as $appt) {
                $occupiedSlots[$appt->appointment_date . '_' . substr($appt->appointment_time, 0, 5)] = true;
            }
        }

        // Prepare next 28 days (4 weeks) grouped by week
        $weeks = [];
        for ($w = 0; $w < 4; $w++) {
            $weekDays = [];
            for ($d = 0; $d < 7; $d++) {
                $weekDays[] = \Carbon\Carbon::today()->addDays(($w * 7) + $d);
            }
            $weeks[$w] = $weekDays;
        }

        // Prepare hours from customizable work hours settings
        $hours = $nailTech->work_hours ?? ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];

        $nailTechPrices = $nailTech ? $nailTech->userPrices
            ->mapWithKeys(function ($userPrice) {
                return [$userPrice->serviceCategory->name => $userPrice->price];
            })->toArray() : [];

        // Base prices for yapim sub-types
        $baseProthezPrice = $nailTechPrices['Jel Protez'] ?? $nailTechPrices['Protez Tırnak'] ?? 0;
        // Jel Güçlendirme ve Kalıcı Oje de aynı fiyatı kullanır
        $baseJelGucPrice  = $baseProthezPrice;
        $baseKalyOjePrice = $baseProthezPrice;
        $baseCikarmaPrice = $nailTechPrices['Çıkarma'] ?? 0;
    @endphp

    <main
        class="flex-1 px-margin-mobile pt-md pb-[100px] flex flex-col gap-md max-w-[600px] md:max-w-3xl lg:max-w-4xl mx-auto w-full"
        x-data="galleryManager({ images: {{ json_encode($uploadedImages) }} })">

        {{-- Extended Glass Background (Behind Header & Profile Box) --}}
        <div class="absolute top-0 left-0 w-full h-[360px] bg-gradient-to-b from-[#FCFAFB]/60 via-[#ead5de]/40 to-transparent backdrop-blur-lg -z-10" style="border-bottom-left-radius: 40px; border-bottom-right-radius: 40px;"></div>

        {{-- Premium Profile Header --}}
        <section
            class="bg-white/80 backdrop-blur-sm rounded-2xl px-6 py-8 border border-[#EAD5DE]/60 shadow-[0_4px_24px_rgba(149,117,130,0.10)] flex flex-col items-center text-center gap-4">
            @if($nailTech && $nailTech->profile_photo_path)
                <div
                    class="relative w-28 h-28 rounded-full overflow-hidden ring-4 ring-[#EAD5DE]/70 shadow-[0_8px_24px_rgba(149,117,130,0.18)]">
                    <img src="{{ str_starts_with($nailTech->profile_photo_path, 'http') ? $nailTech->profile_photo_path : asset('storage/' . $nailTech->profile_photo_path) }}"
                        alt="Uzman Profil" class="w-full h-full object-cover">
                </div>
            @endif
            <div class="space-y-1">
                @if($nailTech && $nailTech->name)
                    <h2 class="text-[20px] font-bold text-[#3B2030]" style="font-family:'Playfair Display',serif;">{{ $nailTech->name }}</h2>
                @endif
            </div>
            @if($nailTech && $nailTech->bio)
                <p class="text-[13px] text-[#957582] leading-relaxed px-4 text-center w-full" style="min-width: 280px;">
                    {{ str_replace(["\r", "\n"], ' ', $nailTech->bio) }}
                </p>
            @endif
        </section>

        @if($nailTech && ($nailTech->show_portfolio ?? true))
            {{-- Portfolio Bento Grid --}}
            <section class="bg-surface-container-lowest rounded-xl p-md border border-outline-variant/30 shadow-sm space-y-md">
                <h3 class="font-headline-sm text-headline-sm border-b border-surface-container-highest pb-2">Portfolio</h3>
                <div class="grid grid-cols-2 gap-sm auto-rows-[160px]">

                    {{-- Box 1 --}}
                    <div class="rounded-xl overflow-hidden shadow-sm row-span-2 col-span-1 relative group cursor-pointer">
                        @if($nailTech && $nailTech->portfolio_image_1)
                            <img alt="Nail Art 1"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                src="{{ str_starts_with($nailTech->portfolio_image_1, 'http') ? $nailTech->portfolio_image_1 : asset('storage/' . $nailTech->portfolio_image_1) }}" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3"
                                @click="openLightbox(0)">
                                <button type="button"
                                    class="w-full py-2 bg-white/90 backdrop-blur-sm rounded-lg font-label-caps text-label-caps text-on-surface hover:bg-white transition-colors">Select
                                    Design</button>
                            </div>
                        @else
                            <div
                                class="w-full h-full bg-surface-container/60 border border-dashed border-outline-variant/30 flex flex-col items-center justify-center p-4 text-center rounded-xl">
                                <span class="material-symbols-outlined text-outline/50 text-2xl mb-1">add_a_photo</span>
                                <span class="text-[10px] text-on-surface-variant/60 font-label-caps">Görsel Yok</span>
                            </div>
                        @endif
                    </div>

                    {{-- Box 2 --}}
                    <div class="rounded-xl overflow-hidden shadow-sm col-span-1 relative group cursor-pointer">
                        @if($nailTech && $nailTech->portfolio_image_2)
                            <img alt="Nail Art 2"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                src="{{ str_starts_with($nailTech->portfolio_image_2, 'http') ? $nailTech->portfolio_image_2 : asset('storage/' . $nailTech->portfolio_image_2) }}" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-2"
                                @click="openLightbox(1)">
                                <button type="button"
                                    class="w-full py-1.5 bg-white/90 backdrop-blur-sm rounded-lg font-label-caps text-label-caps text-on-surface hover:bg-white transition-colors text-[10px]">Select</button>
                            </div>
                        @else
                            <div
                                class="w-full h-full bg-surface-container/60 border border-dashed border-outline-variant/30 flex flex-col items-center justify-center rounded-xl">
                                <span class="material-symbols-outlined text-outline/50 text-xl mb-1">add_a_photo</span>
                                <span class="text-[9px] text-on-surface-variant/60 font-label-caps">Görsel Yok</span>
                            </div>
                        @endif
                    </div>

                    {{-- Box 3 --}}
                    <div class="rounded-xl overflow-hidden shadow-sm col-span-1 relative group cursor-pointer">
                        @if($nailTech && $nailTech->portfolio_image_3)
                            <img alt="Nail Art 3"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                src="{{ str_starts_with($nailTech->portfolio_image_3, 'http') ? $nailTech->portfolio_image_3 : asset('storage/' . $nailTech->portfolio_image_3) }}" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-2"
                                @click="openLightbox(2)">
                                <button type="button"
                                    class="w-full py-1.5 bg-white/90 backdrop-blur-sm rounded-lg font-label-caps text-label-caps text-on-surface hover:bg-white transition-colors text-[10px]">Select</button>
                            </div>
                        @else
                            <div
                                class="w-full h-full bg-surface-container/60 border border-dashed border-outline-variant/30 flex flex-col items-center justify-center rounded-xl">
                                <span class="material-symbols-outlined text-outline/50 text-xl mb-1">add_a_photo</span>
                                <span class="text-[9px] text-on-surface-variant/60 font-label-caps">Görsel Yok</span>
                            </div>
                        @endif
                    </div>

                </div>
                <button type="button" @click="openGallery()"
                    class="w-full py-3 border border-outline-variant rounded-full font-label-caps text-label-caps text-on-surface hover:bg-surface-container transition-colors mt-4">View
                    Full Gallery</button>
            </section>

            {{-- GALLERY MODAL --}}
            <div x-cloak x-show="showModal"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                x-transition.opacity>
                <div class="bg-surface-container-lowest rounded-2xl w-full max-w-[450px] h-[520px] flex flex-col p-md shadow-2xl border border-outline-variant/30 relative"
                    @click.away="closeGallery()">
                    <!-- Close Button -->
                    <button @click="closeGallery()"
                        class="absolute top-4 right-4 text-on-surface-variant hover:opacity-80 p-1 rounded-full bg-surface-container">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>

                    <h3 class="font-headline-sm text-headline-sm text-on-surface mb-sm">Portföy Galerisi</h3>

                    <template x-if="images.length === 0">
                        <div class="flex-1 flex flex-col items-center justify-center text-on-surface-variant/60">
                            <span class="material-symbols-outlined text-4xl mb-2">image_not_supported</span>
                            <p class="font-body-md text-sm">Henüz portföy görseli eklenmemiş.</p>
                        </div>
                    </template>

                    <template x-if="images.length > 0">
                        <div class="flex-1 flex flex-col gap-md min-h-0">
                            <!-- Main Large Display -->
                            <div class="flex-1 relative rounded-xl overflow-hidden bg-surface-variant flex items-center justify-center border border-outline-variant/20 cursor-pointer group"
                                @click="openLightbox(activeIdx)">
                                <img :src="images[activeIdx]" class="w-full h-full object-cover" />
                                <div
                                    class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="material-symbols-outlined text-white text-3xl">zoom_in</span>
                                </div>
                            </div>

                            <!-- Bottom Thumbnail Slider -->
                            <div
                                class="flex gap-sm overflow-x-auto py-2 no-scrollbar border-t border-outline-variant/20 shrink-0">
                                <template x-for="(img, idx) in images" :key="idx">
                                    <div class="w-20 h-20 shrink-0 rounded-lg overflow-hidden border-2 cursor-pointer transition-all"
                                        :class="activeIdx === idx ? 'border-primary scale-95 shadow-sm' : 'border-transparent hover:opacity-80'"
                                        @click="activeIdx = idx">
                                        <img :src="img" class="w-full h-full object-cover" />
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- LIGHTBOX FULLSCREEN SLIDER --}}
            <div x-cloak x-show="showLightbox"
                class="fixed inset-0 z-[200] bg-black/95 flex flex-col items-center justify-center select-none"
                x-transition.opacity @keydown.escape.window="closeLightbox()" @keydown.arrow-left.window="prevImage()"
                @keydown.arrow-right.window="nextImage()">

                <!-- Close Lightbox Button -->
                <button @click="closeLightbox()"
                    class="absolute top-6 right-6 text-white/80 hover:text-white p-2 rounded-full hover:bg-white/10 z-[210]">
                    <span class="material-symbols-outlined text-3xl">close</span>
                </button>

                <!-- Next/Prev Buttons (Desktop) -->
                <button @click="prevImage()"
                    class="absolute left-6 text-white/60 hover:text-white hover:bg-white/10 p-3 rounded-full z-[210] hidden md:block transition-colors">
                    <span class="material-symbols-outlined text-4xl">chevron_left</span>
                </button>
                <button @click="nextImage()"
                    class="absolute right-6 text-white/60 hover:text-white hover:bg-white/10 p-3 rounded-full z-[210] hidden md:block transition-colors">
                    <span class="material-symbols-outlined text-4xl">chevron_right</span>
                </button>

                <!-- Large Image with Swipe handlers -->
                <div class="w-full max-w-4xl max-h-[80vh] flex items-center justify-center p-4 relative"
                    @touchstart="handleTouchStart($event)" @touchend="handleTouchEnd($event)">

                    <img :src="images[activeIdx]"
                        class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl transition-all duration-300" />

                    <!-- Index Counter -->
                    <div class="absolute bottom-[-40px] text-white/70 font-label-caps text-label-caps tracking-widest"
                        x-text="(activeIdx + 1) + ' / ' + images.length"></div>
                </div>
            </div>
        @endif

        {{-- Appointment Form --}}
        <section class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-[#EAD5DE]/60 shadow-[0_4px_24px_rgba(149,117,130,0.10)]" x-data="bookingCalendar({
                        blockedSlots: {{ json_encode($blockedSlots) }},
                        occupiedSlots: {{ json_encode($occupiedSlots) }},
                        hours: {{ json_encode($hours) }},
                        todayStr: '{{ today()->toDateString() }}'
                    })">
            <div class="mb-5 border-b border-[#EAD5DE]/40 pb-4 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold tracking-[0.15em] text-[#B496A1] uppercase mb-1">Adım 1</p>
                    <h3 class="text-[17px] font-bold text-[#3B2030]" style="font-family:'Playfair Display',serif;">İşlem Türü Seçin</h3>
                </div>
            </div>

            <form action="{{ route('appointment.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
                id="appointmentForm">
                @csrf
                <input type="hidden" name="nail_tech_id" value="{{ $nailTech->id ?? 1 }}">

                {{-- Service Type Selection --}}
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button"
                            @click="serviceType = 'yapim'; updateBasePrice()"
                            class="relative flex cursor-pointer rounded-2xl border-2 p-4 transition-all duration-200"
                            :class="serviceType === 'yapim' || serviceType === 'yapim_jel' || serviceType === 'yapim_kalici' ? 'border-[#95687A] bg-gradient-to-br from-[#F9F0F4] to-[#F1E4EC] shadow-[0_4px_16px_rgba(149,117,130,0.18)]' : 'border-[#EAD5DE]/70 bg-white/60 hover:border-[#C4A0B4] hover:shadow-sm'">
                            <div class="flex w-full items-center justify-center gap-2.5">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-all"
                                     :class="serviceType === 'yapim' || serviceType === 'yapim_jel' || serviceType === 'yapim_kalici' ? 'bg-[#95687A] text-white shadow-sm' : 'bg-[#F2E7EA] text-[#957582]'">
                                     <span class="material-symbols-outlined text-[20px]" style="font-weight: 300;">auto_fix_high</span>
                                </div>
                                <div class="flex flex-col items-start leading-tight gap-0.5">
                                    <span class="text-[11px] font-bold tracking-wide" :class="serviceType === 'yapim' || serviceType === 'yapim_jel' || serviceType === 'yapim_kalici' ? 'text-[#3B2030]' : 'text-[#6B4F5E]'">Protez Tırnak</span>
                                    <span class="text-[11px] font-bold tracking-wide" :class="serviceType === 'yapim' || serviceType === 'yapim_jel' || serviceType === 'yapim_kalici' ? 'text-[#3B2030]' : 'text-[#6B4F5E]'">Jel Güçlendirme</span>
                                    <span class="text-[11px] font-bold tracking-wide" :class="serviceType === 'yapim' || serviceType === 'yapim_jel' || serviceType === 'yapim_kalici' ? 'text-[#3B2030]' : 'text-[#6B4F5E]'">Kalıcı Oje</span>
                                </div>
                            </div>
                        </button>

                        <label
                            class="relative flex cursor-pointer rounded-2xl border-2 p-4 transition-all duration-200"
                            :class="serviceType === 'cikarma' ? 'border-[#95687A] bg-gradient-to-br from-[#F9F0F4] to-[#F1E4EC] shadow-[0_4px_16px_rgba(149,117,130,0.18)]' : 'border-[#EAD5DE]/70 bg-white/60 hover:border-[#C4A0B4] hover:shadow-sm'">
                            <input type="radio" value="cikarma" x-model="serviceType"
                                class="peer sr-only" @change="updateBasePrice()">
                            <div class="flex w-full items-center justify-center gap-2.5">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-all"
                                    :class="serviceType === 'cikarma' ? 'bg-[#95687A] text-white shadow-sm' : 'bg-[#F2E7EA] text-[#957582]'">
                                    <span class="material-symbols-outlined text-[20px]" style="font-weight: 300;">delete_sweep</span>
                                </div>
                                <div class="flex flex-col items-start leading-tight gap-0.5">
                                    <span class="text-[11px] font-bold tracking-wide" :class="serviceType === 'cikarma' ? 'text-[#3B2030]' : 'text-[#6B4F5E]'">Protez Tırnak</span>
                                    <span class="text-[11px] font-bold tracking-wide" :class="serviceType === 'cikarma' ? 'text-[#3B2030]' : 'text-[#6B4F5E]'">Çıkarma</span>
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- Hidden service_type input (real value) --}}
                    <input type="hidden" name="service_type" :value="serviceType">

                    {{-- Base fiyat gösterimi (görsel yüklenmeden önce) --}}
                    <div x-show="serviceType === 'yapim' && !aiPriceLoaded" x-transition class="mt-4 flex justify-end">
                        <div class="flex items-center gap-3 bg-white border border-[#EAD5DE] px-4 py-2.5 rounded-xl shadow-sm">
                            <span class="text-[12px] font-semibold text-[#957582]">Base Ücret</span>
                            <span class="text-[15px] font-bold text-[#3B2030]" x-text="'₺' + basePriceDisplay"></span>
                        </div>
                    </div>
                </div>

                {{-- Yapım (Image Upload and AI Price Estimation) Section --}}
                <div x-show="serviceType === 'yapim'" x-collapse class="space-y-5">

                    {{-- Section heading --}}
                    <div class="border-t border-[#EAD5DE]/40 pt-5">
                        <p class="text-[10px] font-bold tracking-[0.15em] text-[#B496A1] uppercase mb-1">Adım 2 (İsteğe Bağlı)</p>
                        <p class="text-[13px] text-[#957582]">İstediğiniz modelin fotoğrafını yükleyerek işleminizi hızlandırabilirsiniz.</p>
                    </div>

                    {{-- Image Upload (Drag & Drop) --}}
                    <div class="space-y-2">
                        <div id="dropzone"
                            class="relative w-full h-[200px] rounded-2xl border-2 border-dashed border-[#D2B4C1]/70 bg-gradient-to-br from-[#FBF5F8] to-[#F4E8EF] hover:from-[#F7EEF3] hover:to-[#EDD9E8] transition-all duration-300 flex flex-col items-center justify-center cursor-pointer overflow-hidden group shadow-inner">

                            <input type="file" name="design_image" id="fileInput"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*">

                            <div id="uploadPlaceholder"
                                class="flex flex-col items-center pointer-events-none transition-opacity duration-300">
                                <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center mb-3 shadow-[0_4px_12px_rgba(149,117,130,0.15)]">
                                    <span class="material-symbols-outlined text-[28px] text-[#95687A]" style="font-weight: 300;">cloud_upload</span>
                                </div>
                                <span class="text-[14px] text-[#3B2030] font-bold">Fotoğraf Yükle</span>
                                <span class="text-[12px] text-[#B496A1] mt-1">JPG, PNG veya WEBP (Maks. 5MB)</span>
                            </div>

                            <img id="imagePreview" class="absolute inset-0 w-full h-full object-cover hidden" alt="Preview">
                        </div>
                        <p class="text-[11px] text-[#B496A1] italic mt-1 text-center">* Lütfen yaptırmak istediğiniz tırnak modelinin yakından ve belirgin bir görselini yükleyin.</p>
                            
                        <div id="viewPriceBtnContainer" class="hidden mt-3 text-center">
                            <button type="button" id="viewPriceBtn" class="bg-[#95687A]/10 text-[#95687A] hover:bg-[#95687A]/20 border border-[#95687A]/25 px-4 py-2.5 rounded-full text-[11px] font-bold font-label-caps transition-all duration-200 w-full flex items-center justify-center gap-2 shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">calculate</span>
                                YAPAY ZEKA İLE TAHMİNİ FİYAT OLUŞTUR
                            </button>
                        </div>
                    </div>

                    {{-- AI Price Estimation Section --}}
                    <div id="priceEstimationSection"
                        class="fiyat-kutusu hidden bg-gradient-to-br from-[#FBF5F8] to-[#F4E8EF] rounded-2xl p-4 border border-[#D2B4C1]/50 flex flex-col gap-3 shadow-sm">
                        {{-- Loading / Status Row --}}
                        <div class="flex items-start gap-3">
                            <div id="priceSpinner" class="shrink-0 mt-0.5">
                                <span class="material-symbols-outlined text-[#95687A] animate-spin">progress_activity</span>
                            </div>
                            <div class="flex-1">
                                <div id="priceTitle" class="fiyat-gosterim font-body-md font-semibold text-[#95687A]">Fiyat Oluşturuluyor...</div>
                                <p id="priceDesc" class="hidden text-sm text-[#957582] mt-1"></p>
                            </div>
                        </div>

                        {{-- Price Display (Shown on success) --}}
                        <div id="serviceSelectorContainer"
                            class="hidden flex flex-col gap-2 pt-2 border-t border-[#D2B4C1]/30">
                            <div
                                class="flex justify-between items-center bg-white p-4 rounded-xl border border-[#EAD5DE]/60 shadow-sm">
                                <span
                                    class="text-[11px] font-bold text-[#957582] tracking-wider uppercase">Tahmini Toplam:</span>
                                <span id="singleTotalPrice" class="text-2xl font-black text-[#3B2030]">₺0</span>
                            </div>
                            <p class="text-[11px] text-[#B496A1] text-center italic mt-1">* Sadece tahminidir. Uzman randevu sırasında değiştirebilir.</p>
                        </div>
                    </div>
                </div>

                {{-- Çıkarma Price Display --}}
                <div x-show="serviceType === 'cikarma'" x-transition class="mt-4 flex justify-end">
                    <div class="flex items-center gap-3 bg-white border border-[#EAD5DE] px-4 py-2.5 rounded-xl shadow-sm">
                        <span class="text-[12px] font-semibold text-[#957582]">Çıkarma Ücreti</span>
                        <span class="text-[15px] font-bold text-[#3B2030]">₺{{ intval($baseCikarmaPrice) > 0 ? intval($baseCikarmaPrice) : '?' }}</span>
                    </div>
                </div>

                {{-- Client Details --}}
                <div class="border-t border-[#EAD5DE]/40 pt-5 space-y-2">
                    <p class="text-[10px] font-bold tracking-[0.15em] text-[#B496A1] uppercase mb-1">Adınız Soyadınız</p>
                    <input type="text" name="client_name" required
                        class="w-full bg-white/80 border border-[#EAD5DE]/70 focus:border-[#95687A] focus:ring-2 focus:ring-[#95687A]/20 px-4 py-3 text-[#3B2030] placeholder-[#C4A0B4] rounded-xl transition-all duration-200 outline-none text-[14px]"
                        placeholder="Adınızı giriniz...">
                </div>

                {{-- Calendar Slot Picker --}}
                <div class="border-t border-[#EAD5DE]/40 pt-5 space-y-4">
                    <div>
                        <p class="text-[10px] font-bold tracking-[0.15em] text-[#B496A1] uppercase mb-1">Adım 3</p>
                        <p class="text-[15px] font-bold text-[#3B2030]" style="font-family:'Playfair Display',serif;">Tarih & Saat Seçin</p>
                    </div>

                    {{-- Selected Slot Preview Alert --}}
                    <div class="p-3 bg-[#95687A]/10 border border-[#95687A]/25 rounded-xl flex items-center justify-between text-[#95687A] font-medium text-xs transition-all duration-300"
                        x-show="selectedDate && selectedTime" x-transition.opacity style="display: none;">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">event_available</span>
                            <span>Seçilen Randevu: <span class="font-bold"
                                    x-text="formatDate(selectedDate) + ' - Saat ' + selectedTime"></span></span>
                        </div>
                        <button type="button" @click="selectedDate = ''; selectedTime = ''; activeSlotKey = ''"
                            class="text-[10px] underline hover:opacity-85">Temizle</button>
                    </div>

                    {{-- Hidden Inputs for Form Submission --}}
                    <input type="hidden" name="appointment_date" :value="selectedDate" required>
                    <input type="hidden" name="appointment_time" :value="selectedTime" required>
                    <input type="hidden" name="estimated_price" id="estimatedPriceInput" value="0">

                    <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-5 border border-[#EAD5DE]/60 shadow-sm">
                        <!-- Month / Year with arrows inside calendar box -->
                        <div class="relative flex items-center justify-center mb-5">
                            <button type="button" @click="prevMonth()" x-show="shouldShowPrevArrow()"
                                class="absolute left-0 p-1.5 rounded-full bg-[#F4EDF0] hover:bg-[#EAD5DE] text-[#3B2030] transition-colors flex items-center justify-center w-8 h-8 z-10 shadow-sm">
                                <span class="material-symbols-outlined text-sm">chevron_left</span>
                            </button>

                            <div class="text-[16px] font-bold text-[#3B2030]" style="font-family:'Playfair Display',serif;"
                                x-text="monthName"></div>

                            <button type="button" @click="nextMonth()" x-show="shouldShowNextArrow()"
                                class="absolute right-0 p-1.5 rounded-full bg-[#F4EDF0] hover:bg-[#EAD5DE] text-[#3B2030] transition-colors flex items-center justify-center w-8 h-8 z-10 shadow-sm">
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                            </button>
                        </div>

                        <!-- Days Header -->
                        <div class="grid grid-cols-7 gap-1 text-center mb-3">
                            <div class="font-bold text-[10px] text-[#B496A1]">Pt</div>
                            <div class="font-bold text-[10px] text-[#B496A1]">Sa</div>
                            <div class="font-bold text-[10px] text-[#B496A1]">Ça</div>
                            <div class="font-bold text-[10px] text-[#B496A1]">Pe</div>
                            <div class="font-bold text-[10px] text-[#B496A1]">Cu</div>
                            <div class="font-bold text-[10px] text-[#B496A1]">Ct</div>
                            <div class="font-bold text-[10px] text-[#B496A1]">Pz</div>
                        </div>

                        <!-- Calendar Grid -->
                        <div class="grid grid-cols-7 gap-1 text-center">
                            <template x-for="day in daysInGrid" :key="day.dateStr">
                                <div @click="selectDay(day)" :class="{
                                                'text-[#C4A0B4]/40 cursor-not-allowed': !day.isSelectable,
                                                'rounded-full bg-red-50 text-red-300 border border-red-100 line-through cursor-not-allowed': day.isSelectable && isDayFullyBooked(day.dateStr),
                                                'rounded-full hover:bg-[#F4EDF0] cursor-pointer transition-all text-[#3B2030]': day.isSelectable && !isDayFullyBooked(day.dateStr) && selectedDate !== day.dateStr,
                                                'rounded-full bg-[#95687A] text-white shadow-[0_4px_12px_rgba(149,104,122,0.4)] cursor-pointer font-bold': day.isSelectable && !isDayFullyBooked(day.dateStr) && selectedDate === day.dateStr
                                            }"
                                    class="py-1.5 relative select-none flex items-center justify-center w-9 h-9 mx-auto transition-all text-[13px]">
                                    <span x-text="day.dayNum"></span>
                                    <template x-if="day.hasDot">
                                        <span
                                            class="absolute bottom-0.5 left-1/2 transform -translate-x-1/2 w-1 h-1 rounded-full"
                                            :class="selectedDate === day.dateStr ? 'bg-white/80' : 'bg-[#95687A]'"></span>
                                    </template>
                                </div>
                            </template>
                        </div>

                        {{-- Time Slots --}}
                        <div class="mt-5 border-t border-[#EAD5DE]/40 pt-4" x-show="selectedDate">
                            <div class="font-bold text-[10px] text-[#B496A1] mb-3 tracking-widest uppercase"
                                x-text="formatFriendlySelectedDate() + ' TARİHİ İÇİN UYGUN SAATLER'"></div>

                            <div class="flex overflow-x-auto no-scrollbar gap-2 pb-2">
                                <template x-for="slot in getAvailableSlotsForSelectedDate()" :key="slot.key">
                                    <button type="button"
                                        @click="if (slot.isAvailable) { selectedTime = slot.hour; activeSlotKey = slot.key; }"
                                        :disabled="!slot.isAvailable" :class="{
                                                    'bg-[#F4EDF0]/30 text-[#C4A0B4]/40 border border-[#EAD5DE]/20 cursor-not-allowed': !slot.isAvailable,
                                                    'border border-[#EAD5DE] text-[#957582] hover:bg-[#F4EDF0] hover:border-[#C4A0B4] transition-all': slot.isAvailable && selectedTime !== slot.hour,
                                                    'bg-[#95687A] text-white border border-[#95687A] shadow-[0_4px_12px_rgba(149,104,122,0.3)] font-bold': slot.isAvailable && selectedTime === slot.hour
                                                }"
                                        class="flex-none px-4 py-2 rounded-full transition-all text-[12px] whitespace-nowrap">
                                        <span x-text="formatTimeLabel(slot.hour)"></span>
                                    </button>
                                </template>

                                <template x-if="getAvailableSlotsForSelectedDate().filter(s => s.isAvailable).length === 0">
                                    <div class="text-[12px] text-[#B496A1] italic py-1">Bu tarihte uygun randevu saati bulunmuyor.</div>
                                </template>
                            </div>
                        </div>

                        {{-- Legend --}}
                        <div class="flex justify-center gap-4 text-[10px] text-[#B496A1] pt-3 border-t border-[#EAD5DE]/40 mt-4">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#EAD5DE] inline-block"></span>
                                <span>Müsait</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#95687A] inline-block"></span>
                                <span>Seçili</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-gray-200 inline-block"></span>
                                <span>Dolu</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-3">
                    <button type="submit" id="submitBtn"
                        class="w-full bg-gradient-to-r from-[#95687A] to-[#7B4F5F] text-white font-bold py-4 rounded-2xl shadow-[0_8px_24px_rgba(149,104,122,0.35)] hover:shadow-[0_12px_28px_rgba(149,104,122,0.45)] hover:from-[#8A5F70] hover:to-[#6E4555] transition-all duration-300 flex justify-center items-center gap-2 text-[13px] tracking-[0.12em] uppercase">
                        Randevu Talep Et
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </button>
                    <p class="text-center text-[11px] text-[#B496A1] mt-2.5 flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">attach_money</span>
                        Ödeme nakit alınmaktadır.
                    </p>
                </div>
            </form>
        </section>
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Alpine.js and Collapse Plugin --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('galleryManager', (config) => ({
                images: config.images || [],
                showModal: false,
                showLightbox: false,
                activeIdx: 0,

                touchStartX: 0,
                touchEndX: 0,

                openGallery() {
                    if (this.images.length === 0) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Portföy Boş',
                            text: 'Henüz yüklenmiş bir portföy görseli bulunmuyor.',
                            confirmButtonColor: '#7b5068'
                        });
                        return;
                    }
                    this.activeIdx = 0;
                    this.showModal = true;
                },

                closeGallery() {
                    this.showModal = false;
                },

                openLightbox(index) {
                    if (index >= this.images.length) return;
                    this.activeIdx = index;
                    this.showLightbox = true;
                },

                closeLightbox() {
                    this.showLightbox = false;
                },

                nextImage() {
                    this.activeIdx = (this.activeIdx + 1) % this.images.length;
                },

                prevImage() {
                    this.activeIdx = (this.activeIdx - 1 + this.images.length) % this.images.length;
                },

                handleTouchStart(e) {
                    this.touchStartX = e.changedTouches[0].screenX;
                },

                handleTouchEnd(e) {
                    this.touchEndX = e.changedTouches[0].screenX;
                    this.handleSwipe();
                },

                handleSwipe() {
                    const diff = this.touchEndX - this.touchStartX;
                    if (diff > 50) {
                        this.prevImage();
                    } else if (diff < -50) {
                        this.nextImage();
                    }
                }
            }));

            Alpine.data('bookingCalendar', (config) => ({
                blockedSlots: config.blockedSlots || {},
                occupiedSlots: config.occupiedSlots || {},
                hours: config.hours || [],
                todayStr: config.todayStr,

                selectedDate: '',
                selectedTime: '',
                activeSlotKey: '',
                serviceType: 'yapim',
                aiPriceLoaded: false,

                // Base prices from nail tech profile (jel & kalici aynı protez fiyatını kullanır)
                basePrices: {
                    yapim: {{ intval($baseProthezPrice) }},
                    yapim_jel: {{ intval($baseProthezPrice) }},
                    yapim_kalici: {{ intval($baseProthezPrice) }},
                    cikarma: {{ intval($baseCikarmaPrice) }}
                },
                basePriceDisplay: {{ intval($baseProthezPrice) }},

                updateBasePrice() {
                    this.basePriceDisplay = this.basePrices[this.serviceType] || 0;
                    // Reset AI price when sub-type changes
                    this.aiPriceLoaded = false;
                    const estInput = document.getElementById('estimatedPriceInput');
                    if (estInput) estInput.value = this.basePriceDisplay;
                    window.nihaiJP = this.basePriceDisplay;
                    if (window.updatePriceDisplay) window.updatePriceDisplay();
                },

                currentYear: null,
                currentMonth: null,
                monthName: '',
                daysInGrid: [],

                init() {
                    const today = new Date(this.todayStr);
                    this.currentYear = today.getFullYear();
                    this.currentMonth = today.getMonth();
                    this.generateGrid();
                },

                generateGrid() {
                    const firstDayOfMonth = new Date(this.currentYear, this.currentMonth, 1);
                    const lastDayOfMonth = new Date(this.currentYear, this.currentMonth + 1, 0);

                    let m = firstDayOfMonth.toLocaleDateString('tr-TR', { month: 'long', year: 'numeric' });
                    this.monthName = m.charAt(0).toUpperCase() + m.slice(1);

                    const days = [];

                    const startDayOfWeek = firstDayOfMonth.getDay();
                    const prevMonthLastDay = new Date(this.currentYear, this.currentMonth, 0).getDate();
                    for (let i = startDayOfWeek - 1; i >= 0; i--) {
                        const d = prevMonthLastDay - i;
                        let pm = this.currentMonth - 1;
                        let py = this.currentYear;
                        if (pm < 0) { pm = 11; py--; }
                        const dateStr = `${py}-${String(pm + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                        days.push({
                            dateStr: dateStr,
                            dayNum: d,
                            isCurrentMonth: false,
                            isSelectable: false,
                            hasDot: false
                        });
                    }

                    const today = new Date(this.todayStr);
                    const maxAllowedDate = new Date(today);
                    maxAllowedDate.setDate(today.getDate() + 27);

                    const totalDays = lastDayOfMonth.getDate();
                    for (let d = 1; d <= totalDays; d++) {
                        const dateStr = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                        const dateObj = new Date(this.currentYear, this.currentMonth, d);

                        const isAfterOrEqualToday = dateObj >= new Date(today.getFullYear(), today.getMonth(), today.getDate());
                        const isBeforeOrEqualMax = dateObj <= new Date(maxAllowedDate.getFullYear(), maxAllowedDate.getMonth(), maxAllowedDate.getDate());
                        let isSelectable = isAfterOrEqualToday && isBeforeOrEqualMax;

                        const isTodayDate = dateStr === this.todayStr;

                        days.push({
                            dateStr: dateStr,
                            dayNum: d,
                            isCurrentMonth: true,
                            isSelectable: isSelectable,
                            hasDot: isTodayDate
                        });
                    }

                    const totalCells = Math.ceil(days.length / 7) * 7;
                    const leadingDaysNeeded = totalCells - days.length;
                    for (let d = 1; d <= leadingDaysNeeded; d++) {
                        let nm = this.currentMonth + 1;
                        let ny = this.currentYear;
                        if (nm > 11) { nm = 0; ny++; }
                        const dateStr = `${ny}-${String(nm + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                        days.push({
                            dateStr: dateStr,
                            dayNum: d,
                            isCurrentMonth: false,
                            isSelectable: false,
                            hasDot: false
                        });
                    }

                    this.daysInGrid = days;
                },

                prevMonth() {
                    const today = new Date(this.todayStr);
                    const viewDate = new Date(this.currentYear, this.currentMonth, 1);
                    const limitDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    if (viewDate > limitDate) {
                        this.currentMonth--;
                        if (this.currentMonth < 0) {
                            this.currentMonth = 11;
                            this.currentYear--;
                        }
                        this.generateGrid();
                    }
                },

                nextMonth() {
                    const today = new Date(this.todayStr);
                    const viewDate = new Date(this.currentYear, this.currentMonth, 1);
                    const maxDate = new Date(today);
                    maxDate.setDate(today.getDate() + 27);
                    const limitDate = new Date(maxDate.getFullYear(), maxDate.getMonth(), 1);
                    if (viewDate < limitDate) {
                        this.currentMonth++;
                        if (this.currentMonth > 11) {
                            this.currentMonth = 0;
                            this.currentYear++;
                        }
                        this.generateGrid();
                    }
                },

                shouldShowPrevArrow() {
                    const today = new Date(this.todayStr);
                    return this.currentYear !== today.getFullYear() || this.currentMonth !== today.getMonth();
                },

                shouldShowNextArrow() {
                    const today = new Date(this.todayStr);
                    const lastDayOfTodayMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0).getDate();
                    const isTodayInLastWeek = today.getDate() >= (lastDayOfTodayMonth - 6);
                    const isCurrentlyShowingTodayMonth = this.currentYear === today.getFullYear() && this.currentMonth === today.getMonth();
                    return isTodayInLastWeek && isCurrentlyShowingTodayMonth;
                },

                selectDay(day) {
                    if (!day.isSelectable || this.isDayFullyBooked(day.dateStr)) return;
                    this.selectedDate = day.dateStr;
                    this.selectedTime = '';
                    this.activeSlotKey = '';
                },

                isDayFullyBooked(dateStr) {
                    if (!dateStr) return false;
                    if (this.hours.length === 0) return false;

                    const isTodaySelected = dateStr === this.todayStr;
                    const now = new Date();

                    return this.hours.every(hour => {
                        const key = `${dateStr}_${hour}`;
                        const isBlocked = !!this.blockedSlots[key];
                        const isOccupied = !!this.occupiedSlots[key];

                        let isPast = false;
                        if (isTodaySelected) {
                            const parts = dateStr.split('-');
                            const slotTime = new Date(parts[0], parts[1] - 1, parts[2]);
                            const [h, m] = hour.split(':');
                            slotTime.setHours(parseInt(h), parseInt(m), 0, 0);
                            if (slotTime < now) {
                                isPast = true;
                            }
                        }

                        return isBlocked || isOccupied || isPast;
                    });
                },

                getAvailableSlotsForSelectedDate() {
                    if (!this.selectedDate) return [];

                    const slots = [];
                    const isTodaySelected = this.selectedDate === this.todayStr;
                    const now = new Date();

                    this.hours.forEach(hour => {
                        const key = `${this.selectedDate}_${hour}`;
                        const isBlocked = !!this.blockedSlots[key];
                        const isOccupied = !!this.occupiedSlots[key];

                        let isPast = false;
                        if (isTodaySelected) {
                            const parts = this.selectedDate.split('-');
                            const slotTime = new Date(parts[0], parts[1] - 1, parts[2]);
                            const [h, m] = hour.split(':');
                            slotTime.setHours(parseInt(h), parseInt(m), 0, 0);
                            if (slotTime < now) {

                                isPast = true;
                            }
                        }

                        slots.push({
                            hour: hour,
                            key: key,
                            isAvailable: !isBlocked && !isOccupied && !isPast
                        });
                    });

                    return slots;
                },

                formatFriendlySelectedDate() {
                    if (!this.selectedDate) return '';
                    const parts = this.selectedDate.split('-');
                    const date = new Date(parts[0], parts[1] - 1, parts[2]);
                    return date.toLocaleDateString('tr-TR', { day: 'numeric', month: 'short' }).toUpperCase();
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const parts = dateStr.split('-');
                    const date = new Date(parts[0], parts[1] - 1, parts[2]);
                    return date.toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', weekday: 'long' });
                },

                formatTimeLabel(hourStr) {
                    return hourStr.substring(0, 5);
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('fileInput');
            const uploadPlaceholder = document.getElementById('uploadPlaceholder');
            const imagePreview = document.getElementById('imagePreview');
            const priceSection = document.getElementById('priceEstimationSection');
            const priceSpinner = document.getElementById('priceSpinner');
            const priceTitle = document.getElementById('priceTitle');
            const priceDesc = document.getElementById('priceDesc');
            const dropzone = document.getElementById('dropzone');


            // Drag and drop styles
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, unhighlight, false);
            });

            function highlight(e) {
                dropzone.classList.add('border-primary', 'bg-surface-container-high');
            }

            function unhighlight(e) {
                dropzone.classList.remove('border-primary', 'bg-surface-container-high');
            }

            const viewPriceBtnContainer = document.getElementById('viewPriceBtnContainer');
            const viewPriceBtn = document.getElementById('viewPriceBtn');
            const selectorContainer = document.getElementById('serviceSelectorContainer');

            // Handle file selection
            fileInput.addEventListener('change', function (e) {
                if (this.files && this.files[0]) {
                    const file = this.files[0];

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        imagePreview.src = e.target.result;
                        imagePreview.classList.remove('hidden');
                        uploadPlaceholder.classList.add('hidden');
                    }
                    reader.readAsDataURL(file);

                    // "Tahmini Fiyatı Gör" butonunu göster
                    if (viewPriceBtnContainer) viewPriceBtnContainer.classList.remove('hidden');
                    
                    // Fiyat kutusunu gizle
                    if (priceSection) priceSection.classList.add('hidden');
                    
                    // Önceki fiyattan kalma div'i gizle
                    if (selectorContainer) selectorContainer.classList.add('hidden');

                    // Trigger AI Price Simulation in background
                    simulateAIPrice(file);
                }
            });

            if (viewPriceBtn) {
                viewPriceBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (viewPriceBtnContainer) viewPriceBtnContainer.classList.add('hidden');
                    if (priceSection) {
                        priceSection.classList.remove('hidden');
                        priceSection.classList.add('animate-in', 'fade-in', 'slide-in-from-bottom-2');
                    }
                });
            }

            // Initialize estimated price with base protez price
            window.nihaiJP = 0;
            (function() {
                const estInput = document.getElementById('estimatedPriceInput');
                if (estInput && estInput.value === '0') {
                    estInput.value = {{ intval($baseProthezPrice) }};
                    window.nihaiJP = {{ intval($baseProthezPrice) }};
                }
            })();

            window.updatePriceDisplay = function () {
                const totalPriceEl = document.getElementById('singleTotalPrice');
                const estPriceInput = document.getElementById('estimatedPriceInput');
                if (totalPriceEl) totalPriceEl.innerText = `₺${window.nihaiJP}`;
                if (estPriceInput) estPriceInput.value = window.nihaiJP;
            };

            function simulateAIPrice(file) {
                const priceBreakdown = document.getElementById('priceBreakdown');
                
                // Arka planda başlarken yazıları resetle
                priceSpinner.innerHTML = '<span class="material-symbols-outlined text-primary animate-spin">progress_activity</span>';
                priceTitle.innerText = 'Fiyat Oluşturuluyor...';
                priceTitle.className = 'font-body-md font-semibold text-primary';
                priceDesc.classList.add('hidden');
                if (priceBreakdown) priceBreakdown.classList.add('hidden');

                const formData = new FormData();
                formData.append('design_image', file);

                const nailTechInput = document.querySelector('input[name="nail_tech_id"]');
                if (nailTechInput) {
                    formData.append('nail_tech_id', nailTechInput.value);
                }

                const csrfToken = document.querySelector('input[name="_token"]').value;

                fetch('{{ route("tirnak.hesapla") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Spinner güncelle
                            priceSpinner.innerHTML = '<span class="material-symbols-outlined text-green-600 dark:text-green-400">check_circle</span>';
                            priceTitle.className = 'fiyat-gosterim font-body-md font-semibold text-green-600 dark:text-green-400';
                            priceTitle.innerText = 'Fiyat Oluşturuldu! (yapayzeka yanlış sonuç verebilir)';

                            window.nihaiJP = data.nihai_jp;

                            if (selectorContainer) {
                                selectorContainer.classList.remove('hidden');
                            }

                            window.updatePriceDisplay();

                            // Alpine'a AI fiyatı yüklendi sinyali ver (booking section'u hedefle)
                            const bookingSection = document.getElementById('appointmentForm');
                            if (bookingSection) {
                                const sectionEl = bookingSection.closest('[x-data]');
                                if (sectionEl && sectionEl._x_dataStack) {
                                    const alpineData = sectionEl._x_dataStack.find(d => 'aiPriceLoaded' in d);
                                    if (alpineData) alpineData.aiPriceLoaded = true;
                                }
                            }

                            // Açıklama metnini gizle
                            priceDesc.classList.add('hidden');
                        } else {
                            // Sadece kullanıcı dostu mesajı göster, debug_error'ı sadece konsola yaz
                            if (data.debug_error) console.error("Backend Error:", data.debug_error);
                            throw new Error(data.message || 'Analiz sırasında bir hata oluştu.');
                        }
                    })
                    .catch(error => {
                        console.error("===== HATA DETAYI =====", error.message || error);
                        priceSpinner.innerHTML = '<span class="material-symbols-outlined text-amber-500">warning</span>';
                        priceTitle.className = 'font-body-md font-semibold text-amber-600';
                        priceTitle.innerText = error.message || 'Yapay zeka şuanda yanıt vermiyor.';
                        
                        priceDesc.innerText = 'Çok fazla istek attıysanız veya sistem yoğunsa lütfen birkaç dakika sonra tekrar deneyin.';
                        priceDesc.classList.remove('hidden');

                        const priceBreakdownEl = document.getElementById('priceBreakdown');
                        if (priceBreakdownEl) priceBreakdownEl.classList.add('hidden');
                    });
            }

            // Form Validation for selectedDate and selectedTime
            const appointmentForm = document.getElementById('appointmentForm');
            if (appointmentForm) {
                appointmentForm.addEventListener('submit', function (e) {
                    const dateInput = appointmentForm.querySelector('input[name="appointment_date"]');
                    const timeInput = appointmentForm.querySelector('input[name="appointment_time"]');
                    if (!dateInput.value || !timeInput.value) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Randevu Saati Seçin',
                            text: 'Lütfen takvimden uygun bir gün ve saat seçin.',
                            confirmButtonColor: '#7b5068'
                        });
                        return;
                    }


                    // Immediately show loading popup in the middle of the screen
                    Swal.fire({
                        title: 'Randevu Talebiniz Gönderiliyor',
                        text: 'Lütfen bekleyin...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                });
            }
        });
    </script>
@endpush