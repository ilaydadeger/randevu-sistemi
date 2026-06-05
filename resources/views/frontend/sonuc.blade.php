<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analiz Sonucu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-100 to-pink-100 flex items-center justify-center p-4">

    <div class="glass-panel rounded-3xl p-8 md:p-12 w-full max-w-md shadow-2xl relative overflow-hidden">
        
        <div class="text-center z-10 relative">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-6 shadow-inner">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            
            <h1 class="text-3xl font-extrabold text-gray-800 mb-2">Analiz Tamamlandı!</h1>
            <p class="text-gray-500 text-sm mb-8">Yapay zekamız fotoğrafı inceledi ve tahmini maliyeti çıkardı.</p>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-6 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-500 to-pink-500"></div>
                <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4 mt-2">Tahmini Tutar</h2>
                <div class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600 mb-2">
                    ₺{{ number_format($nihai_fiyat, 0, ',', '.') }}
                </div>
                <p class="text-xs text-gray-400 mt-2">*10 parmak baz alınarak hesaplanmıştır.</p>
            </div>

            <div class="text-left bg-purple-50 rounded-xl p-5 mb-8 border border-purple-100 shadow-inner">
                <h3 class="font-bold text-gray-700 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Bulunan Detaylar
                </h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex justify-between items-center pb-2 border-b border-purple-100/50">
                        <span>Görünen Tırnak Sayısı</span>
                        <span class="font-bold text-purple-700">{{ $gorunen_tirnak }} adet</span>
                    </li>
                    @forelse($bulunan_sanatlar as $sanat => $adet)
                        <li class="flex justify-between items-center pb-2 border-b border-purple-100/50 last:border-0 last:pb-0">
                            <span class="capitalize">{{ str_replace('_', ' ', $sanat) }}</span>
                            <span class="font-bold text-purple-700">{{ $adet }} adet</span>
                        </li>
                    @empty
                        <li class="text-gray-400 italic">Ekstra tasarım detayı bulunamadı.</li>
                    @endforelse
                </ul>
            </div>

            <a href="{{ route('tirnak.analiz') }}" class="inline-flex w-full justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-gray-900 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-all transform hover:-translate-y-1">
                Yeni Fotoğraf Yükle
            </a>
        </div>
    </div>
</body>
</html>
