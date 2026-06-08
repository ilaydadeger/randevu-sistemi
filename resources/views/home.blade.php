@extends('layouts.app')

@section('title', "Ana Sayfa - L'ART DE L'ONGLE")

@section('content')
    @php
        $uploadedImages = [];
        if ($nailTech && $nailTech->portfolio_image_1)
            $uploadedImages[] = asset('storage/' . $nailTech->portfolio_image_1);
        if ($nailTech && $nailTech->portfolio_image_2)
            $uploadedImages[] = asset('storage/' . $nailTech->portfolio_image_2);
        if ($nailTech && $nailTech->portfolio_image_3)
            $uploadedImages[] = asset('storage/' . $nailTech->portfolio_image_3);

        // Fetch blocked slots and occupied slots directly inside the blade
        $blockedSlots = [];
        $occupiedSlots = [];
        if ($nailTech) {
            $scheduleBlocks = $nailTech->scheduleBlocks()->get();
            foreach ($scheduleBlocks as $block) {
                $blockedSlots[$block->blocked_date . '_' . substr($block->blocked_time, 0, 5)] = true;
            }

            $appointments = $nailTech->appointments()
                ->whereIn('status', ['pending', 'approved'])
                ->where('appointment_date', '>=', today()->toDateString())
                ->get();

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

        $nailTechPrices = $nailTech ? $nailTech->userPrices()
            ->join('service_categories', 'user_prices.service_category_id', '=', 'service_categories.id')
            ->select('service_categories.name', 'user_prices.price')
            ->get()
            ->pluck('price', 'name')
            ->toArray() : [];
    @endphp

    <main class="flex-1 px-margin-mobile pt-md pb-[100px] flex flex-col gap-md max-w-[600px] mx-auto w-full"
        x-data="galleryManager({ images: {{ json_encode($uploadedImages) }} })">

        {{-- Premium Profile Header --}}
        <section
            class="bg-surface-container-lowest rounded-xl p-md border border-outline-variant/30 shadow-sm flex flex-col items-center text-center gap-sm">
            @if($nailTech && $nailTech->profile_photo_path)
                <div
                    class="relative w-32 h-32 rounded-full overflow-hidden border-2 border-surface-container-highest shadow-sm">
                    <img src="{{ asset('storage/' . $nailTech->profile_photo_path) }}"
                        alt="Uzman Profil" class="w-full h-full object-cover">
                </div>
            @endif
            <div class="space-y-xs">
                @if($nailTech && $nailTech->name)
                    <h2 class="font-headline-sm text-headline-sm text-on-surface">{{ $nailTech->name }}</h2>
                @endif

            </div>
            @if($nailTech && $nailTech->bio)
            <p class="font-body-md text-body-md text-on-surface-variant max-w-sm mt-2">
                {{ $nailTech->bio }}
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
                                src="{{ asset('storage/' . $nailTech->portfolio_image_1) }}" />
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
                                src="{{ asset('storage/' . $nailTech->portfolio_image_2) }}" />
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
                                src="{{ asset('storage/' . $nailTech->portfolio_image_3) }}" />
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
        <section class="bg-surface-container-lowest rounded-xl p-md border border-outline-variant/30 shadow-sm" x-data="bookingCalendar({
                    blockedSlots: {{ json_encode($blockedSlots) }},
                    occupiedSlots: {{ json_encode($occupiedSlots) }},
                    hours: {{ json_encode($hours) }},
                    todayStr: '{{ today()->toDateString() }}'
                })">
            <div class="mb-6 border-b border-surface-variant pb-4 flex items-center justify-between">
                <div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface">Randevu Talebi Oluştur</h3>
                </div>
            </div>

            <form action="{{ route('appointment.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
                id="appointmentForm">
                @csrf
                <input type="hidden" name="nail_tech_id" value="{{ $nailTech->id ?? 1 }}">

                {{-- Image Upload (Drag & Drop) --}}
                <div class="space-y-2">
                    <label class="font-label-caps text-label-caps text-on-surface-variant">TIRNAK MODELİ (GÖRSEL)</label>
                    <div id="dropzone"
                        class="relative w-full h-48 rounded-xl border-2 border-dashed border-outline-variant bg-surface-container hover:bg-surface-container-high hover:border-primary transition-all flex flex-col items-center justify-center cursor-pointer overflow-hidden group">

                        <input type="file" name="design_image" id="fileInput"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*">

                        <div id="uploadPlaceholder"
                            class="flex flex-col items-center pointer-events-none transition-opacity duration-300">
                            <span
                                class="material-symbols-outlined text-4xl text-outline mb-2 group-hover:text-primary transition-colors">cloud_upload</span>
                            <span class="font-body-md text-on-surface-variant font-medium">Görseli sürükleyin veya
                                seçin</span>
                            <span class="text-xs text-outline mt-1">PNG, JPG, WEBP (Max 5MB)</span>
                        </div>

                        <img id="imagePreview" class="absolute inset-0 w-full h-full object-cover hidden" alt="Preview">
                    </div>
                </div>

                {{-- AI Price Estimation Section --}}
                <div id="priceEstimationSection"
                    class="fiyat-kutusu hidden bg-primary-container/20 rounded-xl p-4 border border-primary/20 flex flex-col gap-3">
                    {{-- Loading / Status Row --}}
                    <div class="flex items-start gap-3">
                        <div id="priceSpinner" class="shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-primary animate-spin">progress_activity</span>
                        </div>
                        <div class="flex-1">
                            <div id="priceTitle" class="fiyat-gosterim font-body-md font-semibold text-primary">Fiyat Oluşturuluyor...</div>
                            <p id="priceDesc" class="hidden text-sm text-on-surface-variant mt-1"></p>
                        </div>
                    </div>

                    {{-- Price Display (Shown on success) --}}
                    <div id="serviceSelectorContainer" class="hidden flex flex-col gap-2 pt-2 border-t border-primary/10">
                        <div class="flex justify-between items-center bg-surface-container-lowest p-4 rounded-xl border border-outline-variant/20 shadow-sm">
                            <span class="text-xs font-bold text-on-surface-variant font-label-caps tracking-widest">Protez Tırnak Toplam Fiyat:</span>
                            <span id="singleTotalPrice" class="text-2xl font-black text-primary">₺0</span>
                        </div>
                    </div>
                </div>

                {{-- Client Details --}}
                <div class="space-y-xs">
                    <label class="font-label-caps text-label-caps text-on-surface-variant">AD SOYAD</label>
                    <input type="text" name="client_name" required
                        class="w-full bg-surface-container-low border-0 border-b-2 border-surface-variant focus:border-primary focus:ring-0 px-4 py-3 text-on-surface rounded-t-DEFAULT"
                        placeholder="Adınız Soyadınız">
                </div>

                {{-- Calendar Slot Picker --}}
                <div class="space-y-xs">
                    <label class="font-label-caps text-label-caps text-on-surface-variant">RANDEVU TARİHİ VE SAATİ</label>

                    {{-- Selected Slot Preview Alert --}}
                    <div class="p-sm bg-primary-container/20 border border-primary/20 rounded-xl flex items-center justify-between text-primary font-medium text-xs transition-all duration-300 my-1"
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

                    <div class="bg-surface-container-low rounded-xl p-4 border border-outline-variant/30 shadow-inner">
                        <!-- Month / Year with arrows inside calendar box -->
                        <div class="relative flex items-center justify-center mb-4">
                            <button type="button" @click="prevMonth()" x-show="shouldShowPrevArrow()"
                                class="absolute left-0 p-1 rounded-full bg-surface-container hover:bg-surface-variant transition-colors flex items-center justify-center w-7 h-7 z-10">
                                <span class="material-symbols-outlined text-sm font-bold">chevron_left</span>
                            </button>

                            <div class="font-label-caps text-label-caps text-primary tracking-widest text-center text-xs font-bold"
                                x-text="monthName"></div>

                            <button type="button" @click="nextMonth()" x-show="shouldShowNextArrow()"
                                class="absolute right-0 p-1 rounded-full bg-surface-container hover:bg-surface-variant transition-colors flex items-center justify-center w-7 h-7 z-10">
                                <span class="material-symbols-outlined text-sm font-bold">chevron_right</span>
                            </button>
                        </div>

                        <!-- Days Header -->
                        <div class="grid grid-cols-7 gap-1 text-center mb-2">
                            <div class="font-label-caps text-[10px] text-on-surface-variant">Pz</div>
                            <div class="font-label-caps text-[10px] text-on-surface-variant">Pt</div>
                            <div class="font-label-caps text-[10px] text-on-surface-variant">Sa</div>
                            <div class="font-label-caps text-[10px] text-on-surface-variant">Ça</div>
                            <div class="font-label-caps text-[10px] text-on-surface-variant">Pe</div>
                            <div class="font-label-caps text-[10px] text-on-surface-variant">Cu</div>
                            <div class="font-label-caps text-[10px] text-on-surface-variant">Ct</div>
                        </div>

                        <!-- Calendar Grid -->
                        <div class="grid grid-cols-7 gap-2 text-center font-body-md text-body-md">
                            <template x-for="day in daysInGrid" :key="day.dateStr">
                                <div @click="selectDay(day)" :class="{
                                            'text-on-surface-variant opacity-30 cursor-not-allowed': !day.isSelectable,
                                            'rounded-full bg-error/10 text-error/60 border border-error/20 line-through cursor-not-allowed': day.isSelectable && isDayFullyBooked(day.dateStr),
                                            'rounded-full hover:bg-surface-container cursor-pointer transition-colors': day.isSelectable && !isDayFullyBooked(day.dateStr) && selectedDate !== day.dateStr,
                                            'rounded-full bg-primary text-on-primary shadow-sm cursor-pointer font-semibold': day.isSelectable && !isDayFullyBooked(day.dateStr) && selectedDate === day.dateStr
                                        }"
                                    class="py-2 relative select-none flex items-center justify-center w-9 h-9 mx-auto transition-colors">
                                    <span x-text="day.dayNum"></span>
                                    <template x-if="day.hasDot">
                                        <span
                                            class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 rounded-full"
                                            :class="selectedDate === day.dateStr ? 'bg-on-primary' : 'bg-primary'"></span>
                                    </template>
                                </div>
                            </template>
                        </div>

                        {{-- Time Slots --}}
                        <div class="mt-8 border-t border-outline-variant/20 pt-4" x-show="selectedDate">
                            <div class="font-label-caps text-label-caps text-on-surface-variant mb-4 font-bold text-xs tracking-wider"
                                x-text="formatFriendlySelectedDate() + ' TARİHİ İÇİN UYGUN SAATLER'"></div>

                            <div class="flex overflow-x-auto no-scrollbar gap-sm pb-2">
                                <template x-for="slot in getAvailableSlotsForSelectedDate()" :key="slot.key">
                                    <button type="button"
                                        @click="if (slot.isAvailable) { selectedTime = slot.hour; activeSlotKey = slot.key; }"
                                        :disabled="!slot.isAvailable" :class="{
                                                'bg-surface-variant/20 text-on-surface-variant/30 border border-outline-variant/10 cursor-not-allowed opacity-50': !slot.isAvailable,
                                                'border border-outline-variant font-body-md text-body-md text-on-surface hover:bg-surface-container transition-colors': slot.isAvailable && selectedTime !== slot.hour,
                                                'bg-secondary text-on-secondary font-body-md text-body-md shadow-sm transition-colors': slot.isAvailable && selectedTime === slot.hour
                                            }"
                                        class="flex-none px-6 py-2 rounded-full transition-colors whitespace-nowrap">
                                        <span x-text="formatTimeLabel(slot.hour)"></span>
                                    </button>
                                </template>

                                <template x-if="getAvailableSlotsForSelectedDate().filter(s => s.isAvailable).length === 0">
                                    <div class="text-xs text-on-surface-variant italic py-1">Bu tarihte uygun randevu saati
                                        bulunmuyor.</div>
                                </template>
                            </div>
                        </div>

                        {{-- Legend --}}
                        <div
                            class="flex justify-center gap-4 text-[9px] md:text-[10px] text-on-surface-variant pt-2 border-t border-outline-variant/20 mt-4">
                            <div class="flex items-center gap-1">
                                <span
                                    class="w-2 h-2 rounded-full bg-surface-container border border-outline-variant/30 inline-block"></span>
                                <span>Müsait</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-primary inline-block"></span>
                                <span>Seçili</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span
                                    class="w-2 h-2 rounded-full bg-surface-variant/20 border border-outline-variant/10 inline-block"></span>
                                <span>Dolu / Kapalı</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span
                                    class="w-2.5 h-2.5 rounded bg-error/10 border border-error/20 inline-block line-through text-[7px] text-center leading-none text-error/60 font-bold">31</span>
                                <span>Tamamen Dolu / Kapalı</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" id="submitBtn"
                        class="w-full bg-gradient-to-r from-primary to-[#8a6565] text-on-primary font-label-caps text-label-caps py-4 rounded-full shadow-[0px_10px_20px_rgba(122,85,85,0.2)] hover:opacity-90 transition-opacity flex justify-center items-center gap-2">
                        RANDEVU TALEP ET
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </button>
                </div>
            </form>
        </section>
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                            confirmButtonColor: '#7a5555'
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

                    this.monthName = firstDayOfMonth.toLocaleDateString('tr-TR', { month: 'long', year: 'numeric' }).toUpperCase();

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

                    // Trigger AI Price Simulation
                    simulateAIPrice(file);
                }
            });

            window.nihaiJP = 0;

            window.updatePriceDisplay = function() {
                const totalPriceEl = document.getElementById('singleTotalPrice');
                const estPriceInput = document.getElementById('estimatedPriceInput');
                if (totalPriceEl) totalPriceEl.innerText = `₺${window.nihaiJP}`;
                if (estPriceInput) estPriceInput.value = window.nihaiJP;
            };

            function simulateAIPrice(file) {
                const priceBreakdown = document.getElementById('priceBreakdown');
                priceSection.classList.remove('hidden');
                priceSection.classList.add('animate-in', 'fade-in', 'slide-in-from-bottom-2');
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
                            priceTitle.innerText = 'Fiyat Oluşturuldu!';

                            window.nihaiJP = data.nihai_jp;

                            const selectorContainer = document.getElementById('serviceSelectorContainer');
                            if (selectorContainer) {
                                selectorContainer.classList.remove('hidden');
                            }

                            window.updatePriceDisplay();

                            // Açıklama metnini gizle
                            priceDesc.classList.add('hidden');
                        } else {
                            throw new Error((data.debug_error ? 'DEBUG: ' + data.debug_error : '') || data.message || 'Analiz sırasında bir hata oluştu.');
                        }
                    })
                    .catch(error => {
                        console.error("===== HATA DETAYI =====", error.message || error);
                        priceSpinner.innerHTML = '<span class="material-symbols-outlined text-amber-500">schedule</span>';
                        priceTitle.className = 'font-body-md font-semibold text-amber-600';
                        priceTitle.innerText = 'Yapay Zeka Uyandırılıyor...';
                        priceDesc.classList.remove('hidden');
                        priceDesc.innerHTML = 'Sunucu ilk istekte biraz zaman alıyor. Çok fazla deneme yapmak engellenmenize (HTTP 429) sebep olabilir.<br><button id="retryBtn" class="underline font-semibold text-primary mt-2" disabled>Lütfen Bekleyin (15s)</button>';
                        const priceBreakdownEl = document.getElementById('priceBreakdown');
                        if (priceBreakdownEl) priceBreakdownEl.classList.add('hidden');

                        // Sadece manuel tekrar dene butonu koyalım, spam'i önlemek için 15 saniye bekletelim
                        let cooldown = 15;
                        const retryBtn = document.getElementById('retryBtn');
                        
                        const timer = setInterval(() => {
                            cooldown--;
                            if (retryBtn) retryBtn.innerText = `Lütfen Bekleyin (${cooldown}s)`;
                            
                            if (cooldown <= 0) {
                                clearInterval(timer);
                                if (retryBtn) {
                                    retryBtn.innerText = "Tekrar Dene";
                                    retryBtn.disabled = false;
                                    retryBtn.classList.remove('text-on-surface-variant');
                                    retryBtn.classList.add('text-primary');
                                }
                            }
                        }, 1000);

                        retryBtn?.addEventListener('click', (e) => {
                            e.preventDefault();
                            if (!retryBtn.disabled) {
                                retryBtn.disabled = true;
                                retryBtn.innerText = "Deneniyor...";
                                fileInput.dispatchEvent(new Event('change'));
                            }
                        });
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
                            confirmButtonColor: '#7a5555'
                        });
                        return;
                    }

                    // Append selected service type to client name so the artist can see it clearly
                    const nameInput = appointmentForm.querySelector('input[name="client_name"]');
                    if (nameInput && !nameInput.value.includes('(')) {
                        nameInput.value = `${nameInput.value} (Protez Tırnak)`;
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