@extends('layouts.app')

@section('title', "L'ART DE L'ONGLE - Randevu Paneli")

@section('content')
@php
    $user = auth()->user();
    // Prepare next 28 days (4 weeks) grouped by week
    $weeks = [];
    for($w=0; $w<4; $w++) {
        $weekDays = [];
        for($d=0; $d<7; $d++) {
            $weekDays[] = \Carbon\Carbon::today()->addDays(($w * 7) + $d);
        }
        $weeks[$w] = $weekDays;
    }
    
    // Prepare hours 09:00 to 18:00
    $hours = $user->work_hours ?? ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];

    // Prepare blocked slots for quick lookup
    $blockedSlots = [];
    foreach($scheduleBlocks as $block) {
        // e.g. "2023-10-14_09:00"
        $blockedSlots[$block->blocked_date . '_' . substr($block->blocked_time, 0, 5)] = true;
    }

    // Prepare occupied slots (active bookings)
    $occupiedSlots = [];
    $activeBookings = $user->appointments()
        ->whereIn('status', ['pending', 'approved'])
        ->where('appointment_date', '>=', today()->toDateString())
        ->get();
    foreach ($activeBookings as $appt) {
        $occupiedSlots[$appt->appointment_date . '_' . substr($appt->appointment_time, 0, 5)] = true;
    }
@endphp

