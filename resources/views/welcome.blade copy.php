<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Informasi PPM | PT Bayan Resources Tbk</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Material Icons & FontAwesome -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <!-- Alpine Plugins for Intersection Observer (Animations) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased text-gray-800 bg-white selection:bg-accent selection:text-primary-dark">
    
    <!-- Section 1: Navbar Sticky -->
    <x-navbar />

    <!-- Main Content -->
    <main>
        <!-- Section 2: Hero Video & Sambutan Pimpinan -->
        <x-hero-slider>
            <!-- Section 2B: Profil Pimpinan -->
            @php
                $pimpinan = \App\Models\Pimpinan::first() ?? new \App\Models\Pimpinan([
                    'nama' => "Dato' Dr. Low Tuck Kwong",
                    'jabatan' => 'President Director',
                    'sambutan' => "Kami berkomitmen untuk terus memperluas cakupan Pengembangan dan Pemberdayaan Masyarakat (PPM), memberdayakan masyarakat secara inklusif dan berkelanjutan."
                ]);
            @endphp
            <x-sambutan-section :pimpinan="$pimpinan" />
        </x-hero-slider>

        <!-- Section Pengumuman — Image Slider (Kotak Panjang) -->
        @php
            $pengumumans = \App\Models\Pengumuman::where('is_active', true)->latest('published_at')->take(5)->get();
            $prioritasConfig = [
                'penting' => ['bg' => 'bg-red-500', 'text' => 'text-white'],
                'info'    => ['bg' => 'bg-amber-500', 'text' => 'text-white'],
                'umum'    => ['bg' => 'bg-[var(--color-primary)]', 'text' => 'text-white'],
            ];
        @endphp
        <section id="pengumuman" class="py-16 md:py-24 bg-gray-50 border-t border-gray-100 relative overflow-hidden">
            <!-- Ornamen -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-[var(--color-primary)]/5 rounded-full blur-3xl pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <!-- Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 lg:mb-10 gap-6">
                    <div>
                        <span class="text-[var(--color-primary)] font-bold uppercase tracking-[0.3em] text-[10px] mb-3 block">Announcements</span>
                        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">Pengumuman</h2>
                        <p class="text-gray-500 mt-2 text-base md:text-lg">Informasi dan pemberitahuan resmi terbaru seputar program PPM.</p>
                    </div>
                </div>
                
                @if($pengumumans->count() > 0)
                <div x-data="{
                        activeSlide: 0,
                        slides: {{ $pengumumans->count() }},
                        autoplayInterval: null,
                        init() {
                            this.startAutoplay();
                        },
                        startAutoplay() {
                            this.autoplayInterval = setInterval(() => { this.next() }, 5000);
                        },
                        stopAutoplay() {
                            clearInterval(this.autoplayInterval);
                        },
                        next() {
                            this.activeSlide = (this.activeSlide === this.slides - 1) ? 0 : this.activeSlide + 1;
                        },
                        prev() {
                            this.activeSlide = (this.activeSlide === 0) ? this.slides - 1 : this.activeSlide - 1;
                        },
                        goTo(index) {
                            this.activeSlide = index;
                        }
                     }" 
                     class="relative w-full rounded-3xl overflow-hidden bg-gray-900 shadow-[0_20px_60px_rgba(0,0,0,0.12)] group"
                     @mouseenter="stopAutoplay()"
                     @mouseleave="startAutoplay()"
                     x-intersect.half="shown = true">
                     
                    <!-- Slider Container (Kotak Panjang Aspect Ratio) -->
                    <div class="relative w-full aspect-[4/3] md:aspect-[21/9] lg:h-[500px]">
                        @foreach($pengumumans as $index => $item)
                        @php $cfg = $prioritasConfig[$item->prioritas] ?? $prioritasConfig['umum']; @endphp
                        
                        <!-- Slide Item -->
                        <div x-show="activeSlide === {{ $index }}" 
                             x-transition:enter="transition-opacity duration-700 ease-in-out"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition-opacity duration-700 ease-in-out absolute inset-0"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="absolute inset-0 w-full h-full"
                             style="display: none;">
                            
                            <!-- Image/Background -->
                            @if($item->image)
                                <img src="{{ $item->image }}" alt="{{ $item->judul }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[10000ms] ease-linear scale-100" :class="{ 'scale-105': activeSlide === {{ $index }} }">
                            @else
                                <div class="absolute inset-0 bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center">
                                    <span class="material-icons text-gray-700 text-9xl">campaign</span>
                                </div>
                            @endif

                            <!-- Gradient Overlays for readability -->
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/50 to-transparent"></div>
                            <div class="absolute inset-0 bg-gradient-to-r from-gray-900/80 via-transparent to-transparent hidden md:block"></div>

                            <!-- Content Area -->
                            <div class="absolute inset-0 flex flex-col justify-end p-6 md:p-12 lg:p-16">
                                <div class="max-w-3xl">
                                    <!-- Badges -->
                                    <div class="flex flex-wrap items-center gap-3 mb-4 md:mb-5">
                                        <span class="{{ $cfg['bg'] }} {{ $cfg['text'] }} text-[10px] font-bold px-3 py-1.5 rounded-md uppercase tracking-widest shadow-lg">
                                            {{ strtoupper($item->prioritas) }}
                                        </span>
                                        @if($item->published_at && $item->published_at->isToday())
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-widest text-[#FFF] bg-white/20 backdrop-blur-md px-3 py-1.5 rounded-md border border-white/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span> HARI INI
                                            </span>
                                        @endif
                                        <span class="text-white/70 text-sm font-semibold flex items-center gap-1.5">
                                            <span class="material-icons text-[16px]">schedule</span>
                                            {{ $item->published_at?->format('d M Y') ?? $item->created_at->format('d M Y') }}
                                        </span>
                                    </div>

                                    <!-- Title -->
                                    <h3 class="text-2xl md:text-4xl lg:text-5xl font-extrabold text-white leading-tight mb-4 drop-shadow-lg">
                                        {{ $item->judul }}
                                    </h3>
                                    
                                    <!-- Excerpt/Isi -->
                                    <p class="text-white/80 text-base md:text-lg line-clamp-2 md:line-clamp-3 leading-relaxed max-w-2xl drop-shadow-md">
                                        {{ $item->isi }}
                                    </p>
                                    
                                    <!-- Read Button (if needed for detail page later) -->
                                    <button class="mt-8 bg-[var(--color-primary)] hover:bg-[var(--color-primary-dark)] text-white px-6 py-3 rounded-full text-sm font-bold tracking-widest uppercase transition-all shadow-[0_10px_20px_rgba(232,97,10,0.3)] hover:shadow-[0_15px_30px_rgba(232,97,10,0.4)] flex items-center gap-2 group/btn">
                                        Selengkapnya <span class="material-icons text-[18px] transition-transform group-hover/btn:translate-x-1">arrow_forward</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Navigation Controls Bottom Right -->
                    <div class="absolute bottom-6 right-6 md:bottom-12 md:right-12 flex items-center gap-4 z-20">
                        <!-- Dots -->
                        <div class="hidden md:flex items-center gap-2 mr-6 bg-gray-900/50 backdrop-blur-md px-4 py-2 rounded-full border border-white/10">
                            <template x-for="i in slides" :key="i">
                                <button @click="goTo(i - 1)" 
                                        class="w-2.5 h-2.5 rounded-full transition-all duration-300"
                                        :class="activeSlide === i - 1 ? 'w-8 bg-[var(--color-primary)]' : 'bg-white/40 hover:bg-white/80'">
                                </button>
                            </template>
                        </div>

                        <!-- Arrows -->
                        <div class="flex gap-2">
                            <button @click="prev()" class="w-12 h-12 flex items-center justify-center rounded-full bg-white text-gray-900 hover:bg-[var(--color-primary)] hover:text-white transition-all shadow-lg group/nav">
                                <span class="material-icons transition-transform group-hover/nav:-translate-x-1">west</span>
                            </button>
                            <button @click="next()" class="w-12 h-12 flex items-center justify-center rounded-full bg-white text-gray-900 hover:bg-[var(--color-primary)] hover:text-white transition-all shadow-lg group/nav">
                                <span class="material-icons transition-transform group-hover/nav:translate-x-1">east</span>
                            </button>
                        </div>
                    </div>
                </div>
                @else
                    <div class="text-center py-16 text-gray-400">
                        <span class="material-icons text-6xl mb-4 block">image_not_supported</span>
                        <p class="text-lg font-semibold">Belum ada pengumuman bergambar.</p>
                    </div>
                @endif
            </div>
        </section>

        <!-- Section 3: Tentang Sistem & Perusahaan + Anggaran PPM -->
        <x-section-tentang />

        <!-- Section 4: Dasar Hukum Pelaksanaan PPM -->
        <x-section-dasar-hukum />

        <!-- Section 5: Bidang / Pilar PPM (dengan Modal) -->
        <x-section-bidang />

        <!-- Section 6: Statistik Counter & Tabel Perusahaan -->
        <x-section-statistik />

        <!-- Section 7: Berita & Artikel Terbaru -->
        <x-section-berita />

        <!-- Section 8: Tabel Laporan Detail & Filter -->
        <x-section-laporan />

        <!-- Section 9: Visualisasi Data (Chart.js) -->
        <x-section-visualisasi />
    </main>

    <!-- Footer: Informasi, Form Kontak, & Counter Kunjungan -->
    <x-footer />

    @livewireScripts
</body>
</html>
