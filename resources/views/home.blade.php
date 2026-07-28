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

        $hours = $nailTech->work_hours ?? ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];

        $nailTechPrices = $nailTech ? $nailTech->userPrices
            ->mapWithKeys(function ($userPrice) {
                return [$userPrice->serviceCategory->name => $userPrice->price];
            })->toArray() : [];

        $baseProthezPrice = $nailTechPrices['Jel Protez'] ?? $nailTechPrices['Protez Tırnak'] ?? 0;
        $baseCikarmaPrice = $nailTechPrices['Çıkarma'] ?? 0;
    @endphp

    {{-- ── SAYFA WRAPPER ── --}}
    <div class="min-h-screen bg-[#FDFBFB] pb-24 text-slate-800 font-sans selection:bg-rose-200"
         x-data="galleryManager({ images: {{ json_encode($uploadedImages) }} })">

        <main class="px-5 pt-6 pb-8 space-y-8 max-w-lg mx-auto">

            {{-- ── Profil Kartı ── --}}
            <section class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.03)] border border-[#F2EAEB] flex flex-col items-center text-center transition-transform hover:scale-[1.01]">
                @if($nailTech && $nailTech->profile_photo_path)
                    <div class="w-20 h-20 rounded-full overflow-hidden shrink-0 border-2 border-[#EAE1E3] p-0.5 mb-3">
                        <img src="{{ str_starts_with($nailTech->profile_photo_path, 'http') ? $nailTech->profile_photo_path : asset('storage/' . $nailTech->profile_photo_path) }}"
                             alt="Profile" class="w-full h-full object-cover rounded-full">
                    </div>
                @endif
                <div>
                    <h2 class="text-lg font-medium text-slate-700">{{ $nailTech->name ?? 'NailwMelis' }}</h2>
                    @if($nailTech && $nailTech->bio)
                        <p class="text-sm text-slate-500 leading-relaxed mt-1">{{ str_replace(["\r", "\n"], ' ', $nailTech->bio) }}</p>
                    @else
                        <p class="text-sm text-slate-500 leading-relaxed mt-1">Güzellik ve zarafetin buluştuğu nokta. Randevunuzu aşağıdan kolayca oluşturabilirsiniz.</p>
                    @endif
                </div>
            </section>

            {{-- ── Portföy ── --}}
            @if($nailTech && ($nailTech->show_portfolio ?? true) && count($uploadedImages) > 0)
            <section class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.03)] border border-[#F2EAEB] space-y-4">
                <h3 class="font-medium text-slate-700 border-b border-[#F2EAEB] pb-3">Portföy</h3>
                <div class="grid grid-cols-2 gap-3 auto-rows-[160px]">
                    @if($nailTech->portfolio_image_1)
                    <div class="rounded-2xl overflow-hidden shadow-sm row-span-2 col-span-1 relative group cursor-pointer">
                        <img alt="Nail Art 1" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                             src="{{ str_starts_with($nailTech->portfolio_image_1, 'http') ? $nailTech->portfolio_image_1 : asset('storage/' . $nailTech->portfolio_image_1) }}" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3" @click="openLightbox(0)">
                            <button type="button" class="w-full py-2 bg-white/90 backdrop-blur-sm rounded-xl text-[11px] font-semibold text-slate-700 hover:bg-white transition-colors">Seç</button>
                        </div>
                    </div>
                    @endif
                    @if($nailTech->portfolio_image_2)
                    <div class="rounded-2xl overflow-hidden shadow-sm col-span-1 relative group cursor-pointer">
                        <img alt="Nail Art 2" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                             src="{{ str_starts_with($nailTech->portfolio_image_2, 'http') ? $nailTech->portfolio_image_2 : asset('storage/' . $nailTech->portfolio_image_2) }}" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-2" @click="openLightbox(1)">
                            <button type="button" class="w-full py-1.5 bg-white/90 backdrop-blur-sm rounded-xl text-[10px] font-semibold text-slate-700 hover:bg-white transition-colors">Seç</button>
                        </div>
                    </div>
                    @endif
                    @if($nailTech->portfolio_image_3)
                    <div class="rounded-2xl overflow-hidden shadow-sm col-span-1 relative group cursor-pointer">
                        <img alt="Nail Art 3" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                             src="{{ str_starts_with($nailTech->portfolio_image_3, 'http') ? $nailTech->portfolio_image_3 : asset('storage/' . $nailTech->portfolio_image_3) }}" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-2" @click="openLightbox(2)">
                            <button type="button" class="w-full py-1.5 bg-white/90 backdrop-blur-sm rounded-xl text-[10px] font-semibold text-slate-700 hover:bg-white transition-colors">Seç</button>
                        </div>
                    </div>
                    @endif
                </div>
                <button type="button" @click="openGallery()"
                    class="w-full py-3 border border-[#F2EAEB] hover:border-[#D2B6BD]/50 rounded-2xl text-sm font-medium text-slate-500 hover:text-slate-700 transition-colors mt-2">
                    Tüm Galeriyi Gör
                </button>
            </section>

            {{-- Gallery Modal --}}
            <div x-cloak x-show="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-transition.opacity>
                <div class="bg-white rounded-3xl w-full max-w-[450px] h-[520px] flex flex-col p-6 shadow-2xl border border-[#F2EAEB] relative" @click.away="closeGallery()">
                    <button @click="closeGallery()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-1 rounded-full bg-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </button>
                    <h3 class="font-semibold text-slate-700 text-lg mb-4">Portföy Galerisi</h3>
                    <template x-if="images.length === 0">
                        <div class="flex-1 flex flex-col items-center justify-center text-slate-400">
                            <p class="text-sm">Henüz portföy görseli eklenmemiş.</p>
                        </div>
                    </template>
                    <template x-if="images.length > 0">
                        <div class="flex-1 flex flex-col gap-4 min-h-0">
                            <div class="flex-1 relative rounded-2xl overflow-hidden bg-slate-50 flex items-center justify-center border border-[#F2EAEB] cursor-pointer group" @click="openLightbox(activeIdx)">
                                <img :src="images[activeIdx]" class="w-full h-full object-cover" />
                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="M11 8v6M8 11h6"/></svg>
                                </div>
                            </div>
                            <div class="flex gap-3 overflow-x-auto py-1 no-scrollbar border-t border-[#F2EAEB] shrink-0">
                                <template x-for="(img, idx) in images" :key="idx">
                                    <div class="w-20 h-20 shrink-0 rounded-xl overflow-hidden border-2 cursor-pointer transition-all"
                                        :class="activeIdx === idx ? 'border-[#D2B6BD] scale-95 shadow-sm' : 'border-transparent hover:opacity-80'"
                                        @click="activeIdx = idx">
                                        <img :src="img" class="w-full h-full object-cover" />
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Lightbox --}}
            <div x-cloak x-show="showLightbox" class="fixed inset-0 z-[200] bg-black/95 flex flex-col items-center justify-center select-none"
                x-transition.opacity @keydown.escape.window="closeLightbox()" @keydown.arrow-left.window="prevImage()" @keydown.arrow-right.window="nextImage()">
                <button @click="closeLightbox()" class="absolute top-6 right-6 text-white/80 hover:text-white p-2 rounded-full hover:bg-white/10 z-[210]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
                <div class="w-full max-w-4xl max-h-[80vh] flex items-center justify-center p-4 relative" @touchstart="handleTouchStart($event)" @touchend="handleTouchEnd($event)">
                    <img :src="images[activeIdx]" class="max-w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl transition-all duration-300" />
                    <div class="absolute bottom-[-40px] text-white/70 text-xs tracking-widest" x-text="(activeIdx + 1) + ' / ' + images.length"></div>
                </div>
            </div>
            @endif

            {{-- ── RANDEVU FORMU ── --}}
            <div x-data="bookingCalendar({
                    blockedSlots: {{ json_encode($blockedSlots) }},
                    occupiedSlots: {{ json_encode($occupiedSlots) }},
                    hours: {{ json_encode($hours) }},
                    todayStr: '{{ today()->toDateString() }}'
                })">

                <form action="{{ route('appointment.store') }}" method="POST" enctype="multipart/form-data" id="appointmentForm" class="space-y-8">
                    @csrf
                    <input type="hidden" name="nail_tech_id" value="{{ $nailTech->id ?? 1 }}">

                    {{-- İşlem Türü --}}
                    <section class="space-y-4">
                        <h3 class="text-sm font-semibold tracking-wider text-slate-400 uppercase ml-1">İşlem Türü</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button"
                                @click="serviceType = 'yapim'; updateBasePrice()"
                                :class="serviceType === 'yapim' || serviceType === 'yapim_jel' || serviceType === 'yapim_kalici'
                                    ? 'bg-[#D2B6BD] text-white border-[#D2B6BD] shadow-md shadow-[#D2B6BD]/30'
                                    : 'bg-white text-slate-600 border-[#F2EAEB] hover:border-[#D2B6BD]/50'"
                                class="py-3 px-3 rounded-2xl text-[13px] leading-relaxed font-medium transition-all duration-300 border flex items-center justify-center text-center min-h-[5rem]">
                                Protez Tırnak, Jel Güçlendirme, Kalıcı Oje
                            </button>
                            <button type="button"
                                @click="serviceType = 'cikarma'; updateBasePrice()"
                                :class="serviceType === 'cikarma'
                                    ? 'bg-[#D2B6BD] text-white border-[#D2B6BD] shadow-md shadow-[#D2B6BD]/30'
                                    : 'bg-white text-slate-600 border-[#F2EAEB] hover:border-[#D2B6BD]/50'"
                                class="py-3 px-3 rounded-2xl text-[13px] leading-relaxed font-medium transition-all duration-300 border flex items-center justify-center text-center min-h-[5rem]">
                                Protez Tırnak Çıkarma
                            </button>
                        </div>
                        <input type="hidden" name="service_type" :value="serviceType">
                    </section>

                    {{-- Base Ücret --}}
                    <section class="bg-white rounded-3xl p-5 shadow-[0_8px_30px_rgb(0,0,0,0.03)] border border-[#F2EAEB] flex justify-between items-center">
                        <span class="text-slate-500 font-medium">Base Ücret</span>
                        <span class="text-xl font-semibold text-[#B3939B]" x-text="'₺' + basePriceDisplay"></span>
                    </section>

                    {{-- Adınız Soyadınız --}}
                    <section class="space-y-2">
                        <label class="text-sm font-semibold tracking-wider text-slate-400 uppercase ml-1 block">Adınız Soyadınız</label>
                        <input type="text" name="client_name" required
                            placeholder="Adınızı giriniz..."
                            class="w-full bg-white border border-[#F2EAEB] rounded-2xl py-4 px-5 text-slate-700 placeholder:text-slate-300 focus:outline-none focus:ring-2 focus:ring-[#D2B6BD]/40 focus:border-[#D2B6BD] transition-all shadow-[0_4px_20px_rgb(0,0,0,0.02)]">
                    </section>

                    {{-- Tırnak Modeli --}}
                    <section class="space-y-3" x-show="serviceType === 'yapim' || serviceType === 'yapim_jel' || serviceType === 'yapim_kalici'" x-collapse>
                        <div class="flex items-center justify-between ml-1">
                            <h3 class="text-sm font-semibold tracking-wider text-slate-400 uppercase">Tırnak Modeli (İsteğe Bağlı)</h3>
                        </div>

                        <div id="dropzone"
                            class="relative w-full flex flex-col items-center justify-center gap-3 py-10 bg-[#FAFAFA] border-2 border-dashed border-[#EAE1E3] rounded-3xl hover:bg-white hover:border-[#D2B6BD] transition-all group cursor-pointer overflow-hidden">
                            <input type="file" name="design_image" id="fileInput"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*">
                            <div id="uploadPlaceholder" class="flex flex-col items-center gap-3 pointer-events-none transition-opacity duration-300">
                                <div class="w-12 h-12 rounded-full bg-[#F3ECEF] flex items-center justify-center group-hover:bg-[#EAE1E3] transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#B3939B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>
                                    </svg>
                                </div>
                                <div class="text-center">
                                    <span class="block text-sm font-medium text-slate-600">Fotoğraf Yükle</span>
                                    <span class="block text-xs text-slate-400 mt-1">JPG, PNG veya WEBP (Maks. 5MB)</span>
                                </div>
                            </div>
                            <img id="imagePreview" class="absolute inset-0 w-full h-full object-cover hidden" alt="Preview">
                        </div>

                        <p class="text-xs text-slate-400 text-center px-4 leading-relaxed">
                            İstediğiniz modelin fotoğrafını yükleyerek işleminizi hızlandırabilirsiniz.
                        </p>

                        <div id="viewPriceBtnContainer" class="hidden text-center">
                            <button type="button" id="viewPriceBtn"
                                class="w-full py-3 bg-[#F3ECEF] hover:bg-[#EAE1E3] text-[#B3939B] rounded-2xl text-[12px] font-semibold transition-all flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                                YAPAY ZEKA İLE TAHMİNİ FİYAT OLUŞTUR
                            </button>
                        </div>

                        <div id="priceEstimationSection" class="fiyat-kutusu hidden bg-[#FBF5F8] rounded-2xl p-4 border border-[#F2EAEB] flex flex-col gap-3">
                            <div class="flex items-start gap-3">
                                <div id="priceSpinner" class="shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#B3939B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                </div>
                                <div class="flex-1">
                                    <div id="priceTitle" class="fiyat-gosterim text-sm font-semibold text-[#B3939B]">Fiyat Oluşturuluyor...</div>
                                    <p id="priceDesc" class="hidden text-xs text-slate-500 mt-1"></p>
                                </div>
                            </div>
                            <div id="serviceSelectorContainer" class="hidden flex flex-col gap-2 pt-2 border-t border-[#F2EAEB]">
                                <div class="flex justify-between items-center bg-white p-4 rounded-2xl border border-[#F2EAEB] shadow-sm">
                                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tahmini Toplam:</span>
                                    <span id="singleTotalPrice" class="text-2xl font-bold text-[#B3939B]">₺0</span>
                                </div>
                                <p class="text-[11px] text-slate-400 text-center italic">* Sadece tahminidir. Uzman randevu sırasında değiştirebilir.</p>
                            </div>
                        </div>
                    </section>

                    {{-- Takvim --}}
                    <section class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.03)] border border-[#F2EAEB] space-y-6">

                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-slate-700 text-lg" x-text="monthName">Takvim</h3>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D2B6BD" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                            </svg>
                        </div>

                        <input type="hidden" name="appointment_date" :value="selectedDate" required>
                        <input type="hidden" name="appointment_time" :value="selectedTime" required>
                        <input type="hidden" name="estimated_price" id="estimatedPriceInput" value="0">

                        {{-- Selected preview --}}
                        <div class="py-2 px-3 bg-[#F3ECEF] border border-[#D2B6BD]/30 rounded-2xl flex items-center justify-between text-[#B3939B] text-xs font-medium"
                            x-show="selectedDate && selectedTime" x-transition.opacity style="display: none;">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="m9 16 2 2 4-4"/></svg>
                                <span>Seçilen: <span class="font-bold" x-text="formatDate(selectedDate) + ' — ' + selectedTime"></span></span>
                            </div>
                            <button type="button" @click="selectedDate = ''; selectedTime = ''; activeSlotKey = ''" class="text-[10px] underline hover:opacity-75">Temizle</button>
                        </div>

                        <div>
                            {{-- Day headers --}}
                            <div class="grid grid-cols-7 text-center mb-4">
                                @foreach(['Pt', 'Sa', 'Ça', 'Pe', 'Cu', 'Ct', 'Pz'] as $day)
                                    <div class="text-xs font-semibold text-slate-400">{{ $day }}</div>
                                @endforeach
                            </div>

                            {{-- Month nav --}}
                            <div class="relative flex items-center justify-center mb-4">
                                <button type="button" @click="prevMonth()" x-show="shouldShowPrevArrow()"
                                    class="absolute left-0 w-8 h-8 flex items-center justify-center rounded-full hover:bg-[#F3ECEF] text-slate-400 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                </button>
                                <span class="text-sm font-semibold text-slate-600" x-text="monthName"></span>
                                <button type="button" @click="nextMonth()" x-show="shouldShowNextArrow()"
                                    class="absolute right-0 w-8 h-8 flex items-center justify-center rounded-full hover:bg-[#F3ECEF] text-slate-400 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                                </button>
                            </div>

                            {{-- Calendar grid --}}
                            <div class="grid grid-cols-7 gap-y-3 gap-x-1">
                                <template x-for="day in daysInGrid" :key="day.dateStr">
                                    <button type="button"
                                        @click="selectDay(day)"
                                        :disabled="!day.isSelectable || isDayFullyBooked(day.dateStr)"
                                        :class="{
                                            'bg-[#B3939B] text-white font-semibold shadow-md shadow-[#B3939B]/30 scale-105': day.isSelectable && selectedDate === day.dateStr,
                                            'text-slate-300 cursor-not-allowed': !day.isSelectable,
                                            'bg-[#F8F9FA] text-slate-400 line-through decoration-slate-300 cursor-not-allowed': day.isSelectable && isDayFullyBooked(day.dateStr) && selectedDate !== day.dateStr,
                                            'text-slate-600 hover:bg-[#F3ECEF] font-medium': day.isSelectable && !isDayFullyBooked(day.dateStr) && selectedDate !== day.dateStr
                                        }"
                                        class="h-10 w-10 mx-auto rounded-full flex items-center justify-center text-sm transition-all">
                                        <span x-text="day.dayNum"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Time slots --}}
                        <div class="border-t border-slate-100 pt-4" x-show="selectedDate" style="display:none;">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3"
                               x-text="formatFriendlySelectedDate() + ' TARİHİ İÇİN UYGUN SAATLER'"></p>
                            <div class="flex overflow-x-auto no-scrollbar gap-2 pb-1">
                                <template x-for="slot in getAvailableSlotsForSelectedDate()" :key="slot.key">
                                    <button type="button"
                                        @click="if (slot.isAvailable) { selectedTime = slot.hour; activeSlotKey = slot.key; }"
                                        :disabled="!slot.isAvailable"
                                        :class="{
                                            'bg-[#B3939B] text-white border-[#B3939B] shadow-md shadow-[#B3939B]/30 font-semibold': slot.isAvailable && selectedTime === slot.hour,
                                            'text-slate-300 border-slate-100 cursor-not-allowed opacity-50': !slot.isAvailable,
                                            'text-slate-600 border-[#F2EAEB] hover:border-[#D2B6BD]/50 hover:bg-[#F3ECEF]': slot.isAvailable && selectedTime !== slot.hour
                                        }"
                                        class="flex-none px-4 py-2 rounded-full border text-xs font-medium transition-all whitespace-nowrap">
                                        <span x-text="formatTimeLabel(slot.hour)"></span>
                                    </button>
                                </template>
                                <template x-if="getAvailableSlotsForSelectedDate().filter(s => s.isAvailable).length === 0">
                                    <div class="text-xs text-slate-400 italic py-2">Bu tarihte uygun randevu saati bulunmuyor.</div>
                                </template>
                            </div>
                        </div>

                        {{-- Legend --}}
                        <div class="flex items-center justify-center gap-6 pt-4 border-t border-slate-100">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-slate-200"></div>
                                <span class="text-xs text-slate-500 font-medium">Müsait</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-[#B3939B] shadow-sm shadow-[#B3939B]/40"></div>
                                <span class="text-xs text-slate-500 font-medium">Seçili</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-slate-100 border border-slate-200"></div>
                                <span class="text-xs text-slate-400 font-medium line-through">Dolu</span>
                            </div>
                        </div>
                    </section>

                    {{-- Randevu Butonu --}}
                    <section class="pt-4 space-y-4">
                        <button type="submit" id="submitBtn"
                            class="w-full py-4 bg-[#B3939B] hover:bg-[#A3838B] active:scale-[0.98] text-white rounded-2xl font-semibold text-lg transition-all shadow-lg shadow-[#B3939B]/30 flex items-center justify-center gap-2">
                            RANDEVU TALEP ET
                        </button>
                        <div class="flex items-center justify-center gap-1.5 text-slate-400">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            <span class="text-xs font-medium">Ödeme nakit alınmaktadır.</span>
                        </div>
                    </section>

                </form>
            </div>

        </main>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        Swal.fire({ icon: 'info', title: 'Portföy Boş', text: 'Henüz yüklenmiş bir portföy görseli bulunmuyor.', confirmButtonColor: '#B3939B' });
                        return;
                    }
                    this.activeIdx = 0;
                    this.showModal = true;
                },
                closeGallery() { this.showModal = false; },
                openLightbox(index) {
                    if (index >= this.images.length) return;
                    this.activeIdx = index;
                    this.showLightbox = true;
                },
                closeLightbox() { this.showLightbox = false; },
                nextImage() { this.activeIdx = (this.activeIdx + 1) % this.images.length; },
                prevImage() { this.activeIdx = (this.activeIdx - 1 + this.images.length) % this.images.length; },
                handleTouchStart(e) { this.touchStartX = e.changedTouches[0].screenX; },
                handleTouchEnd(e) { this.touchEndX = e.changedTouches[0].screenX; this.handleSwipe(); },
                handleSwipe() {
                    const diff = this.touchEndX - this.touchStartX;
                    if (diff > 50) this.prevImage();
                    else if (diff < -50) this.nextImage();
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

                basePrices: {
                    yapim: {{ intval($baseProthezPrice) }},
                    yapim_jel: {{ intval($baseProthezPrice) }},
                    yapim_kalici: {{ intval($baseProthezPrice) }},
                    cikarma: {{ intval($baseCikarmaPrice) }}
                },
                basePriceDisplay: {{ intval($baseProthezPrice) }},

                updateBasePrice() {
                    this.basePriceDisplay = this.basePrices[this.serviceType] || 0;
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
                        let pm = this.currentMonth - 1, py = this.currentYear;
                        if (pm < 0) { pm = 11; py--; }
                        const dateStr = `${py}-${String(pm + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                        days.push({ dateStr, dayNum: d, isCurrentMonth: false, isSelectable: false, hasDot: false });
                    }

                    const today = new Date(this.todayStr);
                    const maxAllowedDate = new Date(today);
                    maxAllowedDate.setDate(today.getDate() + 27);

                    for (let d = 1; d <= lastDayOfMonth.getDate(); d++) {
                        const dateStr = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                        const dateObj = new Date(this.currentYear, this.currentMonth, d);
                        const isAfterOrEqualToday = dateObj >= new Date(today.getFullYear(), today.getMonth(), today.getDate());
                        const isBeforeOrEqualMax = dateObj <= new Date(maxAllowedDate.getFullYear(), maxAllowedDate.getMonth(), maxAllowedDate.getDate());
                        days.push({ dateStr, dayNum: d, isCurrentMonth: true, isSelectable: isAfterOrEqualToday && isBeforeOrEqualMax, hasDot: dateStr === this.todayStr });
                    }

                    const totalCells = Math.ceil(days.length / 7) * 7;
                    for (let d = 1; d <= totalCells - days.length; d++) {
                        let nm = this.currentMonth + 1, ny = this.currentYear;
                        if (nm > 11) { nm = 0; ny++; }
                        const dateStr = `${ny}-${String(nm + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                        days.push({ dateStr, dayNum: d, isCurrentMonth: false, isSelectable: false, hasDot: false });
                    }
                    this.daysInGrid = days;
                },

                prevMonth() {
                    const today = new Date(this.todayStr);
                    const viewDate = new Date(this.currentYear, this.currentMonth, 1);
                    const limitDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    if (viewDate > limitDate) {
                        this.currentMonth--;
                        if (this.currentMonth < 0) { this.currentMonth = 11; this.currentYear--; }
                        this.generateGrid();
                    }
                },
                nextMonth() {
                    const today = new Date(this.todayStr);
                    const maxDate = new Date(today);
                    maxDate.setDate(today.getDate() + 27);
                    const limitDate = new Date(maxDate.getFullYear(), maxDate.getMonth(), 1);
                    const viewDate = new Date(this.currentYear, this.currentMonth, 1);
                    if (viewDate < limitDate) {
                        this.currentMonth++;
                        if (this.currentMonth > 11) { this.currentMonth = 0; this.currentYear++; }
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
                    if (!dateStr || this.hours.length === 0) return false;
                    const isTodaySelected = dateStr === this.todayStr;
                    const now = new Date();
                    return this.hours.every(hour => {
                        const key = `${dateStr}_${hour}`;
                        if (!!this.blockedSlots[key] || !!this.occupiedSlots[key]) return true;
                        if (isTodaySelected) {
                            const parts = dateStr.split('-');
                            const slotTime = new Date(parts[0], parts[1] - 1, parts[2]);
                            const [h, m] = hour.split(':');
                            slotTime.setHours(parseInt(h), parseInt(m), 0, 0);
                            if (slotTime < now) return true;
                        }
                        return false;
                    });
                },
                getAvailableSlotsForSelectedDate() {
                    if (!this.selectedDate) return [];
                    const isTodaySelected = this.selectedDate === this.todayStr;
                    const now = new Date();
                    return this.hours.map(hour => {
                        const key = `${this.selectedDate}_${hour}`;
                        const isBlocked = !!this.blockedSlots[key];
                        const isOccupied = !!this.occupiedSlots[key];
                        let isPast = false;
                        if (isTodaySelected) {
                            const parts = this.selectedDate.split('-');
                            const slotTime = new Date(parts[0], parts[1] - 1, parts[2]);
                            const [h, m] = hour.split(':');
                            slotTime.setHours(parseInt(h), parseInt(m), 0, 0);
                            if (slotTime < now) isPast = true;
                        }
                        return { hour, key, isAvailable: !isBlocked && !isOccupied && !isPast };
                    });
                },
                formatFriendlySelectedDate() {
                    if (!this.selectedDate) return '';
                    const parts = this.selectedDate.split('-');
                    return new Date(parts[0], parts[1] - 1, parts[2]).toLocaleDateString('tr-TR', { day: 'numeric', month: 'short' }).toUpperCase();
                },
                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const parts = dateStr.split('-');
                    return new Date(parts[0], parts[1] - 1, parts[2]).toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', weekday: 'long' });
                },
                formatTimeLabel(hourStr) { return hourStr.substring(0, 5); }
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
            const viewPriceBtnContainer = document.getElementById('viewPriceBtnContainer');
            const viewPriceBtn = document.getElementById('viewPriceBtn');
            const selectorContainer = document.getElementById('serviceSelectorContainer');

            if (!dropzone || !fileInput) return;

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(e => dropzone.addEventListener(e, ev => { ev.preventDefault(); ev.stopPropagation(); }, false));
            ['dragenter', 'dragover'].forEach(e => dropzone.addEventListener(e, () => dropzone.classList.add('border-[#D2B6BD]', 'bg-white'), false));
            ['dragleave', 'drop'].forEach(e => dropzone.addEventListener(e, () => dropzone.classList.remove('border-[#D2B6BD]', 'bg-white'), false));

            fileInput.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        imagePreview.src = e.target.result;
                        imagePreview.classList.remove('hidden');
                        uploadPlaceholder.classList.add('hidden');
                    };
                    reader.readAsDataURL(file);
                    if (viewPriceBtnContainer) viewPriceBtnContainer.classList.remove('hidden');
                    if (priceSection) priceSection.classList.add('hidden');
                    if (selectorContainer) selectorContainer.classList.add('hidden');
                    simulateAIPrice(file);
                }
            });

            if (viewPriceBtn) {
                viewPriceBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (viewPriceBtnContainer) viewPriceBtnContainer.classList.add('hidden');
                    if (priceSection) priceSection.classList.remove('hidden');
                });
            }

            window.nihaiJP = 0;
            (function () {
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
                priceSpinner.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#B3939B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
                priceTitle.innerText = 'Fiyat Oluşturuluyor...';
                priceTitle.className = 'fiyat-gosterim text-sm font-semibold text-[#B3939B]';
                priceDesc.classList.add('hidden');

                const formData = new FormData();
                formData.append('design_image', file);
                const nailTechInput = document.querySelector('input[name="nail_tech_id"]');
                if (nailTechInput) formData.append('nail_tech_id', nailTechInput.value);
                const csrfToken = document.querySelector('input[name="_token"]').value;

                fetch('{{ route("tirnak.hesapla") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        priceSpinner.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>';
                        priceTitle.className = 'fiyat-gosterim text-sm font-semibold text-green-600';
                        priceTitle.innerText = 'Fiyat Oluşturuldu! (yapay zeka yanlış sonuç verebilir)';
                        window.nihaiJP = data.nihai_jp;
                        if (selectorContainer) selectorContainer.classList.remove('hidden');
                        window.updatePriceDisplay();
                        const bookingSection = document.getElementById('appointmentForm');
                        if (bookingSection) {
                            const sectionEl = bookingSection.closest('[x-data]');
                            if (sectionEl && sectionEl._x_dataStack) {
                                const alpineData = sectionEl._x_dataStack.find(d => 'aiPriceLoaded' in d);
                                if (alpineData) alpineData.aiPriceLoaded = true;
                            }
                        }
                        priceDesc.classList.add('hidden');
                    } else {
                        if (data.debug_error) console.error('Backend Error:', data.debug_error);
                        throw new Error(data.message || 'Analiz sırasında bir hata oluştu.');
                    }
                })
                .catch(error => {
                    console.error('===== HATA DETAYI =====', error.message || error);
                    priceSpinner.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4M12 17h.01"/></svg>';
                    priceTitle.className = 'fiyat-gosterim text-sm font-semibold text-amber-600';
                    priceTitle.innerText = error.message || 'Yapay zeka şuanda yanıt vermiyor.';
                    priceDesc.innerText = 'Çok fazla istek attıysanız veya sistem yoğunsa lütfen birkaç dakika sonra tekrar deneyin.';
                    priceDesc.classList.remove('hidden');
                });
            }

            const appointmentForm = document.getElementById('appointmentForm');
            if (appointmentForm) {
                appointmentForm.addEventListener('submit', function (e) {
                    const dateInput = appointmentForm.querySelector('input[name="appointment_date"]');
                    const timeInput = appointmentForm.querySelector('input[name="appointment_time"]');
                    if (!dateInput.value || !timeInput.value) {
                        e.preventDefault();
                        Swal.fire({ icon: 'warning', title: 'Randevu Saati Seçin', text: 'Lütfen takvimden uygun bir gün ve saat seçin.', confirmButtonColor: '#B3939B' });
                        return;
                    }
                    Swal.fire({ title: 'Randevu Talebiniz Gönderiliyor', text: 'Lütfen bekleyin...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
                });
            }
        });
    </script>
@endpush