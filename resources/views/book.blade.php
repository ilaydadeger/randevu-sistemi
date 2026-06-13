@extends('layouts.app')

@section('title', "Fiyatlandırma Ayarları - " . (auth()->user()->salon_name ?? "L'ART DE L'ONGLE"))

@section('content')
    <main class="flex-1 px-margin-mobile pt-md pb-[100px] flex flex-col gap-md max-w-[600px] md:max-w-3xl lg:max-w-4xl mx-auto w-full"
        x-data="pricingManager()">
        <div class="flex flex-col gap-sm">
            <h2 class="font-headline-md text-headline-md text-on-background">CV Fiyatlandırma Modeli</h2>
        </div>

        <form @submit.prevent="submitPricingForm" id="pricingForm" class="space-y-6 mt-2">
            @foreach($categories as $groupName => $groupCategories)
                @php
                    $isLength = str_contains($groupName, 'Uzunluk');
                    $isShape = str_contains($groupName, 'Şekil');
                @endphp
                <section class="bg-surface-container-lowest rounded-xl p-5 border border-outline-variant/30 shadow-sm">
                    <div class="flex items-center justify-between mb-4 border-b border-surface-variant pb-3 gap-4">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">
                                @if(str_contains($groupName, 'Temel')) build
                                @elseif($isLength) straighten
                                @elseif($isShape) category
                                @else palette
                                @endif
                            </span>
                            <h3 class="font-headline-sm text-lg text-on-surface">{{ $groupName }}</h3>
                        </div>

                        @if($isLength)
                            <label class="flex items-center gap-2 cursor-pointer text-[11px] text-on-surface-variant select-none">
                                <input type="checkbox" x-model="excludeLength"
                                    class="rounded border-outline-variant text-primary focus:ring-primary h-3.5 w-3.5">
                                <span>Fiyatlandırmaya Dahil Etme</span>
                            </label>
                        @elseif($isShape)
                            <label class="flex items-center gap-2 cursor-pointer text-[11px] text-on-surface-variant select-none">
                                <input type="checkbox" x-model="excludeShape"
                                    class="rounded border-outline-variant text-primary focus:ring-primary h-3.5 w-3.5">
                                <span>Fiyatlandırmaya Dahil Etme</span>
                            </label>
                        @endif
                    </div>

                    <div class="space-y-4" @if($isLength) x-show="!excludeLength" x-transition @elseif($isShape)
                    x-show="!excludeShape" x-transition @endif>
                        @foreach($groupCategories as $category)
                            <div class="flex items-center justify-between gap-4">
                                <label for="price_{{ $category->id }}" class="font-body-md text-on-surface-variant flex-1">
                                    @php
                                        $displayName = match($category->name) {
                                            'Jel Protez' => 'Protez Tırnak Yapımı',
                                            'Kalıcı Oje' => 'Kalıcı Oje',
                                            'Jel Güçlendirme' => 'Jel Güçlendirme',
                                            'Çıkarma' => 'Çıkarma Ücreti',
                                            default => $category->name
                                        };
                                    @endphp
                                    {{ $displayName }}
                                </label>

                                <div class="relative w-1/3 min-w-[120px]">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant font-medium">₺</span>
                                    <input type="number" step="1" min="0" id="price_{{ $category->id }}"
                                        name="prices[{{ $category->id }}]"
                                        value="{{ isset($userPrices[$category->id]) ? intval($userPrices[$category->id]->price) : '' }}"
                                        placeholder="0"
                                        class="w-full bg-surface-container-low border-0 border-b-2 border-surface-variant focus:border-primary focus:ring-0 pl-8 pr-3 py-2.5 text-on-surface rounded-t-DEFAULT text-right font-medium">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <div class="pt-sm pb-8">
                <button type="submit" :disabled="isLoading"
                    class="w-full bg-gradient-to-r from-primary to-[#8a6565] text-on-primary font-label-caps text-label-caps py-4 rounded-full shadow-[0px_10px_20px_rgba(122,85,85,0.2)] hover:opacity-90 transition-opacity flex justify-center items-center gap-2 disabled:opacity-50">
                    <span x-show="isLoading"
                        class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                    <span x-show="!isLoading" class="material-symbols-outlined text-[18px]">save</span>
                    FİYATLARIMI KAYDET
                </button>
            </div>
        </form>
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
            timer: 3000,
            timerProgressBar: true,
            customClass: {
                popup: 'rounded-xl shadow-lg border border-outline/10 bg-surface-container-lowest text-on-surface font-body-md'
            }
        });

        document.addEventListener('alpine:init', () => {
            Alpine.data('pricingManager', () => ({
                isLoading: false,
                excludeLength: {{ auth()->user()->exclude_length_pricing ? 'true' : 'false' }},
                excludeShape: {{ auth()->user()->exclude_shape_pricing ? 'true' : 'false' }},

                async submitPricingForm() {
                    this.isLoading = true;

                    const form = document.getElementById('pricingForm');
                    const formData = new FormData(form);

                    let prices = {};
                    for (let [key, value] of formData.entries()) {
                        const match = key.match(/prices\[(\d+)\]/);
                        if (match && match[1]) {
                            prices[match[1]] = value;
                        }
                    }

                    try {
                        const response = await fetch("{{ route('panel.book.pricing') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                prices: prices,
                                exclude_length_pricing: this.excludeLength,
                                exclude_shape_pricing: this.excludeShape
                            })
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
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
                }
            }));
        });
    </script>
@endpush