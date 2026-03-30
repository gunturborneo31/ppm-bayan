<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $berita->judul }} | PPM Portal Bayan Group</title>
    <meta name="description" content="{{ $berita->excerpt ?? Str::limit(strip_tags($berita->isi), 160) }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased text-gray-800 bg-white">

    <!-- Navbar -->
    <x-navbar />

    <!-- Hero / Cover Image -->
    <div class="relative w-full bg-gray-900 overflow-hidden" style="padding-top: 5rem; min-height: 420px;">
        @if($berita->thumbnail)
            <img src="{{ asset('storage/' . $berita->thumbnail) }}"
                 alt="{{ $berita->judul }}"
                 class="absolute inset-0 w-full h-full object-cover opacity-40">
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-gray-900 via-orange-950/60 to-gray-900"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/50 to-transparent"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-20">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-sm text-gray-400 mb-8">
                <a href="/" class="hover:text-white transition">Beranda</a>
                <span class="material-icons text-sm text-gray-600">chevron_right</span>
                <a href="/berita" class="hover:text-white transition">Berita</a>
                <span class="material-icons text-sm text-gray-600">chevron_right</span>
                <span class="text-gray-300 truncate max-w-xs">{{ Str::limit($berita->judul, 40) }}</span>
            </nav>

            <!-- Kategori Badge -->
            @if($berita->kategori)
                <span class="inline-flex items-center gap-1.5 bg-orange-500 text-white text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest mb-5">
                    <span class="material-icons text-xs">label</span>
                    {{ $berita->kategori->nama }}
                </span>
            @endif

            <!-- Judul -->
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-white leading-tight mb-6" style="font-family: 'Merriweather', serif;">
                {{ $berita->judul }}
            </h1>

            <!-- Meta -->
            <div class="flex flex-wrap items-center gap-6 text-sm text-gray-300">
                <span class="flex items-center gap-2">
                    <span class="material-icons text-base text-orange-400">calendar_today</span>
                    {{ $berita->published_at ? $berita->published_at->translatedFormat('d F Y') : $berita->created_at->translatedFormat('d F Y') }}
                </span>
                <span class="flex items-center gap-2">
                    <span class="material-icons text-base text-orange-400">schedule</span>
                    {{ ceil(str_word_count(strip_tags($berita->isi)) / 200) }} menit baca
                </span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="flex flex-col lg:flex-row gap-12">

                <!-- Article Body -->
                <article class="flex-1 min-w-0">
                    <!-- Excerpt / Lead -->
                    @if($berita->excerpt)
                        <p class="text-xl text-gray-600 leading-relaxed font-medium mb-10 pb-10 border-b border-gray-100" style="font-family: 'Merriweather', serif; font-style: italic;">
                            {{ $berita->excerpt }}
                        </p>
                    @endif

                    <!-- Body Content -->
                    <div class="prose prose-lg prose-orange max-w-none
                                prose-headings:font-extrabold prose-headings:text-gray-900
                                prose-p:text-gray-700 prose-p:leading-relaxed
                                prose-a:text-orange-600 prose-a:no-underline hover:prose-a:underline
                                prose-img:rounded-2xl prose-img:shadow-lg
                                prose-blockquote:border-orange-400 prose-blockquote:bg-orange-50 prose-blockquote:rounded-r-xl prose-blockquote:py-2
                                prose-strong:text-gray-900
                                prose-li:marker:text-orange-500">
                        {!! $berita->isi !!}
                    </div>

                    <!-- Share Buttons -->
                    <div class="mt-12 pt-8 border-t border-gray-100">
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">Bagikan Artikel</p>
                        <div class="flex flex-wrap gap-3">
                            <a href="https://twitter.com/intent/tweet?text={{ urlencode($berita->judul) }}&url={{ urlencode(request()->url()) }}"
                               target="_blank"
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-900 hover:bg-gray-700 text-white text-sm font-semibold transition">
                                <i class="fa-brands fa-x-twitter text-base"></i> Twitter / X
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                               target="_blank"
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                                <i class="fa-brands fa-facebook-f text-base"></i> Facebook
                            </a>
                            <a href="https://api.whatsapp.com/send?text={{ urlencode($berita->judul . ' ' . request()->url()) }}"
                               target="_blank"
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-green-500 hover:bg-green-600 text-white text-sm font-semibold transition">
                                <i class="fa-brands fa-whatsapp text-base"></i> WhatsApp
                            </a>
                            <button onclick="navigator.clipboard.writeText('{{ request()->url() }}'); this.innerHTML = '<span class=\'material-icons text-sm\'>check</span> Tersalin!'"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold transition">
                                <span class="material-icons text-sm">link</span> Salin Link
                            </button>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="mt-8">
                        <a href="/berita"
                           class="inline-flex items-center gap-2 text-orange-600 hover:text-orange-700 font-semibold transition group">
                            <span class="material-icons text-base transition-transform group-hover:-translate-x-1">arrow_back</span>
                            Kembali ke Daftar Berita
                        </a>
                    </div>
                </article>

                <!-- Sidebar -->
                <aside class="w-full lg:w-72 shrink-0">
                    <div class="sticky top-24 space-y-6">

                        <!-- Info Box -->
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Informasi Artikel</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex items-start gap-3">
                                    <span class="material-icons text-base text-orange-400 mt-0.5">folder</span>
                                    <div>
                                        <p class="text-gray-400 text-xs">Kategori</p>
                                        <p class="font-semibold text-gray-800">{{ $berita->kategori->nama ?? 'Umum' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <span class="material-icons text-base text-orange-400 mt-0.5">calendar_month</span>
                                    <div>
                                        <p class="text-gray-400 text-xs">Tanggal Terbit</p>
                                        <p class="font-semibold text-gray-800">
                                            {{ $berita->published_at ? $berita->published_at->translatedFormat('d F Y') : $berita->created_at->translatedFormat('d F Y') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <span class="material-icons text-base text-orange-400 mt-0.5">timer</span>
                                    <div>
                                        <p class="text-gray-400 text-xs">Estimasi Baca</p>
                                        <p class="font-semibold text-gray-800">{{ ceil(str_word_count(strip_tags($berita->isi)) / 200) }} menit</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Related Articles -->
                        @if($related->count() > 0)
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Artikel Terkait</h3>
                            <div class="space-y-4">
                                @foreach($related as $rel)
                                <a href="/berita/{{ $rel->slug }}"
                                   class="group flex gap-3 p-3 rounded-xl hover:bg-orange-50 transition border border-transparent hover:border-orange-100">
                                    <div class="w-16 h-16 rounded-lg overflow-hidden shrink-0 bg-gray-100">
                                        @if($rel->thumbnail)
                                            <img src="{{ asset('storage/' . $rel->thumbnail) }}"
                                                 alt="{{ $rel->judul }}"
                                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-orange-100 to-orange-200 flex items-center justify-center">
                                                <span class="material-icons text-orange-400">newspaper</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs text-gray-400 mb-1">
                                            {{ $rel->published_at ? $rel->published_at->format('d M Y') : $rel->created_at->format('d M Y') }}
                                        </p>
                                        <h4 class="text-sm font-semibold text-gray-800 group-hover:text-orange-600 transition line-clamp-2 leading-snug">
                                            {{ $rel->judul }}
                                        </h4>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                            <a href="/berita" class="mt-4 block text-center text-sm font-semibold text-orange-600 hover:text-orange-700 transition py-2">
                                Lihat semua berita →
                            </a>
                        </div>
                        @endif

                    </div>
                </aside>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <x-footer />

    @livewireScripts
</body>
</html>
