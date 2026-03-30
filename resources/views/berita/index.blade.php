<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Berita & Informasi | PPM Portal Bayan Group</title>
    <meta name="description" content="Berita dan informasi terkini seputar program PPM PT Bayan Resources Tbk.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased text-gray-800 bg-white">

    <x-navbar />

    <!-- Page Header -->
    <div class="relative bg-gray-900 overflow-hidden" style="padding-top: 5rem;">
        <div class="absolute inset-0 bg-gradient-to-br from-orange-950/80 via-gray-900 to-gray-900"></div>
        <!-- Decorative dots -->
        <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 24px 24px;"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-20">
            <nav class="flex items-center gap-2 text-sm text-gray-400 mb-6">
                <a href="/" class="hover:text-white transition">Beranda</a>
                <span class="material-icons text-sm text-gray-600">chevron_right</span>
                <span class="text-gray-200">Berita</span>
            </nav>
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-1.5 bg-orange-500/20 text-orange-300 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest mb-4 border border-orange-500/20">
                    <span class="material-icons text-xs">newspaper</span> Portal Berita
                </span>
                <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4">Berita & Informasi</h1>
                <p class="text-gray-400 text-lg">Kabar terkini seputar program Pengembangan dan Pemberdayaan Masyarakat (PPM) PT Bayan Resources Tbk.</p>
            </div>
        </div>
    </div>

    <!-- Content -->
    <main class="bg-gray-50 py-12 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($beritas->count() > 0)

            <!-- Featured Article (first) -->
            @php $featured = $beritas->first(); @endphp
            <a href="/berita/{{ $featured->slug }}"
               class="group block mb-12 rounded-[2rem] overflow-hidden bg-white shadow-[0_8px_40px_rgba(0,0,0,0.08)] hover:shadow-[0_20px_60px_rgba(232,97,10,0.15)] transition-all duration-500">
                <div class="flex flex-col lg:flex-row">
                    <div class="lg:w-1/2 relative overflow-hidden" style="min-height: 320px;">
                        @if($featured->thumbnail)
                            @php $fsrc = Str::startsWith($featured->thumbnail,'http') ? $featured->thumbnail : asset('storage/'.$featured->thumbnail); @endphp
                            <img src="{{ $fsrc }}"
                                 alt="{{ $featured->judul }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-700 absolute inset-0">
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-orange-400 to-orange-700 flex items-center justify-center">
                                <span class="material-icons text-white text-8xl opacity-30">newspaper</span>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent to-white/10 lg:bg-gradient-to-l"></div>
                        <div class="absolute top-6 left-6">
                            <span class="inline-flex items-center gap-1 bg-orange-500 text-white text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wider">
                                ⭐ Artikel Utama
                            </span>
                        </div>
                    </div>
                    <div class="lg:w-1/2 p-8 lg:p-12 flex flex-col justify-center">
                        @if($featured->kategori)
                            <span class="text-orange-500 text-xs font-bold uppercase tracking-widest mb-3">{{ $featured->kategori->nama }}</span>
                        @endif
                        <h2 class="text-2xl lg:text-3xl font-extrabold text-gray-900 leading-tight mb-4 group-hover:text-orange-600 transition">
                            {{ $featured->judul }}
                        </h2>
                        @if($featured->excerpt)
                            <p class="text-gray-500 text-base leading-relaxed mb-6">{{ $featured->excerpt }}</p>
                        @endif
                        <div class="flex items-center gap-4 text-sm text-gray-400 mb-8">
                            <span class="flex items-center gap-1.5">
                                <span class="material-icons text-sm text-orange-400">calendar_today</span>
                                {{ $featured->published_at ? $featured->published_at->format('d M Y') : $featured->created_at->format('d M Y') }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="material-icons text-sm text-orange-400">schedule</span>
                                {{ ceil(str_word_count(strip_tags($featured->isi)) / 200) }} menit
                            </span>
                        </div>
                        <span class="inline-flex items-center gap-2 text-orange-600 font-bold text-sm group-hover:gap-4 transition-all">
                            Baca Artikel Lengkap
                            <span class="material-icons text-base">arrow_forward</span>
                        </span>
                    </div>
                </div>
            </a>

            <!-- Grid Articles -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($beritas->skip(1) as $i => $berita)
                <a href="/berita/{{ $berita->slug }}"
                   class="group flex flex-col bg-white rounded-[1.5rem] overflow-hidden shadow-sm hover:shadow-[0_12px_40px_rgba(232,97,10,0.12)] border border-gray-100 hover:border-orange-100 transition-all duration-300 hover:-translate-y-1">

                    <!-- Thumbnail -->
                    <div class="relative overflow-hidden bg-gray-100" style="height: 200px;">
                        @if($berita->thumbnail)
                            @php $tsrc = Str::startsWith($berita->thumbnail,'http') ? $berita->thumbnail : asset('storage/'.$berita->thumbnail); @endphp
                            <img src="{{ $tsrc }}"
                                 alt="{{ $berita->judul }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-orange-50 to-orange-100 flex items-center justify-center">
                                <span class="material-icons text-orange-300 text-5xl">newspaper</span>
                            </div>
                        @endif
                        @if($berita->kategori)
                            <div class="absolute top-4 left-4">
                                <span class="bg-white/90 backdrop-blur-sm text-orange-600 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest shadow-sm">
                                    {{ $berita->kategori->nama }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="flex flex-col flex-1 p-6">
                        <div class="flex items-center gap-3 text-xs text-gray-400 mb-3">
                            <span class="flex items-center gap-1">
                                <span class="material-icons text-xs text-orange-400">calendar_today</span>
                                {{ $berita->published_at ? $berita->published_at->format('d M Y') : $berita->created_at->format('d M Y') }}
                            </span>
                            <span class="text-gray-200">·</span>
                            <span>{{ ceil(str_word_count(strip_tags($berita->isi)) / 200) }} menit</span>
                        </div>

                        <h3 class="text-lg font-extrabold text-gray-900 leading-tight mb-3 group-hover:text-orange-600 transition line-clamp-2">
                            {{ $berita->judul }}
                        </h3>

                        @if($berita->excerpt)
                            <p class="text-gray-500 text-sm leading-relaxed line-clamp-2 mb-4">{{ $berita->excerpt }}</p>
                        @endif

                        <div class="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
                            <span class="text-orange-600 text-sm font-bold group-hover:gap-2 inline-flex items-center gap-1 transition-all">
                                Baca Selengkapnya
                                <span class="material-icons text-sm transition-transform group-hover:translate-x-1">arrow_forward</span>
                            </span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($beritas->hasPages())
                <div class="mt-12 flex justify-center">
                    {{ $beritas->links() }}
                </div>
            @endif

            @else
                <div class="text-center py-32">
                    <div class="w-24 h-24 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="material-icons text-5xl text-orange-300">newspaper</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-700 mb-2">Belum Ada Berita</h3>
                    <p class="text-gray-400">Berita dan informasi akan segera dipublikasikan.</p>
                    <a href="/" class="mt-6 inline-flex items-center gap-2 text-orange-600 font-semibold hover:underline">
                        <span class="material-icons text-base">arrow_back</span> Kembali ke Beranda
                    </a>
                </div>
            @endif
        </div>
    </main>

    <x-footer />

    @livewireScripts
</body>
</html>
