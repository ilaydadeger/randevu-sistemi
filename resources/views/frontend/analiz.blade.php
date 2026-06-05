<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yapay Zeka Tırnak Analizi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-pink-100 via-purple-100 to-indigo-100 flex items-center justify-center p-4">

    <div class="glass-panel rounded-3xl p-8 md:p-12 w-full max-w-lg shadow-2xl relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-pink-300 opacity-50 blur-2xl"></div>
        <div class="absolute bottom-0 left-0 -ml-8 -mb-8 w-32 h-32 rounded-full bg-purple-300 opacity-50 blur-2xl"></div>

        <div class="relative z-10 text-center">
            <h1 class="text-3xl font-extrabold text-gray-800 mb-2">Sihirli Tırnak Analizi ✨</h1>
            <p class="text-gray-600 mb-8 text-sm">Beğendiğin tırnak tasarımını yükle, yapay zekamız fiyatını saniyeler içinde hesaplasın.</p>

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded text-left text-sm" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <form action="{{ route('tirnak.hesapla') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <div class="w-full">
                    <label for="tirnak_gorsel" class="block text-sm font-medium text-gray-700 mb-2 text-left">Fotoğraf Seçin</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-pink-300 border-dashed rounded-2xl hover:border-pink-500 transition-colors bg-white/50 relative group cursor-pointer">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-pink-400 group-hover:text-pink-600 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 justify-center mt-2">
                                <label for="tirnak_gorsel" class="relative cursor-pointer bg-transparent rounded-md font-medium text-pink-600 hover:text-pink-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-pink-500">
                                    <span id="file-name-display">Dosya Yükle</span>
                                    <!-- Dosya input alanı -->
                                    <input id="tirnak_gorsel" name="tirnak_gorsel" type="file" class="sr-only" required accept="image/*">
                                </label>
                                <p class="pl-1">veya sürükleyip bırakın</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">PNG, JPG, JPEG (Max. 5MB)</p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition-all transform hover:scale-[1.02]">
                    Fiyatı Hesapla 💅
                </button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('tirnak_gorsel').addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : "Dosya Yükle";
            document.getElementById('file-name-display').textContent = fileName;
        });
    </script>
</body>
</html>
