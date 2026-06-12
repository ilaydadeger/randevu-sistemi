@extends('layouts.app')

@section('title', "Profilim - " . (auth()->user()->salon_name ?? "L'ART DE L'ONGLE"))

@section('content')
<div x-data="profileManager()">
    <main class="flex-1 px-margin-mobile pt-md pb-[100px] flex flex-col gap-md max-w-[600px] md:max-w-3xl lg:max-w-4xl mx-auto w-full">
        {{-- VIEW 1: Read-Only Header --}}
        <div x-show="!isEditing" x-transition.opacity>
            <section class="flex justify-between items-center mb-sm">
                <div>
                    <h1 class="font-headline-sm text-headline-sm text-on-surface">Bugün,
                        {{ \Carbon\Carbon::today()->locale('tr')->translatedFormat('d M') }}</h1>
                    <p class="font-body-md text-body-md text-on-surface-variant" x-text="pendingApprovals.length + ' Bekleyen Fiyat Onayı'">{{ $pendingApprovals->count() }} Bekleyen Fiyat Onayı</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-secondary-container overflow-hidden border border-outline-variant cursor-pointer hover:opacity-85 transition-opacity"
                    @click="toggleEdit()">
                    <template x-if="userData.profile_photo_path">
                        <img :src="(userData.profile_photo_path.startsWith('http') ? userData.profile_photo_path : '/storage/' + userData.profile_photo_path)" alt="Profile"
                            class="h-full w-full object-cover" />
                    </template>
                </div>
            </section>
        </div>

        {{-- Notifications Section --}}
        @if(isset($unreadNotifications) && $unreadNotifications->count() > 0)
        <div x-show="!isEditing" x-transition.opacity class="flex flex-col gap-2 mb-2">
            @foreach($unreadNotifications as $notification)
                <div id="notif-{{ $notification->id }}" class="bg-error/10 border border-error/20 rounded-xl p-3 flex items-start justify-between gap-3 shadow-sm relative">
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-error text-[20px] mt-0.5">event_busy</span>
                        <div>
                            <p class="font-body-md font-semibold text-on-surface">{{ $notification->data['message'] ?? 'Randevu iptal edildi.' }}</p>
                            @if(isset($notification->data['date']) && isset($notification->data['time']))
                                <p class="text-xs text-on-surface-variant mt-0.5">İptal Edilen Randevu: {{ \Carbon\Carbon::parse($notification->data['date'])->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($notification->data['time'])->format('H:i') }}</p>
                            @endif
                        </div>
                    </div>
                    <button type="button" @click="dismissNotification('{{ $notification->id }}')" class="text-on-surface-variant hover:text-error p-1 rounded-full transition-colors flex shrink-0" title="Bildirimi Kapat">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
            @endforeach
        </div>
        @endif

        {{-- VIEW 2: Edit Header --}}
        <div x-show="isEditing" x-transition.opacity class="flex justify-between items-center mb-sm">
            <div class="flex flex-col gap-xs">
                <h2 class="font-headline-sm text-headline-sm text-on-background">Profil Ayarları</h2>
                <p class="text-sm text-on-surface-variant">Biyografi, profil fotoğrafı ve portföy görsellerinizi
                    güncelleyin.</p>
            </div>
            <button @click="toggleEdit()"
                class="text-primary font-label-caps text-label-caps border border-primary/30 px-4 py-2 rounded-full hover:bg-primary-container/20 transition-colors">
                İPTAL
            </button>
        </div>

        {{-- VIEW 1: Read-Only Profile & Dashboard --}}
        <div x-show="!isEditing" x-transition.opacity class="flex flex-col gap-lg mt-2">

            {{-- Section: Today's Approved Appointments --}}
            <section class="flex flex-col gap-sm">
                <h2 class="font-label-caps text-label-caps text-on-surface-variant mb-base uppercase tracking-widest">Bugünün Randevuları</h2>

                <div id="todayAppointmentsList" class="grid grid-cols-1 gap-base">
                    <template x-for="appointment in todayAppointments" :key="appointment.id">
                        <div class="bg-secondary-fixed/40 rounded-xl p-sm border border-secondary-fixed-dim/20 shadow-sm flex items-center justify-between gap-sm">
                            <div class="flex items-center gap-sm">
                                <div class="bg-primary text-on-primary rounded-lg flex flex-col items-center justify-center px-3 py-1.5 min-w-[60px] shadow-sm">
                                    <span class="font-headline-sm text-base font-bold leading-none" x-text="appointment.time_formatted"></span>
                                </div>
                                <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                            <h3 class="font-body-md text-body-md font-semibold text-on-surface" x-text="appointment.client_name"></h3>
                                            <span x-show="appointment.service_type_label" 
                                                  class="px-2 py-0.5 bg-primary-container text-on-primary-container text-[10px] rounded-full font-bold uppercase tracking-wider" 
                                                  x-text="appointment.service_type_label"></span>
                                        </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-sm shrink-0">
                                <span class="font-headline-sm text-base text-primary" x-text="'₺' + appointment.price"></span>
                                <template x-if="appointment.image_url">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden border border-outline-variant/30 cursor-pointer" @click="openImageModal(appointment.image_url)">
                                        <img alt="Nail Reference" class="w-full h-full object-cover" :src="appointment.image_url" x-on:error="$el.parentElement.style.display = 'none'" />
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    
                    <template x-if="todayAppointments.length === 0">
                        <div class="bg-surface-container-low rounded-xl p-md text-center text-on-surface-variant border border-outline-variant/30">
                            Bugün için onaylanmış randevu bulunmuyor.
                        </div>
                    </template>
                </div>
            </section>

            {{-- Section: Pending Approvals --}}
            <section class="flex flex-col gap-sm">
                <h2 class="font-label-caps text-label-caps text-on-surface-variant mb-base uppercase tracking-widest">Fiyat Onayları</h2>

                <div id="pendingApprovalsList" class="grid grid-cols-1 gap-base">
                    <template x-for="appointment in pendingApprovals" :key="appointment.id">
                        <div :id="'appointment-card-' + appointment.id"
                            class="bg-surface-container-low rounded-xl p-sm border border-surface-container-high shadow-sm relative overflow-hidden transition-all duration-500">
                            <div class="flex justify-between items-start mb-sm">
                                <div class="flex items-center gap-sm">
                                    <div
                                        class="h-10 w-10 rounded-full bg-tertiary-container flex items-center justify-center text-on-tertiary-container font-headline-sm text-headline-sm font-bold"
                                        x-text="appointment.client_name.substring(0, 1)">
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-body-md text-body-md font-semibold text-on-surface" x-text="appointment.client_name"></h3>
                                            <span x-show="appointment.service_type_label" 
                                                  class="px-2 py-0.5 bg-primary-container text-on-primary-container text-[10px] rounded-full font-bold uppercase tracking-wider" 
                                                  x-text="appointment.service_type_label"></span>
                                        </div>
                                        <p class="font-label-caps text-label-caps text-secondary">
                                            <span x-text="appointment.date_formatted"></span> - 
                                            <span x-text="appointment.time_formatted"></span>
                                        </p>
                                    </div>
                                </div>
                                <span class="font-headline-sm text-headline-sm text-primary" x-text="'₺' + appointment.price"></span>
                            </div>
                            <div x-show="appointment.image_url" class="mb-sm rounded-lg overflow-hidden h-24 bg-surface-variant flex items-center justify-center border border-outline-variant/30 cursor-pointer" @click="openImageModal(appointment.image_url)">
                                <img alt="Nail Art Reference" class="w-full h-full object-cover pointer-events-none" :src="appointment.image_url || ''" x-on:error="$el.parentElement.style.display = 'none'" />
                            </div>
                            <div class="flex gap-sm">
                                <button type="button" @click="updateAppointmentStatus(appointment.id, 'cancelled')"
                                    class="flex-1 py-2 px-4 rounded-full bg-error-container text-on-error-container font-label-caps text-label-caps hover:opacity-80 transition-opacity flex justify-center items-center gap-2">
                                    <span class="material-symbols-outlined text-sm pointer-events-none">cancel</span>
                                    Reddet
                                </button>
                                <button type="button" @click="openApproveModal(appointment.id)"
                                    class="flex-1 py-2 px-4 rounded-full bg-primary text-on-primary font-label-caps text-label-caps hover:bg-surface-tint transition-colors flex justify-center items-center gap-2">
                                    <span class="material-symbols-outlined text-sm pointer-events-none">check_circle</span>
                                    Onayla
                                </button>
                            </div>
                        </div>
                    </template>


                    <template x-if="pendingApprovals.length === 0">
                        <div class="bg-surface-container-low rounded-xl p-md text-center text-on-surface-variant border border-outline-variant/30">
                            Onay bekleyen talep bulunmuyor.
                        </div>
                    </template>
                </div>
            </section>

            <div class="mt-4 pt-4 border-t border-surface-container-highest text-center">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="text-error font-label-caps text-label-caps flex items-center justify-center gap-2 mx-auto hover:bg-error-container/20 px-4 py-2 rounded-full transition-colors">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                        ÇIKIŞ YAP
                    </button>
                </form>
            </div>
        </div>

        {{-- VIEW 2: Edit Profile Form --}}
        <section x-show="isEditing" x-transition.opacity style="display: none;"
            class="bg-surface-container-lowest rounded-xl p-md border border-outline-variant/30 shadow-sm mt-2">
            <form @submit.prevent="submitProfileForm" class="space-y-md">
                @csrf

                {{-- Profil Fotoğrafı --}}
                <div class="flex flex-col items-center gap-sm mb-6">
                    <div class="w-24 h-24 rounded-full overflow-hidden bg-surface-variant flex items-center justify-center relative group cursor-pointer border-2 border-surface-container-highest"
                        @click="$refs.photoInput.click()">
                        <template
                            x-if="formData.preview_photo || (userData.profile_photo_path && !formData.remove_profile_photo)">
                            <img :src="formData.preview_photo || (userData.profile_photo_path.startsWith('http') ? userData.profile_photo_path : '/storage/' + userData.profile_photo_path)"
                                alt="Profil Fotoğrafı" class="w-full h-full object-cover">
                        </template>
                        <template
                            x-if="!formData.preview_photo && (!userData.profile_photo_path || formData.remove_profile_photo)">
                            <span class="material-symbols-outlined text-outline">add_a_photo</span>
                        </template>
                    </div>
                    <input type="file" x-ref="photoInput" name="profile_photo" class="hidden" accept="image/*"
                        @change="previewPhoto($event)">
                    <span class="text-xs text-on-surface-variant">Fotoğraf yüklemek/değiştirmek için tıklayın</span>

                    <template x-if="userData.profile_photo_path || formData.preview_photo">
                        <button type="button" @click="removeProfilePhoto()"
                            class="text-xs text-error font-semibold hover:underline">Mevcut Fotoğrafı Kaldır</button>
                    </template>
                </div>

                {{-- Ad Soyad --}}
                <div class="space-y-xs">
                    <label class="font-label-caps text-label-caps text-on-surface-variant">AD SOYAD</label>
                    <input type="text" x-model="formData.name"
                        class="w-full bg-surface-container-low border-0 border-b-2 border-surface-variant focus:border-primary focus:ring-0 px-4 py-3 text-on-surface rounded-t-DEFAULT"
                        placeholder="İsminizin görünmesini istemiyorsanız boş bırakabilirsiniz.">
                </div>

                {{-- Salon / İşletme Adı --}}
                <div class="space-y-xs">
                    <label class="font-label-caps text-label-caps text-on-surface-variant">SALON / İŞLETME ADI</label>
                    <input type="text" x-model="formData.salon_name"
                        class="w-full bg-surface-container-low border-0 border-b-2 border-surface-variant focus:border-primary focus:ring-0 px-4 py-3 text-on-surface rounded-t-DEFAULT"
                        placeholder="Salon / İşletme adı girin">
                </div>

                {{-- Bio --}}
                <div class="space-y-xs">
                    <label class="font-label-caps text-label-caps text-on-surface-variant">BİYOGRAFİ (VİTRİN YAZISI)</label>
                    <textarea x-model="formData.bio" rows="3"
                        class="w-full bg-surface-container-low border-0 border-b-2 border-surface-variant focus:border-primary focus:ring-0 px-4 py-3 text-on-surface rounded-t-DEFAULT"
                        placeholder="Uzmanlık alanlarınızdan bahsedin..."></textarea>
                </div>

                {{-- Adres --}}
                <div class="space-y-xs">
                    <label class="font-label-caps text-label-caps text-on-surface-variant">AÇIK ADRES</label>
                    <textarea x-model="formData.address" rows="2"
                        class="w-full bg-surface-container-low border-0 border-b-2 border-surface-variant focus:border-primary focus:ring-0 px-4 py-3 text-on-surface rounded-t-DEFAULT"
                        placeholder="Randevusu onaylanan müşterilerin görmesi için açık adresiniz."></textarea>
                </div>

                {{-- Portföy Görünürlüğü --}}
                <div class="flex items-center justify-between p-sm bg-surface-container-low rounded-xl border border-outline-variant/30 my-4">
                    <div class="flex flex-col gap-xxs pr-4">
                        <span class="font-body-md text-body-md text-on-surface font-semibold">Portföyü Ana Sayfada Göster</span>
                        <span class="text-xs text-on-surface-variant">Müşterilerin ana sayfanızda portföy alanını ve görsellerini görmesini sağlar.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" x-model="formData.show_portfolio" class="sr-only peer">
                        <div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                {{-- Portföy Görselleri --}}
                <div class="space-y-sm pt-2">
                    <label class="font-label-caps text-label-caps text-on-surface-variant">PORTFÖY GÖRSELLERİ (MAKS. 3
                        ADET)</label>
                    <div class="grid grid-cols-3 gap-sm">
                        <!-- Image 1 -->
                        <div class="flex flex-col items-center gap-xs">
                            <div class="w-full h-24 rounded-lg overflow-hidden bg-surface-variant border border-outline-variant/30 relative flex items-center justify-center cursor-pointer group"
                                @click="$refs.portfolio1Input.click()">
                                <template
                                    x-if="formData.preview_portfolio_1 || (userData.portfolio_image_1 && !formData.remove_portfolio_image_1)">
                                    <img :src="formData.preview_portfolio_1 || (userData.portfolio_image_1.startsWith('http') ? userData.portfolio_image_1 : '/storage/' + userData.portfolio_image_1)"
                                        class="w-full h-full object-cover">
                                </template>
                                <template
                                    x-if="!formData.preview_portfolio_1 && (!userData.portfolio_image_1 || formData.remove_portfolio_image_1)">
                                    <span class="material-symbols-outlined text-outline text-lg">add</span>
                                </template>
                            </div>
                            <input type="file" x-ref="portfolio1Input" class="hidden" accept="image/*"
                                @change="previewPortfolio($event, 1)">
                            <template x-if="userData.portfolio_image_1 || formData.preview_portfolio_1">
                                <button type="button" @click="removePortfolioImage(1)"
                                    class="text-xs text-error font-semibold hover:underline mt-1">Kaldır</button>
                            </template>
                        </div>

                        <!-- Image 2 -->
                        <div class="flex flex-col items-center gap-xs">
                            <div class="w-full h-24 rounded-lg overflow-hidden bg-surface-variant border border-outline-variant/30 relative flex items-center justify-center cursor-pointer group"
                                @click="$refs.portfolio2Input.click()">
                                <template
                                    x-if="formData.preview_portfolio_2 || (userData.portfolio_image_2 && !formData.remove_portfolio_image_2)">
                                    <img :src="formData.preview_portfolio_2 || (userData.portfolio_image_2.startsWith('http') ? userData.portfolio_image_2 : '/storage/' + userData.portfolio_image_2)"
                                        class="w-full h-full object-cover">
                                </template>
                                <template
                                    x-if="!formData.preview_portfolio_2 && (!userData.portfolio_image_2 || formData.remove_portfolio_image_2)">
                                    <span class="material-symbols-outlined text-outline text-lg">add</span>
                                </template>
                            </div>
                            <input type="file" x-ref="portfolio2Input" class="hidden" accept="image/*"
                                @change="previewPortfolio($event, 2)">
                            <template x-if="userData.portfolio_image_2 || formData.preview_portfolio_2">
                                <button type="button" @click="removePortfolioImage(2)"
                                    class="text-xs text-error font-semibold hover:underline mt-1">Kaldır</button>
                            </template>
                        </div>

                        <!-- Image 3 -->
                        <div class="flex flex-col items-center gap-xs">
                            <div class="w-full h-24 rounded-lg overflow-hidden bg-surface-variant border border-outline-variant/30 relative flex items-center justify-center cursor-pointer group"
                                @click="$refs.portfolio3Input.click()">
                                <template
                                    x-if="formData.preview_portfolio_3 || (userData.portfolio_image_3 && !formData.remove_portfolio_image_3)">
                                    <img :src="formData.preview_portfolio_3 || (userData.portfolio_image_3.startsWith('http') ? userData.portfolio_image_3 : '/storage/' + userData.portfolio_image_3)"
                                        class="w-full h-full object-cover">
                                </template>
                                <template
                                    x-if="!formData.preview_portfolio_3 && (!userData.portfolio_image_3 || formData.remove_portfolio_image_3)">
                                    <span class="material-symbols-outlined text-outline text-lg">add</span>
                                </template>
                            </div>
                            <input type="file" x-ref="portfolio3Input" class="hidden" accept="image/*"
                                @change="previewPortfolio($event, 3)">
                            <template x-if="userData.portfolio_image_3 || formData.preview_portfolio_3">
                                <button type="button" @click="removePortfolioImage(3)"
                                    class="text-xs text-error font-semibold hover:underline mt-1">Kaldır</button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="pt-sm">
                    <button type="submit" :disabled="isLoading"
                        class="w-full bg-gradient-to-r from-primary to-[#8a6565] text-on-primary font-label-caps text-label-caps py-4 rounded-full shadow-md hover:opacity-90 transition-opacity flex justify-center items-center gap-2 disabled:opacity-50">
                        <span x-show="isLoading"
                            class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                        DEĞİŞİKLİKLERİ KAYDET
                    </button>
                </div>

                <div class="pt-2 text-center">
                    <button type="button" @click="toggleEdit()"
                        class="text-on-surface-variant font-label-caps text-label-caps hover:underline border-none bg-transparent mb-4">
                        İPTAL ET
                    </button>
                </div>
            </form>
        </section>
    </main>


    {{-- Approve / Reschedule Modal --}}
    <template x-teleport="body">
    <div x-show="approveModalOpen" style="display:none" class="fixed inset-0 z-[140] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-transition.opacity>
        <div class="relative bg-surface-container-lowest rounded-2xl w-full max-w-lg shadow-xl overflow-y-auto max-h-[90vh]" @click.away="approveModalOpen = false">
            <div class="p-4 border-b border-outline-variant/30 flex items-center justify-between sticky top-0 bg-surface-container-lowest z-10">
                <h3 class="font-headline-sm text-headline-sm text-on-surface">Randevuyu Onayla</h3>
                <button @click="approveModalOpen = false" class="text-on-surface-variant hover:text-error transition-colors p-1 rounded-full">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            
            <div class="p-6 space-y-6">
                <p class="text-xs text-on-surface-variant">
                    Müşterinin seçtiği tarih ve saati veya fiyatı değiştirebilirsiniz. Değiştirirseniz müşteriye yeni tarihli bir onay iletilecektir.
                </p>
                
                {{-- Price Input --}}
                <div class="flex items-center gap-2 bg-surface-container-low p-3 rounded-xl border border-outline-variant/30">
                    <span class="font-bold text-xs text-on-surface font-label-caps shrink-0">FİYAT:</span>
                    <div class="relative flex-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant font-bold text-sm">₺</span>
                        <input x-model="activeApprovePrice" type="number" min="0" step="any" 
                            class="w-full bg-surface-container-lowest border border-outline-variant/30 rounded-lg pl-7 pr-3 py-2 text-sm text-on-surface focus:outline-none focus:border-primary">
                    </div>
                </div>

                {{-- Selected Slot Preview --}}
                <div class="p-sm bg-primary-container/20 border border-primary/20 rounded-xl flex items-center gap-2 text-primary font-medium text-xs">
                    <span class="material-symbols-outlined text-[18px]">event_available</span>
                    <span>Randevu Zamanı: <span class="font-bold" x-text="formatDate(selectedDate) + ' - Saat ' + selectedTime"></span></span>
                </div>

                {{-- Client Uploaded Image Preview --}}
                <template x-if="activeApproveImage">
                    <div class="rounded-xl overflow-hidden bg-surface-variant border border-outline-variant/30 flex items-center justify-center cursor-pointer max-h-48" @click="openImageModal(activeApproveImage)">
                        <img :src="activeApproveImage" class="w-full h-full object-contain" alt="Referans Görseli">
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity bg-black/20">
                            <span class="material-symbols-outlined text-white text-3xl drop-shadow-md">zoom_in</span>
                        </div>
                    </div>
                </template>

                {{-- Mini Calendar --}}
                <div class="bg-surface-container-low rounded-xl p-4 border border-outline-variant/30">
                    <!-- Month / Year -->
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
                    <div class="grid grid-cols-7 gap-1 text-center font-body-md text-sm">
                        <template x-for="day in daysInGrid" :key="day.dateStr">
                            <div @click="selectDay(day)" :class="{
                                        'text-on-surface-variant opacity-30 cursor-not-allowed': !day.isSelectable,
                                        'rounded-full bg-error/10 text-error/60 border border-error/20 line-through cursor-not-allowed': day.isSelectable && isDayFullyBooked(day.dateStr) && selectedDate !== day.dateStr,
                                        'rounded-full hover:bg-surface-container cursor-pointer transition-colors': day.isSelectable && !isDayFullyBooked(day.dateStr) && selectedDate !== day.dateStr,
                                        'rounded-full bg-primary text-on-primary shadow-sm cursor-pointer font-semibold': day.isSelectable && selectedDate === day.dateStr
                                    }"
                                class="py-1 relative select-none flex items-center justify-center w-8 h-8 mx-auto transition-colors">
                                <span x-text="day.dayNum"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Time Slots --}}
                    <div class="mt-4 border-t border-outline-variant/20 pt-4" x-show="selectedDate">
                        <div class="font-label-caps text-[10px] text-on-surface-variant mb-3 font-bold tracking-wider"
                            x-text="formatFriendlySelectedDate() + ' UYGUN SAATLER'"></div>

                        <div class="flex flex-wrap gap-2">
                            <template x-for="slot in getAvailableSlotsForSelectedDate()" :key="slot.key">
                                <button type="button"
                                    @click="if (slot.isAvailable) { selectedTime = slot.hour; activeSlotKey = slot.key; }"
                                    :disabled="!slot.isAvailable && selectedTime !== slot.hour" 
                                    :class="{
                                            'bg-surface-variant/20 text-on-surface-variant/30 border border-outline-variant/10 cursor-not-allowed opacity-50': !slot.isAvailable && selectedTime !== slot.hour,
                                            'border border-outline-variant font-body-md text-xs text-on-surface hover:bg-surface-container transition-colors': slot.isAvailable && selectedTime !== slot.hour,
                                            'bg-secondary text-on-secondary font-body-md text-xs shadow-sm transition-colors': selectedTime === slot.hour
                                        }"
                                    class="px-3 py-1.5 rounded-full transition-colors whitespace-nowrap">
                                    <span x-text="formatTimeLabel(slot.hour)"></span>
                                </button>
                            </template>
                            <template x-if="getAvailableSlotsForSelectedDate().filter(s => s.isAvailable || s.hour === selectedTime).length === 0">
                                <div class="text-[10px] text-on-surface-variant italic">Bu tarihte uygun saat yok.</div>
                            </template>
                        </div>
                    </div>
                </div>

            </div>
            
            <div class="p-4 border-t border-outline-variant/30 flex gap-2 justify-end bg-surface-container-lowest sticky bottom-0 z-10">
                <button @click="approveModalOpen = false" class="px-6 py-2 rounded-full font-label-caps text-xs bg-surface-container text-on-surface hover:bg-surface-container-high transition-colors">Vazgeç</button>
                <button @click="confirmApprove()" class="px-6 py-2 rounded-full font-label-caps text-xs bg-primary text-on-primary hover:bg-surface-tint transition-colors flex gap-2 items-center">
                    <span x-show="isApproving" class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span>
                    Kaydet
                </button>
            </div>
        </div>
    </div>
    </template>

    {{-- Fullscreen Design Image Modal --}}
    <template x-teleport="body">
    <div x-show="imageModalOpen" style="display:none" class="fixed inset-0 z-[150] flex items-center justify-center p-6 bg-black/85 backdrop-blur-sm" x-transition.opacity>
        <div class="relative w-full max-w-2xl" @click.away="imageModalOpen = false">
            <button @click="imageModalOpen = false" class="absolute -top-12 right-0 text-white hover:opacity-85 p-2 bg-white/20 rounded-full flex items-center justify-center w-10 h-10">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
            <img :src="modalImageUrl" class="w-full max-h-[80vh] object-contain rounded-2xl shadow-2xl block mx-auto">
        </div>
    </div>
    </template>