<main class="flex-1 px-margin-mobile pt-md pb-[100px] flex flex-col gap-md max-w-[600px] mx-auto w-full" x-data="appointmentsManager()">
    {{-- Header & Segmented Control --}}
    <div class="flex flex-col gap-sm">
        <h2 class="font-headline-md text-headline-md text-on-background">Randevular</h2>
        <div class="flex gap-2 overflow-x-auto no-scrollbar py-1" role="tablist">
            <button @click="tab = 'appointments'" :class="tab === 'appointments' ? 'bg-primary text-on-primary shadow-[0px_4px_10px_rgba(122,85,85,0.2)]' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high'" class="whitespace-nowrap px-4 py-2 rounded-full font-label-caps text-label-caps transition-all duration-300">
                RANDEVULARIM
            </button>
            
            <button @click="tab = 'calendar'" :class="tab === 'calendar' ? 'bg-primary text-on-primary shadow-[0px_4px_10px_rgba(122,85,85,0.2)]' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high'" class="whitespace-nowrap px-4 py-2 rounded-full font-label-caps text-label-caps transition-all duration-300">
                TAKVİM
            </button>

            <button @click="tab = 'notes'" :class="tab === 'notes' ? 'bg-primary text-on-primary shadow-[0px_4px_10px_rgba(122,85,85,0.2)]' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high'" class="whitespace-nowrap px-4 py-2 rounded-full font-label-caps text-label-caps transition-all duration-300">
                NOTLARIM
            </button>
        </div>
    </div>

    {{-- TAB CONTENT: Appointments (Default) --}}
    <div x-show="tab === 'appointments'" x-transition.opacity class="flex flex-col gap-md mt-2">
        
        {{-- Earnings & Filters Dashboard --}}
        <div class="bg-surface-container-lowest rounded-xl p-md border border-outline-variant/30 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-sm relative">
            <div>
                <span class="font-label-caps text-label-caps text-on-surface-variant text-[11px] font-bold tracking-wider">TOPLAM KAZANÇ</span>
                <div class="font-headline-md text-headline-md text-primary mt-1" x-text="'₺' + totalEarnings">₺0</div>
            </div>
            
            <button type="button" @click="resetAllAppointments" class="absolute top-4 right-4 text-error hover:text-error/80 hover:bg-error/10 p-2 rounded-full transition-colors flex items-center justify-center" title="Tüm Randevuları ve Kazancı Sıfırla">
                <span class="material-symbols-outlined text-[20px]">delete_sweep</span>
            </button>

            <div class="flex gap-1 bg-surface-container p-1 rounded-full text-[11px] mt-2 sm:mt-0">
                <button type="button" @click="earningsFilter = 'week'; calculateEarnings();" 
                    :class="earningsFilter === 'week' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:text-on-surface'"
                    class="px-3 py-1.5 rounded-full font-bold font-label-caps transition-all">BU HAFTA</button>
                <button type="button" @click="earningsFilter = 'month'; calculateEarnings();" 
                    :class="earningsFilter === 'month' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:text-on-surface'"
                    class="px-3 py-1.5 rounded-full font-bold font-label-caps transition-all">BU AY</button>
                <button type="button" @click="earningsFilter = 'all'; calculateEarnings();" 
                    :class="earningsFilter === 'all' ? 'bg-primary text-on-primary shadow-sm' : 'text-on-surface-variant hover:text-on-surface'"
                    class="px-3 py-1.5 rounded-full font-bold font-label-caps transition-all">TÜMÜ</button>
            </div>
        </div>

        {{-- Sub-tabs --}}
        <div class="flex gap-2 border-b border-outline-variant/20 pb-1 overflow-x-auto no-scrollbar">
            <button type="button" @click="subTab = 'next'" 
                :class="subTab === 'next' ? 'text-primary border-b-2 border-primary font-bold' : 'text-on-surface-variant opacity-75'" 
                class="px-4 py-2 text-xs font-bold font-label-caps transition-all whitespace-nowrap">
                SIRADAKİ
            </button>
            <button type="button" @click="subTab = 'completed'" 
                :class="subTab === 'completed' ? 'text-primary border-b-2 border-primary font-bold' : 'text-on-surface-variant opacity-75'" 
                class="px-4 py-2 text-xs font-bold font-label-caps transition-all whitespace-nowrap">
                TAMAMLANAN
            </button>
            <button type="button" @click="subTab = 'cancelled'" 
                :class="subTab === 'cancelled' ? 'text-primary border-b-2 border-primary font-bold' : 'text-on-surface-variant opacity-75'" 
                class="px-4 py-2 text-xs font-bold font-label-caps transition-all whitespace-nowrap">
                İPTAL EDİLEN
            </button>
        </div>

        {{-- Sıradaki (Next) Randevular --}}
        <div x-show="subTab === 'next'" x-transition.opacity class="space-y-4">
            <template x-for="appointment in upcomingAppointments" :key="appointment.id">
                <div class="bg-surface-container-low rounded-xl p-sm border border-surface-container-high shadow-sm relative overflow-hidden">
                    <div class="flex justify-between items-start mb-sm">
                        <div class="flex items-center gap-sm">
                            <div class="h-10 w-10 rounded-full bg-tertiary-container flex items-center justify-center text-on-tertiary-container font-headline-sm text-headline-sm font-bold"
                                x-text="appointment.client_name.substring(0, 1)">
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-body-md text-body-md font-semibold text-on-surface" x-text="appointment.client_name"></h3>
                                    <span x-show="appointment.service_type_label" 
                                          class="px-2 py-0.5 bg-primary-container text-on-primary-container text-[10px] rounded-full font-bold uppercase tracking-wider" 
                                          x-text="appointment.service_type_label"></span>
                                </div>
                                <p class="font-label-caps text-label-caps text-secondary mt-0.5">
                                    <span x-text="appointment.date_formatted"></span> - 
                                    <span x-text="appointment.time_formatted"></span>
                                </p>
                            </div>
                        </div>

                        {{-- Price editing --}}
                        <div x-data="{ editing: false, tempPrice: appointment.price }" class="flex items-center gap-2 shrink-0">
                            <div x-show="!editing" class="flex items-center gap-1">
                                <span class="font-headline-sm text-headline-sm text-primary" x-text="'₺' + appointment.price"></span>
                                <button type="button" @click="editing = true; tempPrice = appointment.price;" class="text-on-surface-variant hover:text-primary p-1 flex items-center justify-center" title="Fiyatı Düzenle">
                                    <span class="material-symbols-outlined text-[16px]">edit</span>
                                </button>
                            </div>
                            <div x-show="editing" class="flex items-center gap-1">
                                <input type="number" x-model="tempPrice" class="w-16 bg-surface-container-low border border-outline rounded px-1.5 py-0.5 text-xs text-right focus:outline-none">
                                <button type="button" @click="updatePrice(appointment.id, tempPrice); editing = false;" class="text-green-600 hover:text-green-800 p-1 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[16px]">check</span>
                                </button>
                                <button type="button" @click="editing = false;" class="text-error hover:text-error/80 p-1 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <template x-if="appointment.image_url">
                        <div class="mb-sm rounded-lg overflow-hidden h-24 bg-surface-variant flex items-center justify-center border border-outline-variant/30 cursor-pointer" @click="openImageModal(appointment.image_url)">
                            <img alt="Nail Art Reference" class="w-full h-full object-cover" :src="appointment.image_url" x-on:error="$el.parentElement.style.display = 'none'" />
                        </div>
                    </template>

                    <div class="flex gap-sm">
                        <button type="button" @click="updateAppointmentStatusDirect(appointment.id, 'cancelled')" class="flex-1 py-2 px-4 rounded-full bg-error-container text-on-error-container font-label-caps text-label-caps hover:opacity-80 transition-opacity">
                            İptal Et
                        </button>
                        <button type="button" @click="updateAppointmentStatusDirect(appointment.id, 'completed')" class="flex-1 py-2 px-4 rounded-full bg-primary text-on-primary font-label-caps text-label-caps hover:bg-surface-tint transition-colors">
                            Tamamlandı
                        </button>
                    </div>
                </div>
            </template>
            
            <template x-if="upcomingAppointments.length === 0">
                <div class="bg-surface-container-low rounded-xl p-md text-center text-on-surface-variant border border-outline-variant/30 text-sm italic">
                    Sıradaki randevunuz bulunmuyor.
                </div>
            </template>
        </div>

        {{-- Tamamlanan (Completed) Randevular --}}
        <div x-show="subTab === 'completed'" x-transition.opacity class="space-y-4" x-cloak>
            <template x-for="appointment in completedAppointments" :key="appointment.id">
                <div class="bg-surface-container-low rounded-xl p-sm border border-surface-container-high shadow-sm relative overflow-hidden">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-sm">
                            <div class="h-10 w-10 rounded-full bg-tertiary-container flex items-center justify-center text-on-tertiary-container font-headline-sm text-headline-sm font-bold"
                                x-text="appointment.client_name.substring(0, 1)">
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-body-md text-body-md font-semibold text-on-surface" x-text="appointment.client_name"></h3>
                                    <span x-show="appointment.service_type_label" 
                                          class="px-2 py-0.5 bg-primary-container text-on-primary-container text-[10px] rounded-full font-bold uppercase tracking-wider" 
                                          x-text="appointment.service_type_label"></span>
                                </div>
                                <p class="font-label-caps text-label-caps text-secondary mt-0.5">
                                    <span x-text="appointment.date_formatted"></span> - 
                                    <span x-text="appointment.time_formatted"></span>
                                </p>
                            </div>
                        </div>

                        {{-- Price editing --}}
                        <div x-data="{ editing: false, tempPrice: appointment.price }" class="flex items-center gap-2 shrink-0">
                            <div x-show="!editing" class="flex items-center gap-1">
                                <span class="font-headline-sm text-headline-sm text-primary" x-text="'₺' + appointment.price"></span>
                                <button type="button" @click="editing = true; tempPrice = appointment.price;" class="text-on-surface-variant hover:text-primary p-1 flex items-center justify-center" title="Fiyatı Düzenle">
                                    <span class="material-symbols-outlined text-[16px]">edit</span>
                                </button>
                            </div>
                            <div x-show="editing" class="flex items-center gap-1">
                                <input type="number" x-model="tempPrice" class="w-16 bg-surface-container-low border border-outline rounded px-1.5 py-0.5 text-xs text-right focus:outline-none">
                                <button type="button" @click="updatePrice(appointment.id, tempPrice); editing = false;" class="text-green-600 hover:text-green-800 p-1 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[16px]">check</span>
                                </button>
                                <button type="button" @click="editing = false;" class="text-error hover:text-error/80 p-1 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <template x-if="appointment.image_url">
                        <div class="mt-sm rounded-lg overflow-hidden h-24 bg-surface-variant flex items-center justify-center border border-outline-variant/30 cursor-pointer" @click="openImageModal(appointment.image_url)">
                            <img alt="Nail Art Reference" class="w-full h-full object-cover" :src="appointment.image_url" x-on:error="$el.parentElement.style.display = 'none'" />
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="completedAppointments.length === 0">
                <div class="bg-surface-container-low rounded-xl p-md text-center text-on-surface-variant border border-outline-variant/30 text-sm italic">
                    Tamamlanan randevunuz bulunmuyor.
                </div>
            </template>
        </div>

        {{-- İptal Edilen (Cancelled) Randevular --}}
        <div x-show="subTab === 'cancelled'" x-transition.opacity class="space-y-4" x-cloak>
            <template x-for="appointment in cancelledAppointments" :key="appointment.id">
                <div class="bg-surface-container-low rounded-xl p-sm border border-surface-container-high shadow-sm relative overflow-hidden opacity-70">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-sm">
                            <div class="h-10 w-10 rounded-full bg-tertiary-container flex items-center justify-center text-on-tertiary-container font-headline-sm text-headline-sm font-bold"
                                x-text="appointment.client_name.substring(0, 1)">
                            </div>
                            <div>
                                <h3 class="font-body-md text-body-md font-semibold text-on-surface-variant" x-text="appointment.client_name"></h3>
                                <p class="font-label-caps text-label-caps text-on-surface-variant/60 mt-0.5">
                                    <span x-text="appointment.date_formatted"></span> - 
                                    <span x-text="appointment.time_formatted"></span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <span class="font-headline-sm text-headline-sm text-on-surface-variant/60" x-text="'₺' + appointment.price"></span>
                        </div>
                    </div>

                    <template x-if="appointment.image_url">
                        <div class="mt-sm rounded-lg overflow-hidden h-24 bg-surface-variant flex items-center justify-center border border-outline-variant/30 cursor-pointer" @click="openImageModal(appointment.image_url)">
                            <img alt="Nail Art Reference" class="w-full h-full object-cover opacity-80" :src="appointment.image_url" x-on:error="$el.parentElement.style.display = 'none'" />
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="cancelledAppointments.length === 0">
                <div class="bg-surface-container-low rounded-xl p-md text-center text-on-surface-variant border border-outline-variant/30 text-sm italic">
                    İptal edilen randevunuz bulunmuyor.
                </div>
            </template>
        </div>
    </div>

    {{-- TAB CONTENT: Messages --}}
    <div x-cloak x-show="tab === 'messages'" x-transition.opacity class="flex-col gap-sm mt-2">
        <div class="bg-surface-container-low rounded-xl p-md border border-outline-variant/30 text-center py-10">
            <span class="material-symbols-outlined text-4xl text-outline mb-2">inbox</span>
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Mesaj Kutusu</h3>
            <p class="font-body-md text-on-surface-variant">Müşterilerinizle olan mesajlarınız burada listelenecek.</p>
        </div>
    </div>

    {{-- TAB CONTENT: Calendar --}}
    <div x-cloak x-show="tab === 'calendar'" x-transition.opacity class="flex flex-col gap-sm mt-2">
        {{-- Section: Daily Work Hours Settings --}}
        <div class="bg-surface-container-lowest rounded-xl p-md border border-outline-variant/30 shadow-sm">
            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-2">Çalışma Saatleri</h3>
            <p class="font-body-md text-on-surface-variant text-sm mb-4">Takviminizde gösterilecek günlük randevu saat dilimlerini düzenleyin.</p>
            
            <form action="{{ route('panel.schedule.hours') }}" method="POST" class="space-y-4">
                @csrf
                <div class="flex flex-wrap gap-2 items-center" x-data="{ 
                    hours: {{ json_encode($hours) }}, 
                    newHour: '',
                    addHour() {
                        if (!this.newHour) return;
                        if (!/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/.test(this.newHour)) {
                            alert('Lütfen geçerli bir saat girin (Örn: 14:30)');
                            return;
                        }
                        let parts = this.newHour.split(':');
                        let formatted = parts[0].padStart(2, '0') + ':' + parts[1].padStart(2, '0');
                        if (!this.hours.includes(formatted)) {
                            this.hours.push(formatted);
                            this.hours.sort();
                        }
                        this.newHour = '';
                    },
                    removeHour(index) {
                        this.hours.splice(index, 1);
                    }
                }">
                    <template x-for="(hour, index) in hours" :key="hour">
                        <div class="inline-flex items-center gap-1.5 bg-primary/10 text-primary border border-primary/20 px-3 py-1.5 rounded-full text-xs font-semibold">
                            <span x-text="hour"></span>
                            <button type="button" @click="removeHour(index)" class="text-primary/70 hover:text-primary flex items-center justify-center">
                                <span class="material-symbols-outlined text-[14px]">close</span>
                            </button>
                            <input type="hidden" name="work_hours[]" :value="hour">
                        </div>
                    </template>
                    
                    <div class="flex gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                        <input type="time" x-model="newHour" class="bg-surface-container-low border border-outline-variant rounded-lg px-3 py-1.5 text-xs text-on-surface focus:outline-none focus:border-primary">
                        <button type="button" @click="addHour()" class="bg-secondary text-on-secondary hover:opacity-90 px-4 py-1.5 rounded-lg text-xs font-semibold font-label-caps transition-opacity">EKLE</button>
                    </div>
                </div>
                
                <div class="flex justify-end pt-2 border-t border-surface-variant/30">
                    <button type="submit" class="bg-primary text-on-primary hover:opacity-90 px-6 py-2.5 rounded-full text-xs font-bold font-label-caps transition-opacity shadow-sm">SAATLERİ KAYDET</button>
                </div>
            </form>
        </div>

        {{-- Section: Availability Management --}}
        <div class="bg-surface-container-lowest rounded-xl p-4 border border-outline-variant/30 shadow-sm mt-2">
            <div class="flex items-center justify-between border-b border-surface-variant/30 pb-3 mb-4">
                <div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface">Müsaitlik Yönetimi</h3>
                    <p class="font-body-md text-on-surface-variant text-[11px] mt-0.5">Mola veya tatil zamanlarında istediğiniz saat dilimini kapatabilirsiniz (Kırmızı = Kapalı, Gri = Müsait).</p>
                </div>
            </div>

            <div class="bg-surface-container-low rounded-xl p-4 border border-outline-variant/30 shadow-inner">
                <!-- Month / Year with arrows inside calendar box -->
                <div class="relative flex items-center justify-center mb-4">
                    <button type="button" @click="prevMonth()" x-show="shouldShowPrevArrow()"
                        class="absolute left-0 p-1 rounded-full bg-surface-container hover:bg-surface-variant transition-colors flex items-center justify-center w-7 h-7 z-10">
                        <span class="material-symbols-outlined text-sm font-bold">chevron_left</span>
                    </button>
                    
                    <div class="font-label-caps text-label-caps text-primary tracking-widest text-center text-xs font-bold" x-text="monthName"></div>
                    
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
                        <div 
                            @click="selectDay(day)"
                            :class="{
                                'text-on-surface-variant opacity-30 cursor-not-allowed': !day.isSelectable,
                                'rounded-full bg-error/10 text-error border border-error/20 cursor-pointer line-through': day.isSelectable && selectedDate !== day.dateStr && isDayFullyBooked(day.dateStr),
                                'rounded-full hover:bg-surface-container cursor-pointer transition-colors': day.isSelectable && selectedDate !== day.dateStr && !isDayFullyBooked(day.dateStr),
                                'rounded-full bg-primary text-on-primary font-bold shadow-sm cursor-pointer': day.isSelectable && selectedDate === day.dateStr
                            }"
                            class="py-2 relative select-none flex items-center justify-center w-9 h-9 mx-auto transition-colors"
                        >
                            <span x-text="day.dayNum"></span>
                            <template x-if="day.hasDot">
                                <span class="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 rounded-full"
                                      :class="selectedDate === day.dateStr ? 'bg-on-primary' : 'bg-primary'"></span>
                            </template>
                        </div>
                    </template>
                </div>
                
                {{-- Time Slots --}}
                <div class="mt-8 border-t border-outline-variant/20 pt-4" x-show="selectedDate">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                        <div class="font-label-caps text-label-caps text-on-surface-variant font-bold text-xs tracking-wider"
                             x-text="formatFriendlySelectedDate() + ' TARİHİ İÇİN SAAT YÖNETİMİ'"></div>
                        <div class="flex gap-2">
                            <button type="button" @click="toggleDayLocal(selectedDate, 'block')"
                                class="bg-error-container/20 text-error hover:bg-error-container/40 border border-error/30 px-3 py-1.5 rounded-lg text-[10px] font-bold font-label-caps tracking-wide transition-colors flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">block</span> GÜNÜ KAPAT
                            </button>
                            <button type="button" @click="toggleDayLocal(selectedDate, 'open')"
                                class="bg-primary/10 text-primary hover:bg-primary/20 border border-primary/20 px-3 py-1.5 rounded-lg text-[10px] font-bold font-label-caps tracking-wide transition-colors flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">lock_open</span> GÜNÜ AÇ
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <template x-for="hour in hours" :key="hour">
                            <button type="button"
                                @click="toggleBlockLocal(selectedDate, hour)"
                                :disabled="isOccupied(hour)"
                                :class="{
                                    'bg-secondary/15 text-secondary border border-secondary/20 cursor-not-allowed opacity-75': isOccupied(hour),
                                    'bg-error-container/40 text-error border border-error/20 hover:bg-error-container/50': !isOccupied(hour) && isBlocked(hour),
                                    'bg-surface-container text-on-surface-variant hover:bg-surface-container-high border border-transparent': !isOccupied(hour) && !isBlocked(hour)
                                }"
                                class="py-2.5 rounded-lg font-body-md text-xs font-semibold transition-all duration-200 flex items-center justify-center gap-1.5 whitespace-nowrap"
                            >
                                <span class="material-symbols-outlined text-[14px]" x-text="isOccupied(hour) ? 'event_busy' : (isBlocked(hour) ? 'lock' : 'check_circle')"></span>
                                <span x-text="formatTimeLabel(hour)"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="flex justify-center flex-wrap gap-4 text-[9px] md:text-[10px] text-on-surface-variant pt-2 border-t border-outline-variant/20 mt-4">
                    <div class="flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded bg-surface-container inline-block"></span>
                        <span>Müsait (Açık)</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded bg-error-container/40 border border-error/20 inline-block"></span>
                        <span>Kapalı (Bloke)</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded bg-secondary/15 border border-secondary/20 inline-block"></span>
                        <span>Dolu (Müşteri Randevusu)</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded bg-error/10 border border-error/20 inline-block line-through text-[7px] text-center leading-none text-error font-bold">31</span>
                        <span>Tamamen Dolu / Kapalı</span>
                    </div>
                </div>

                {{-- Save Changes Button Section --}}
                <div class="mt-6 pt-4 border-t border-outline-variant/20 flex flex-col gap-2" x-show="selectedDate">
                    <button type="button" 
                        @click="saveAvailabilityChanges()"
                        :disabled="!hasUnsavedChanges() || isSavingAvailability"
                        :class="{
                            'bg-gradient-to-r from-primary to-[#8a6565] text-on-primary shadow-md hover:opacity-90 animate-pulse': hasUnsavedChanges() && !isSavingAvailability,
                            'bg-surface-container-high text-on-surface-variant/40 cursor-not-allowed': !hasUnsavedChanges() || isSavingAvailability
                        }"
                        class="w-full font-label-caps text-label-caps py-3 rounded-xl flex justify-center items-center gap-2 transition-all duration-300 font-semibold"
                    >
                        <template x-if="isSavingAvailability">
                            <span class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                        </template>
                        <template x-if="!isSavingAvailability">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                        </template>
                        DEĞİŞİKLİKLERİ KAYDET
                    </button>
                    <template x-if="hasUnsavedChanges()">
                        <p class="text-[10px] text-primary/80 font-semibold text-center mt-1 animate-bounce">
                            ⚠️ Kaydedilmemiş değişiklikleriniz var. Kaydetmek için tıklayın.
                        </p>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB CONTENT: Notes --}}
    <div x-cloak x-show="tab === 'notes'" x-transition.opacity class="flex flex-col gap-md mt-2">
        {{-- Add Note Button --}}
        <div class="flex justify-end">
            <button type="button" @click="openCreateNote()" 
                class="bg-primary text-on-primary hover:bg-surface-tint px-5 py-2.5 rounded-full text-xs font-bold font-label-caps transition-colors shadow-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">add</span> NOT EKLE
            </button>
        </div>

        {{-- Create/Edit Note Form Inline --}}
        <div x-show="isCreatingNote" x-transition.opacity class="bg-surface-container-lowest rounded-xl p-md border border-outline-variant/30 shadow-sm flex flex-col gap-sm">
            <h4 class="font-headline-sm text-headline-sm text-on-surface text-sm font-semibold">Yeni Not Oluştur</h4>
            <div class="flex flex-col gap-xs mt-1">
                <label class="text-[11px] font-bold font-label-caps text-on-surface-variant">NOT BAŞLIĞI</label>
                <input type="text" x-model="noteFormTitle" placeholder="Örn: Ayşe Hanım için özel oje rengi..." 
                    class="bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none focus:border-primary w-full">
            </div>
            <div class="flex flex-col gap-xs">
                <label class="text-[11px] font-bold font-label-caps text-on-surface-variant">NOT İÇERİĞİ</label>
                <textarea x-model="noteFormContent" rows="4" placeholder="Not detaylarını buraya yazın..." 
                    class="bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none focus:border-primary w-full resize-none"></textarea>
            </div>
            <div class="flex justify-end gap-sm pt-2 border-t border-surface-variant/30 mt-2">
                <button type="button" @click="closeCreateNote()" class="text-on-surface-variant hover:text-on-surface px-4 py-2 rounded-full text-xs font-semibold font-label-caps transition-colors">VAZGEÇ</button>
                <button type="button" @click="saveNote()" :disabled="isSavingNote" 
                    class="bg-primary text-on-primary hover:opacity-90 px-6 py-2 rounded-full text-xs font-bold font-label-caps transition-opacity flex items-center gap-1.5 shadow-sm">
                    <template x-if="isSavingNote">
                        <span class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span>
                    </template>
                    KAYDET
                </button>
            </div>
        </div>

        {{-- Notes Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-sm">
            <template x-for="note in notes" :key="note.id">
                <div class="bg-surface-container-low hover:bg-surface-container-high rounded-xl p-sm border border-surface-container-high hover:border-outline-variant/30 shadow-sm relative overflow-hidden transition-all duration-300 flex flex-col justify-between cursor-pointer"
                    @click="openEditNote(note)">
                    <div class="flex justify-between items-start gap-sm mb-sm">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-body-md text-body-md font-semibold text-on-surface truncate" x-text="note.title"></h4>
                            <p class="font-body-sm text-body-sm text-on-surface-variant mt-1 line-clamp-3" x-text="note.content"></p>
                        </div>
                        <button type="button" @click.stop="deleteNote(note.id)" :disabled="isDeletingNote"
                            class="text-on-surface-variant hover:text-error p-1 rounded-full hover:bg-error-container/10 transition-colors shrink-0 flex items-center justify-center" title="Notu Sil">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between border-t border-outline-variant/10 pt-2 mt-auto">
                        <span class="text-[10px] font-medium text-secondary/80 font-label-caps flex items-center gap-1">
                            <span class="material-symbols-outlined text-[12px]">calendar_today</span>
                            <span x-text="note.date_formatted"></span>
                        </span>
                        <span class="text-[10px] font-semibold text-primary font-label-caps hover:underline">Düzenle</span>
                    </div>
                </div>
            </template>

            <template x-if="notes.length === 0">
                <div class="col-span-full bg-surface-container-low rounded-xl p-md text-center text-on-surface-variant border border-outline-variant/30 text-sm italic py-10">
                    <span class="material-symbols-outlined text-4xl text-outline mb-2">sticky_note_2</span>
                    <p class="font-body-md">Kayıtlı notunuz bulunmuyor.</p>
                </div>
            </template>
        </div>
    </div>

    {{-- Note Edit Modal --}}
    <div x-cloak x-show="selectedNote !== null" class="fixed inset-0 z-[150] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-transition.opacity>
        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/30 shadow-2xl w-full max-w-[450px] p-md flex flex-col gap-sm relative" @click.away="closeEditNote()">
            <div class="flex justify-between items-center border-b border-surface-variant/30 pb-sm">
                <h3 class="font-headline-sm text-headline-sm text-on-surface">Notu Düzenle</h3>
                <button @click="closeEditNote()" class="text-on-surface-variant hover:text-on-surface p-1 rounded-full hover:bg-surface-container flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>
            <div class="flex flex-col gap-xs mt-1">
                <label class="text-[11px] font-bold font-label-caps text-on-surface-variant">NOT BAŞLIĞI</label>
                <input type="text" x-model="noteFormTitle" class="bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none focus:border-primary w-full">
            </div>
            <div class="flex flex-col gap-xs">
                <label class="text-[11px] font-bold font-label-caps text-on-surface-variant">NOT İÇERİĞİ</label>
                <textarea x-model="noteFormContent" rows="5" class="bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-sm text-on-surface focus:outline-none focus:border-primary w-full resize-none"></textarea>
            </div>
            <div class="flex justify-between items-center pt-sm border-t border-surface-variant/30 mt-2">
                <button type="button" @click="deleteNote(selectedNote.id)" :disabled="isDeletingNote"
                    class="bg-error-container text-on-error-container hover:bg-error-container/85 px-4 py-2 rounded-full text-xs font-bold font-label-caps transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">delete</span> SİL
                </button>
                <div class="flex gap-sm">
                    <button type="button" @click="closeEditNote()" class="text-on-surface-variant hover:text-on-surface px-4 py-2 rounded-full text-xs font-semibold font-label-caps transition-colors">VAZGEÇ</button>
                    <button type="button" @click="updateNote()" :disabled="isSavingNote" 
                        class="bg-primary text-on-primary hover:opacity-90 px-6 py-2 rounded-full text-xs font-bold font-label-caps transition-opacity flex items-center gap-1.5 shadow-sm">
                        <template x-if="isSavingNote">
                            <span class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span>
                        </template>
                        GÜNCELLE
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Fullscreen Design Image Modal --}}
    <div x-cloak x-show="imageModalOpen" class="fixed inset-0 z-[150] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" x-transition.opacity>
        <div class="relative max-w-lg max-h-[80vh] w-full" @click.away="imageModalOpen = false">
            <button @click="imageModalOpen = false" class="absolute -top-12 right-0 text-white hover:opacity-85 p-2 bg-black/30 rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
            <img :src="modalImageUrl" class="max-w-full max-h-[80vh] mx-auto object-contain rounded-xl shadow-2xl">
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        customClass: {
            popup: 'rounded-xl shadow-lg border border-outline/10 bg-surface-container-lowest text-on-surface font-body-md'
        }
    });

    document.addEventListener('alpine:init', () => {
        Alpine.data('appointmentsManager', () => ({
            tab: 'appointments',
            subTab: 'next',
            imageModalOpen: false,
            modalImageUrl: '',
            earningsFilter: 'all',
            totalEarnings: 0,
            
            notes: {!! json_encode($notes->map(fn($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'content' => $n->content,
                'date_formatted' => \Carbon\Carbon::parse($n->created_at)->locale('tr')->translatedFormat('d M, Y'),
            ])) !!},
            isCreatingNote: false,
            noteFormTitle: '',
            noteFormContent: '',
            selectedNote: null,
            isSavingNote: false,
            isDeletingNote: false,
            
            upcomingAppointments: {!! json_encode($upcomingAppointments->map(fn($a) => [
                'id' => $a->id,
                'price' => floatval($a->estimated_price),
                'date' => $a->appointment_date,
                'date_formatted' => strtoupper(\Carbon\Carbon::parse($a->appointment_date)->locale('tr')->translatedFormat('d M')),
                'time_formatted' => \Carbon\Carbon::parse($a->appointment_time)->format('H:i'),
                'client_name' => str_replace(' (Protez Tırnak)', '', $a->client_name),
                'service_type_label' => $a->service_type === 'yapim' ? 'Yapım' : ($a->service_type === 'cikarma' ? 'Çıkarma' : ''),
                'tracking_code_short' => substr($a->tracking_code, 0, 4),

                'image_url' => $a->image_path ? (str_starts_with($a->image_path, 'http') ? $a->image_path : asset('storage/' . $a->image_path)) : null,
            ])) !!},
            completedAppointments: {!! json_encode($completedAppointments->map(fn($a) => [
                'id' => $a->id,
                'price' => floatval($a->estimated_price),
                'date' => $a->appointment_date,
                'date_formatted' => strtoupper(\Carbon\Carbon::parse($a->appointment_date)->locale('tr')->translatedFormat('d M')),
                'time_formatted' => \Carbon\Carbon::parse($a->appointment_time)->format('H:i'),
                'client_name' => str_replace(' (Protez Tırnak)', '', $a->client_name),
                'service_type_label' => $a->service_type === 'yapim' ? 'Yapım' : ($a->service_type === 'cikarma' ? 'Çıkarma' : ''),
                'tracking_code_short' => substr($a->tracking_code, 0, 4),

                'image_url' => $a->image_path ? (str_starts_with($a->image_path, 'http') ? $a->image_path : asset('storage/' . $a->image_path)) : null,
            ])) !!},
            cancelledAppointments: {!! json_encode($cancelledAppointments->map(fn($a) => [
                'id' => $a->id,
                'price' => floatval($a->estimated_price),
                'date' => $a->appointment_date,
                'date_formatted' => strtoupper(\Carbon\Carbon::parse($a->appointment_date)->locale('tr')->translatedFormat('d M')),
                'time_formatted' => \Carbon\Carbon::parse($a->appointment_time)->format('H:i'),
                'client_name' => str_replace(' (Protez Tırnak)', '', $a->client_name),
                'service_type_label' => $a->service_type === 'yapim' ? 'Yapım' : ($a->service_type === 'cikarma' ? 'Çıkarma' : ''),
                'tracking_code_short' => substr($a->tracking_code, 0, 4),

                'image_url' => $a->image_path ? (str_starts_with($a->image_path, 'http') ? $a->image_path : asset('storage/' . $a->image_path)) : null,
            ])) !!},
            
            blockedSlots: {!! json_encode($blockedSlots) !!},
            localBlockedSlots: {},
            isSavingAvailability: false,
            occupiedSlots: {!! json_encode($occupiedSlots) !!},
            hours: {!! json_encode($hours) !!},
            todayStr: '{{ today()->toDateString() }}',
            
            selectedDate: '',
            currentYear: null,
            currentMonth: null,
            monthName: '',
            daysInGrid: [],
            
            init() {
                const today = new Date(this.todayStr);
                this.currentYear = today.getFullYear();
                this.currentMonth = today.getMonth();
                this.generateGrid();
                this.calculateEarnings();
                this.localBlockedSlots = JSON.parse(JSON.stringify(this.blockedSlots));
                setInterval(() => {
                    this.fetchUpdates();
                }, 5000);
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
                if (!day.isSelectable) return;
                this.selectedDate = day.dateStr;
            },

            isBlocked(hour) {
                const key = `${this.selectedDate}_${hour}`;
                return !!this.localBlockedSlots[key];
            },

            isOccupied(hour) {
                const key = `${this.selectedDate}_${hour}`;
                return !!this.occupiedSlots[key];
            },

            isDayFullyBooked(dateStr) {
                if (!dateStr) return false;
                if (this.hours.length === 0) return false;
                return this.hours.every(hour => {
                    const key = `${dateStr}_${hour}`;
                    return !!this.localBlockedSlots[key] || !!this.occupiedSlots[key];
                });
            },

            formatTimeLabel(hourStr) {
                return hourStr.substring(0, 5);
            },

            formatFriendlySelectedDate() {
                if (!this.selectedDate) return '';
                const parts = this.selectedDate.split('-');
                const date = new Date(parts[0], parts[1] - 1, parts[2]);
                return date.toLocaleDateString('tr-TR', { day: 'numeric', month: 'short' }).toUpperCase();
            },

            async updateAppointmentStatus(id, status, event) {
                const btn = event.currentTarget;
                const spinner = btn.querySelector('.spinner');
                const card = document.getElementById('appointment-card-' + id);
                
                spinner.classList.remove('hidden');
                
                try {
                    const response = await fetch(`/panel/appointments/${id}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: status })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        card.classList.add('opacity-0', 'scale-95');
                        setTimeout(() => card.remove(), 500);
                        
                        Toast.fire({
                            icon: 'success',
                            title: status === 'approved' ? 'Randevu onaylandı!' : 'Randevu reddedildi.',
                            iconColor: '#7a5555'
                        });
                    } else {
                        spinner.classList.add('hidden');
                        Toast.fire({
                            icon: 'error',
                            title: data.message || 'Hata oluştu.',
                            iconColor: '#ba1a1a'
                        });
                    }
                } catch (error) {
                    spinner.classList.add('hidden');
                    Toast.fire({
                        icon: 'error',
                        title: 'Bağlantı hatası.',
                        iconColor: '#ba1a1a'
                    });
                }
            },

            toggleBlockLocal(date, time) {
                const key = `${date}_${time}`;
                this.localBlockedSlots[key] = !this.localBlockedSlots[key];
                this.localBlockedSlots = { ...this.localBlockedSlots };
            },

            toggleDayLocal(date, action) {
                if (!date) return;
                
                this.hours.forEach(hour => {
                    const key = `${date}_${hour}`;
                    if (!this.isOccupied(hour)) {
                        if (action === 'block') {
                            this.localBlockedSlots[key] = true;
                        } else {
                            this.localBlockedSlots[key] = false;
                        }
                    }
                });
                
                this.localBlockedSlots = { ...this.localBlockedSlots };
            },

            hasUnsavedChanges() {
                if (!this.selectedDate) return false;
                return this.hours.some(hour => {
                    const key = `${this.selectedDate}_${hour}`;
                    const localVal = !!this.localBlockedSlots[key];
                    const savedVal = !!this.blockedSlots[key];
                    return localVal !== savedVal;
                });
            },

            async saveAvailabilityChanges() {
                if (!this.selectedDate) return;
                
                this.isSavingAvailability = true;
                
                const blockedHours = [];
                this.hours.forEach(hour => {
                    const key = `${this.selectedDate}_${hour}`;
                    if (this.localBlockedSlots[key]) {
                        blockedHours.push(hour);
                    }
                });

                try {
                    const response = await fetch("{{ route('panel.schedule.save-day') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            date: this.selectedDate,
                            blocked_hours: blockedHours
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.hours.forEach(hour => {
                            const key = `${this.selectedDate}_${hour}`;
                            if (this.localBlockedSlots[key]) {
                                this.blockedSlots[key] = true;
                            } else {
                                delete this.blockedSlots[key];
                            }
                        });
                        this.blockedSlots = { ...this.blockedSlots };

                        Toast.fire({
                            icon: 'success',
                            title: data.message,
                            iconColor: '#7a5555'
                        });
                        
                        this.fetchUpdates();
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: data.message || 'Bir hata oluştu.',
                            iconColor: '#ba1a1a'
                        });
                    }
                } catch (error) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Bağlantı hatası.',
                        iconColor: '#ba1a1a'
                    });
                } finally {
                    this.isSavingAvailability = false;
                }
            },

            getAppointmentPrice(id) {
                let appt = this.completedAppointments.find(a => a.id === id) 
                    || this.upcomingAppointments.find(a => a.id === id)
                    || this.cancelledAppointments.find(a => a.id === id);
                return appt ? Math.round(appt.price) : 0;
            },

            async fetchUpdates() {
                try {
                    const response = await fetch('{{ route("panel.api.updates") }}');
                    if (response.ok) {
                        const data = await response.json();
                        if (data.success) {
                            this.upcomingAppointments = data.upcomingAppointments;
                            this.completedAppointments = data.completedAppointments;
                            this.cancelledAppointments = data.cancelledAppointments;
                            this.blockedSlots = data.blockedSlots;
                            this.occupiedSlots = data.occupiedSlots;
                            this.notes = data.notes || [];
                            
                            if (!this.hasUnsavedChanges()) {
                                this.localBlockedSlots = JSON.parse(JSON.stringify(this.blockedSlots));
                            } else {
                                Object.keys(this.blockedSlots).forEach(key => {
                                    if (!key.startsWith(this.selectedDate + '_')) {
                                        this.localBlockedSlots[key] = this.blockedSlots[key];
                                    }
                                });
                                Object.keys(this.localBlockedSlots).forEach(key => {
                                    if (!key.startsWith(this.selectedDate + '_') && !this.blockedSlots[key]) {
                                        delete this.localBlockedSlots[key];
                                    }
                                });
                                this.localBlockedSlots = { ...this.localBlockedSlots };
                            }
                            
                            this.calculateEarnings();
                        }
                    }
                } catch (e) {
                    console.error("Updates polling error: ", e);
                }
            },

            async updateAppointmentStatusDirect(id, status) {
                try {
                    const response = await fetch(`/panel/appointments/${id}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: status })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        Toast.fire({
                            icon: 'success',
                            title: status === 'completed' ? 'Randevu tamamlandı olarak işaretlendi!' : 'Randevu iptal edildi.',
                            iconColor: '#7a5555'
                        });
                        this.fetchUpdates();
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: data.message || 'Hata oluştu.',
                            iconColor: '#ba1a1a'
                        });
                    }
                } catch (error) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Bağlantı hatası.',
                        iconColor: '#ba1a1a'
                    });
                }
            },

            openCreateNote() {
                this.isCreatingNote = true;
                this.noteFormTitle = '';
                this.noteFormContent = '';
            },
            
            closeCreateNote() {
                this.isCreatingNote = false;
                this.noteFormTitle = '';
                this.noteFormContent = '';
            },
            
            async saveNote() {
                if (!this.noteFormTitle.trim() || !this.noteFormContent.trim()) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Lütfen başlık ve içerik girin.',
                        iconColor: '#ba1a1a'
                    });
                    return;
                }
                
                this.isSavingNote = true;
                try {
                    const response = await fetch("{{ route('panel.notes.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            title: this.noteFormTitle,
                            content: this.noteFormContent
                        })
                    });
                    
                    const data = await response.json();
                    if (response.ok && data.success) {
                        this.notes.unshift(data.note);
                        this.closeCreateNote();
                        Toast.fire({
                            icon: 'success',
                            title: data.message,
                            iconColor: '#7a5555'
                        });
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: data.message || 'Bir hata oluştu.',
                            iconColor: '#ba1a1a'
                        });
                    }
                } catch (e) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Bağlantı hatası.',
                        iconColor: '#ba1a1a'
                    });
                } finally {
                    this.isSavingNote = false;
                }
            },
            
            openEditNote(note) {
                this.selectedNote = note;
                this.noteFormTitle = note.title;
                this.noteFormContent = note.content;
            },
            
            closeEditNote() {
                this.selectedNote = null;
                this.noteFormTitle = '';
                this.noteFormContent = '';
            },
            
            async updateNote() {
                if (!this.selectedNote) return;
                if (!this.noteFormTitle.trim() || !this.noteFormContent.trim()) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Lütfen başlık ve içerik girin.',
                        iconColor: '#ba1a1a'
                    });
                    return;
                }
                
                this.isSavingNote = true;
                try {
                    const response = await fetch(`/panel/notes/${this.selectedNote.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            title: this.noteFormTitle,
                            content: this.noteFormContent
                        })
                    });
                    
                    const data = await response.json();
                    if (response.ok && data.success) {
                        const index = this.notes.findIndex(n => n.id === this.selectedNote.id);
                        if (index !== -1) {
                            this.notes[index] = data.note;
                        }
                        this.closeEditNote();
                        Toast.fire({
                            icon: 'success',
                            title: data.message,
                            iconColor: '#7a5555'
                        });
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: data.message || 'Bir hata oluştu.',
                            iconColor: '#ba1a1a'
                        });
                    }
                } catch (e) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Bağlantı hatası.',
                        iconColor: '#ba1a1a'
                    });
                } finally {
                    this.isSavingNote = false;
                }
            },
            
            async deleteNote(id) {
                const confirmed = await Swal.fire({
                    title: 'Emin misiniz?',
                    text: 'Bu not kalıcı olarak silinecektir.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#7a5555',
                    cancelButtonColor: '#ba1a1a',
                    confirmButtonText: 'Evet, sil!',
                    cancelButtonText: 'Vazgeç',
                    customClass: {
                        popup: 'rounded-xl shadow-lg border border-outline/10 bg-surface-container-lowest text-on-surface font-body-md'
                    }
                });
                
                if (!confirmed.isConfirmed) return;
                
                this.isDeletingNote = true;
                try {
                    const response = await fetch(`/panel/notes/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    if (response.ok && data.success) {
                        this.notes = this.notes.filter(n => n.id !== id);
                        this.closeEditNote();
                        Toast.fire({
                            icon: 'success',
                            title: data.message,
                            iconColor: '#7a5555'
                        });
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: data.message || 'Bir hata oluştu.',
                            iconColor: '#ba1a1a'
                        });
                    }
                } catch (e) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Bağlantı hatası.',
                        iconColor: '#ba1a1a'
                    });
                } finally {
                    this.isDeletingNote = false;
                }
            },

            openImageModal(url) {
                this.modalImageUrl = url;
                this.imageModalOpen = true;
            },

            calculateEarnings() {
                let sum = 0;
                const now = new Date();
                
                const startOfWeek = new Date();
                const day = startOfWeek.getDay();
                const diff = startOfWeek.getDate() - day + (day === 0 ? -6 : 1);
                startOfWeek.setDate(diff);
                startOfWeek.setHours(0, 0, 0, 0);
                
                const endOfWeek = new Date(startOfWeek);
                endOfWeek.setDate(startOfWeek.getDate() + 6);
                endOfWeek.setHours(23, 59, 59, 999);
                
                const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
                const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59, 999);

                this.completedAppointments.forEach(appt => {
                    const apptDate = new Date(appt.date);
                    let include = false;
                    
                    if (this.earningsFilter === 'all') {
                        include = true;
                    } else if (this.earningsFilter === 'month') {
                        include = apptDate >= startOfMonth && apptDate <= endOfMonth;
                    } else if (this.earningsFilter === 'week') {
                        include = apptDate >= startOfWeek && apptDate <= endOfWeek;
                    }
                    
                    if (include) {
                        sum += appt.price;
                    }
                });
                
                this.totalEarnings = Math.round(sum);
            },

            async updatePrice(id, newPrice) {
                if (parseFloat(newPrice) < 0 || isNaN(parseFloat(newPrice))) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Lütfen geçerli bir fiyat girin.',
                        iconColor: '#ba1a1a'
                    });
                    return;
                }

                try {
                    const response = await fetch(`/panel/appointments/${id}/price`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ price: newPrice })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        let appt = this.completedAppointments.find(a => a.id === id);
                        if (appt) appt.price = parseFloat(newPrice);

                        let upcoming = this.upcomingAppointments.find(a => a.id === id);
                        if (upcoming) upcoming.price = parseFloat(newPrice);

                        let cancelled = this.cancelledAppointments.find(a => a.id === id);
                        if (cancelled) cancelled.price = parseFloat(newPrice);

                        this.calculateEarnings();

                        Toast.fire({
                            icon: 'success',
                            title: data.message,
                            iconColor: '#7a5555'
                        });
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: data.message || 'Bir hata oluştu.',
                            iconColor: '#ba1a1a'
                        });
                    }
                } catch (error) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Bağlantı hatası.',
                        iconColor: '#ba1a1a'
                    });
                }
            },

            async resetAllAppointments() {
                const result = await Swal.fire({
                    title: 'Tüm Randevuları Sıfırla',
                    text: 'Dikkat! Bu işlem onaylanmış, beklemede veya iptal edilmiş TÜM randevuları ve toplam kazanç verisini veritabanından kalıcı olarak silecektir. Emin misiniz?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ba1a1a',
                    cancelButtonColor: '#7a5555',
                    confirmButtonText: 'Evet, Hepsini Sil',
                    cancelButtonText: 'İptal'
                });

                if (result.isConfirmed) {
                    try {
                        const response = await fetch('{{ route("panel.appointments.reset") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            Toast.fire({
                                icon: 'success',
                                title: data.message,
                                iconColor: '#7a5555'
                            });
                            this.upcomingAppointments = [];
                            this.completedAppointments = [];
                            this.cancelledAppointments = [];
                            this.totalEarnings = 0;
                            setTimeout(() => { window.location.reload(); }, 1500);
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: data.message || 'Bir hata oluştu.',
                                iconColor: '#ba1a1a'
                            });
                        }
                    } catch (error) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Bağlantı hatası.',
                            iconColor: '#ba1a1a'
                        });
                    }
                }
            }
        }));
    });
</script>
@endpush