</div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
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

        document.addEventListener('alpine:init', () => {
            Alpine.data('profileManager', () => ({
                isEditing: false,
                isLoading: false,
                imageModalOpen: false,
                modalImageUrl: '',

                isApproving: false,
                approveModalOpen: false,
                activeApproveId: null,
                activeApprovePrice: 0,
                activeApproveImage: null,
                
                blockedSlots: {!! json_encode($blockedSlots) !!},
                occupiedSlots: {!! json_encode($occupiedSlots) !!},
                hours: {!! json_encode($hours) !!},
                todayStr: '{{ now()->toDateString() }}',

                selectedDate: '',
                selectedTime: '',
                activeSlotKey: '',

                currentYear: null,
                currentMonth: null,
                monthName: '',
                daysInGrid: [],

                pendingApprovals: {!! json_encode($pendingApprovals->map(function($a) {
                    return [
                        'id'             => $a->id,
                        'client_name'    => str_replace(' (Protez Tırnak)', '', $a->client_name),
                        'tracking_code'  => $a->tracking_code,
                        'date'           => $a->appointment_date,
                        'time'           => $a->appointment_time,
                        'date_formatted' => \Carbon\Carbon::parse($a->appointment_date)->locale('tr')->translatedFormat('d M, Y'),
                        'time_formatted' => \Carbon\Carbon::parse($a->appointment_time)->format('H:i'),
                        'price'          => floatval($a->estimated_price),
                        'service_type_label' => $a->service_type === 'yapim' ? 'Yapım' : ($a->service_type === 'cikarma' ? 'Çıkarma' : ''),
                        'image_url'      => $a->image_path ? (str_starts_with($a->image_path, 'http') ? $a->image_path : asset('storage/' . $a->image_path)) : null,
                    ];
                })) !!},
                todayAppointments: {!! json_encode($todayAppointments->map(function($a) {
                    return [
                        'id'             => $a->id,
                        'client_name'    => str_replace(' (Protez Tırnak)', '', $a->client_name),
                        'tracking_code'  => $a->tracking_code,
                        'time_formatted' => \Carbon\Carbon::parse($a->appointment_time)->format('H:i'),
                        'price'          => floatval($a->estimated_price),
                        'service_type_label' => $a->service_type === 'yapim' ? 'Yapım' : ($a->service_type === 'cikarma' ? 'Çıkarma' : ''),
                        'image_url'      => $a->image_path ? (str_starts_with($a->image_path, 'http') ? $a->image_path : asset('storage/' . $a->image_path)) : null,
                    ];
                })) !!},

                init() {
                    setInterval(() => {
                        this.fetchUpdates();
                    }, 5000);
                },

                openImageModal(url) {
                    this.modalImageUrl = url;
                    this.imageModalOpen = true;
                },

                async fetchUpdates() {
                    try {
                        const response = await fetch('{{ route("panel.api.updates") }}');
                        if (response.ok) {
                            const contentType = response.headers.get("content-type");
                            if (contentType && contentType.indexOf("application/json") !== -1) {
                                const data = await response.json();
                                if (data.success) {
                                    this.pendingApprovals = data.pendingApprovals;
                                    this.todayAppointments = data.todayAppointments;
                                }
                            } else {
                                // Gelen yanıt JSON değilse (muhtemelen oturum süresi dolduğu için login sayfasına yönlendirildi)
                                window.location.reload();
                            }
                        }
                    } catch (e) {
                        console.error("Updates polling error: ", e);
                    }
                },

                userData: {
                    name: {!! json_encode($user->name) !!},
                    salon_name: {!! json_encode($user->salon_name ?? "") !!},
                    bio: {!! json_encode($user->bio ?? "") !!},
                    address: {!! json_encode($user->address ?? "") !!},
                    profile_photo_path: {!! json_encode($user->profile_photo_path ?? "") !!},
                    portfolio_image_1: {!! json_encode($user->portfolio_image_1 ?? "") !!},
                    portfolio_image_2: {!! json_encode($user->portfolio_image_2 ?? "") !!},
                    portfolio_image_3: {!! json_encode($user->portfolio_image_3 ?? "") !!},
                    show_portfolio: {{ $user->show_portfolio ? 'true' : 'false' }}
                },
                formData: {
                    name: {!! json_encode($user->name) !!},
                    salon_name: {!! json_encode($user->salon_name ?? "") !!},
                    bio: {!! json_encode($user->bio ?? "") !!},
                    address: {!! json_encode($user->address ?? "") !!},
                    show_portfolio: {{ $user->show_portfolio ? 'true' : 'false' }},
                    preview_photo: null,
                    file: null,
                    remove_profile_photo: false,
                    preview_portfolio_1: null,
                    file_portfolio_1: null,
                    remove_portfolio_image_1: false,
                    preview_portfolio_2: null,
                    file_portfolio_2: null,
                    remove_portfolio_image_2: false,
                    preview_portfolio_3: null,
                    file_portfolio_3: null,
                    remove_portfolio_image_3: false
                },

                previewPhoto(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.formData.file = file;
                        this.formData.preview_photo = URL.createObjectURL(file);
                        this.formData.remove_profile_photo = false;
                    }
                },

                removeProfilePhoto() {
                    this.formData.file = null;
                    this.formData.preview_photo = null;
                    this.formData.remove_profile_photo = true;
                },

                previewPortfolio(event, num) {
                    const file = event.target.files[0];
                    if (file) {
                        this.formData['file_portfolio_' + num] = file;
                        this.formData['preview_portfolio_' + num] = URL.createObjectURL(file);
                        this.formData['remove_portfolio_image_' + num] = false;
                    }
                },

                removePortfolioImage(num) {
                    this.formData['file_portfolio_' + num] = null;
                    this.formData['preview_portfolio_' + num] = null;
                    this.formData['remove_portfolio_image_' + num] = true;
                },

                toggleEdit() {
                    this.isEditing = !this.isEditing;
                    if (!this.isEditing) {
                        // Reset form to saved data if cancelled
                        this.formData.name = this.userData.name;
                        this.formData.salon_name = this.userData.salon_name;
                        this.formData.bio = this.userData.bio;
                        this.formData.address = this.userData.address;
                        this.formData.show_portfolio = this.userData.show_portfolio;
                        this.formData.preview_photo = null;
                        this.formData.file = null;
                        this.formData.remove_profile_photo = false;
                        for (let i = 1; i <= 3; i++) {
                            this.formData['preview_portfolio_' + i] = null;
                            this.formData['file_portfolio_' + i] = null;
                            this.formData['remove_portfolio_image_' + i] = false;
                        }
                    }
                },

                async submitProfileForm() {
                    this.isLoading = true;

                    const formPayload = new FormData();
                    formPayload.append('name', this.formData.name || '');
                    formPayload.append('salon_name', this.formData.salon_name || '');
                    formPayload.append('bio', this.formData.bio || '');
                    formPayload.append('address', this.formData.address || '');
                    formPayload.append('show_portfolio', this.formData.show_portfolio ? '1' : '0');

                    if (this.formData.remove_profile_photo) {
                        formPayload.append('remove_profile_photo', '1');
                    } else if (this.formData.file) {
                        formPayload.append('profile_photo', this.formData.file);
                    }

                    for (let i = 1; i <= 3; i++) {
                        if (this.formData['remove_portfolio_image_' + i]) {
                            formPayload.append('remove_portfolio_image_' + i, '1');
                        } else if (this.formData['file_portfolio_' + i]) {
                            formPayload.append('portfolio_image_' + i, this.formData['file_portfolio_' + i]);
                        }
                    }

                    try {
                        const response = await fetch("{{ route('panel.profile.update') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formPayload
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.userData.name = this.formData.name;
                            this.userData.salon_name = this.formData.salon_name;
                            this.userData.bio = this.formData.bio;
                            this.userData.address = this.formData.address;
                            this.userData.show_portfolio = !!data.user.show_portfolio;
                            this.userData.profile_photo_path = data.user.profile_photo_path;
                            this.userData.portfolio_image_1 = data.user.portfolio_image_1;
                            this.userData.portfolio_image_2 = data.user.portfolio_image_2;
                            this.userData.portfolio_image_3 = data.user.portfolio_image_3;

                            this.isEditing = false;

                            // Clear temp files
                            this.formData.preview_photo = null;
                            this.formData.file = null;
                            this.formData.remove_profile_photo = false;
                            for (let i = 1; i <= 3; i++) {
                                this.formData['preview_portfolio_' + i] = null;
                                this.formData['file_portfolio_' + i] = null;
                                this.formData['remove_portfolio_image_' + i] = false;
                            }

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
                    } finally {
                        this.isLoading = false;
                    }
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
                        days.push({ dateStr: dateStr, dayNum: d, isSelectable: false });
                    }

                    const today = new Date(this.todayStr);
                    const maxAllowedDate = new Date(today);
                    maxAllowedDate.setDate(today.getDate() + 90);

                    const totalDays = lastDayOfMonth.getDate();
                    for (let d = 1; d <= totalDays; d++) {
                        const dateStr = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                        const dateObj = new Date(this.currentYear, this.currentMonth, d);
                        const isSelectable = dateObj >= new Date(today.getFullYear(), today.getMonth(), today.getDate()) && dateObj <= new Date(maxAllowedDate.getFullYear(), maxAllowedDate.getMonth(), maxAllowedDate.getDate());
                        days.push({ dateStr: dateStr, dayNum: d, isSelectable: isSelectable });
                    }

                    const totalCells = Math.ceil(days.length / 7) * 7;
                    const leadingDaysNeeded = totalCells - days.length;
                    for (let d = 1; d <= leadingDaysNeeded; d++) {
                        let nm = this.currentMonth + 1;
                        let ny = this.currentYear;
                        if (nm > 11) { nm = 0; ny++; }
                        const dateStr = `${ny}-${String(nm + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                        days.push({ dateStr: dateStr, dayNum: d, isSelectable: false });
                    }

                    this.daysInGrid = days;
                },

                prevMonth() {
                    this.currentMonth--;
                    if (this.currentMonth < 0) {
                        this.currentMonth = 11;
                        this.currentYear--;
                    }
                    this.generateGrid();
                },

                nextMonth() {
                    this.currentMonth++;
                    if (this.currentMonth > 11) {
                        this.currentMonth = 0;
                        this.currentYear++;
                    }
                    this.generateGrid();
                },

                shouldShowPrevArrow() {
                    const viewDate = new Date(this.currentYear, this.currentMonth, 1);
                    const today = new Date(this.todayStr);
                    const limitDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    return viewDate > limitDate;
                },

                shouldShowNextArrow() {
                    return true;
                },

                selectDay(day) {
                    if (!day.isSelectable) return;
                    if (this.isDayFullyBooked(day.dateStr) && this.selectedDate !== day.dateStr) return;
                    this.selectedDate = day.dateStr;
                    this.selectedTime = '';
                    this.activeSlotKey = '';
                },

                isDayFullyBooked(dateStr) {
                    const isAnyAvailable = this.hours.some(hour => {
                        if (dateStr === this.activeApproveDate && hour === this.activeApproveTime) {
                            return true;
                        }
                        const key = dateStr + '_' + hour;
                        const isBlocked = !!this.blockedSlots[key];
                        const isOccupied = !!this.occupiedSlots[key];
                        return !isBlocked && !isOccupied;
                    });
                    return !isAnyAvailable;
                },

                getAvailableSlotsForSelectedDate() {
                    if (!this.selectedDate) return [];
                    return this.hours.map(hour => {
                        const key = this.selectedDate + '_' + hour;
                        const isBlocked = !!this.blockedSlots[key];
                        const isOccupied = !!this.occupiedSlots[key];
                        
                        let isAvailable = !isBlocked && !isOccupied;
                        
                        // If it's the current appointment's slot, it IS available
                        if (this.selectedDate === this.activeApproveDate && hour === this.activeApproveTime) {
                            isAvailable = true;
                        }
                        
                        if (this.selectedDate === this.todayStr) {
                            const now = new Date();
                            const [h, m] = hour.split(':');
                            const slotTime = new Date();
                            slotTime.setHours(parseInt(h), parseInt(m), 0, 0);
                            
                            const bufferTime = new Date(now.getTime() + 60*60*1000);
                            if (slotTime <= bufferTime) {
                                isAvailable = false;
                            }
                        }
                        
                        return { key, hour, isAvailable };
                    });
                },

                formatFriendlySelectedDate() {
                    if (!this.selectedDate) return '';
                    const d = new Date(this.selectedDate);
                    return d.toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric' }).toUpperCase();
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric' });
                },

                activeApproveDate: '',
                activeApproveTime: '',

                formatTimeLabel(hour) {
                    return hour;
                },

                openApproveModal(id) {
                    const appointment = this.pendingApprovals.find(a => a.id == id);
                    if (!appointment) return;
                    this.activeApproveId = appointment.id;
                    this.activeApprovePrice = appointment.price;
                    this.selectedDate = appointment.date;
                    this.selectedTime = appointment.time;
                    this.activeApproveImage = appointment.image_url;
                    this.activeApproveDate = appointment.date;
                    this.activeApproveTime = appointment.time;
                    
                    if (appointment.date) {
                        const parts = appointment.date.split('-');
                        if(parts.length >= 3) {
                            this.currentYear = parseInt(parts[0], 10);
                            this.currentMonth = parseInt(parts[1], 10) - 1;
                        } else {
                            const apptDate = new Date(appointment.date);
                            this.currentYear = apptDate.getFullYear() || new Date().getFullYear();
                            this.currentMonth = (apptDate.getMonth() >= 0) ? apptDate.getMonth() : new Date().getMonth();
                        }
                    } else {
                        const today = new Date();
                        this.currentYear = today.getFullYear();
                        this.currentMonth = today.getMonth();
                    }
                    
                    this.generateGrid();
                    this.approveModalOpen = true;
                },

                async confirmApprove() {
                    if(this.activeApprovePrice === '' || isNaN(this.activeApprovePrice) || parseFloat(this.activeApprovePrice) < 0) {
                        Toast.fire({ icon: 'error', title: 'Lütfen geçerli bir fiyat girin.' });
                        return;
                    }
                    if(!this.selectedDate || !this.selectedTime) {
                        Toast.fire({ icon: 'error', title: 'Lütfen tarih ve saat seçin.' });
                        return;
                    }
                    
                    this.isApproving = true;
                    const id = this.activeApproveId;
                    try {
                        const payload = { 
                            status: 'approved',
                            price: parseFloat(this.activeApprovePrice),
                            new_date: this.selectedDate,
                            new_time: this.selectedTime
                        };

                        const response = await fetch(`/panel/appointments/${id}/status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();
                        
                        if (response.ok && data.success) {
                            Toast.fire({ icon: 'success', title: data.message });
                            const card = document.getElementById('appointment-card-' + id);
                            if (card) {
                                card.style.opacity = '0';
                                card.style.transform = 'scale(0.95)';
                                setTimeout(() => card.remove(), 500);
                            }
                            this.pendingApprovals = this.pendingApprovals.filter(a => a.id !== id);
                            this.fetchUpdates();
                            this.approveModalOpen = false;
                        } else {
                            Toast.fire({ icon: 'error', title: data.message || 'Hata oluştu.' });
                        }
                    } catch (error) {
                        Toast.fire({ icon: 'error', title: 'Bağlantı hatası.' });
                    } finally {
                        this.isApproving = false;
                    }
                },

                async updateAppointmentStatus(id, status) {
                    const appointment = this.pendingApprovals.find(a => a.id === id);
                    if (!appointment) return;

                    if (status === 'cancelled') {
                        const confirmCancel = await Swal.fire({
                            title: 'Randevuyu Reddet',
                            text: 'Bu randevu talebini reddetmek istediğinize emin misiniz?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Evet, Reddet',
                            cancelButtonText: 'Vazgeç',
                            customClass: {
                                popup: 'rounded-2xl border border-outline-variant/30 bg-surface-container-lowest text-on-surface font-body-md shadow-xl',
                                title: 'text-on-surface font-headline-sm !pt-4',
                                confirmButton: 'bg-error-container text-on-error-container rounded-full px-6 py-2.5 font-label-caps text-xs font-bold hover:opacity-85 transition-opacity mx-1',
                                cancelButton: 'bg-surface-container text-on-surface rounded-full px-6 py-2.5 font-label-caps text-xs font-bold hover:bg-surface-container-high transition-colors mx-1'
                            },
                            buttonsStyling: false
                        });

                        if (!confirmCancel.isConfirmed) {
                            this.isApproving = null;
                            return;
                        }
                    }

                    let finalPrice = null;
                    if (status === 'approved') {
                        const { value: priceResult } = await Swal.fire({
                            title: 'Randevuyu Onayla',
                            html: `
                                <div class="text-left font-body-md space-y-3 mt-3">
                                    <p class="text-xs text-on-surface-variant">Randevuyu onaylıyorsunuz. Yapay zekanın oluşturduğu tahmini fiyatı aşağıdan düzenleyebilirsiniz:</p>
                                    <div class="flex items-center gap-2 bg-surface-container-low p-3 rounded-xl border border-outline-variant/30">
                                        <span class="font-bold text-xs text-on-surface font-label-caps shrink-0">FİYAT:</span>
                                        <div class="relative flex-1">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant font-bold text-sm">₺</span>
                                            <input id="swal-price-input" type="number" min="0" step="any" 
                                                class="w-full bg-surface-container-lowest border border-outline-variant/30 rounded-lg pl-7 pr-3 py-2 text-sm text-on-surface focus:outline-none focus:border-primary" 
                                                value="${appointment.price}">
                                        </div>
                                    </div>
                                </div>
                            `,
                            showCancelButton: true,
                            confirmButtonText: 'Onayla',
                            cancelButtonText: 'Vazgeç',
                            customClass: {
                                popup: 'rounded-2xl border border-outline-variant/30 bg-surface-container-lowest text-on-surface font-body-md shadow-xl',
                                title: 'text-on-surface font-headline-sm !pt-4',
                                confirmButton: 'bg-primary text-on-primary rounded-full px-6 py-2.5 font-label-caps text-xs font-bold hover:bg-surface-tint transition-colors mx-1',
                                cancelButton: 'bg-surface-container text-on-surface rounded-full px-6 py-2.5 font-label-caps text-xs font-bold hover:bg-surface-container-high transition-colors mx-1'
                            },
                            buttonsStyling: false,
                            preConfirm: () => {
                                const inputVal = document.getElementById('swal-price-input').value;
                                if (inputVal === '' || isNaN(inputVal) || parseFloat(inputVal) < 0) {
                                    Swal.showValidationMessage('Lütfen geçerli bir tutar girin.');
                                    return false;
                                }
                                return parseFloat(inputVal);
                            }
                        });

                        if (priceResult === undefined) {
                            this.isApproving = null;
                            return; // cancelled
                        }
                        finalPrice = priceResult;
                    }

                    const card = document.getElementById('appointment-card-' + id);

                    try {
                        const payload = { status: status };
                        if (finalPrice !== null) {
                            payload.price = finalPrice;
                        }

                        const response = await fetch(`/panel/appointments/${id}/status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            if (card) {
                                card.classList.add('opacity-0', '-translate-y-4');
                            }
                            setTimeout(() => {
                                this.pendingApprovals = this.pendingApprovals.filter(a => a.id !== id);
                                this.fetchUpdates();
                            }, 500);

                            Toast.fire({
                                icon: 'success',
                                title: status === 'approved' ? 'Randevu onaylandı!' : 'Randevu reddedildi.',
                                iconColor: '#7a5555'
                            });
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
                    } finally {
                        this.isApproving = null;
                    }
                },
                
                async dismissNotification(id) {
                    try {
                        const response = await fetch(`/panel/notifications/${id}/dismiss`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        
                        if (response.ok) {
                            const el = document.getElementById('notif-' + id);
                            if (el) {
                                el.style.opacity = '0';
                                setTimeout(() => el.remove(), 300);
                            }
                        }
                    } catch (error) {
                        console.error('Bildirim kapatılamadı', error);
                    }
                }
            }))
        })
    </script>
@endpush
