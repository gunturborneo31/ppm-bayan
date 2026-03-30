@php
    $beritas = \App\Models\Berita::latest('published_at')->take(5)->get();
@endphp

<section class="py-16 md:py-24 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-12 lg:gap-16 items-center">
            
            <!-- Sisi Kiri: Teks Sambutan Pimpinan (Bukan bentuk Card lagi) -->
            <div class="w-full lg:w-5/12 flex flex-col justify-center">
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-1 bg-primary rounded-full"></div>
                        <span class="text-primary font-bold tracking-widest uppercase text-sm">Sambutan</span>
                    </div>
                    <h2 class="text-3xl lg:text-4xl lg:leading-[1.2] font-extrabold text-gray-900 mb-6">
                        Transparansi & Komitmen Berkelanjutan
                    </h2>
                    <p class="text-gray-600 text-base lg:text-lg leading-relaxed mb-8">
                        "Portal PPM ini adalah wujud nyata integritas perusahaan dalam menjalankan pilar-pilar pengembangan dan pemberdayaan masyarakat. Kami berharap inovasi layanan ini mampu memberikan dampak positif yang terukur bagi seluruh elemen masyarakat."
                    </p>
                </div>

                <!-- Profil Pimpinan (Lebih Besar & Menonjol) -->
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 p-6 md:p-8 bg-gray-50 rounded-[2rem] border border-gray-100 shadow-sm transition hover:shadow-md">
                    <img src="https://ui-avatars.com/api/?name=Pimpinan&background=ea580c&color=fff&size=512" alt="Foto Pimpinan" class="w-32 h-32 md:w-40 md:h-40 xl:w-48 xl:h-48 rounded-full object-cover shadow-md border-4 border-white flex-shrink-0">
                    <div class="text-center sm:text-left pt-2 md:pt-4">
                        <h4 class="font-extrabold text-gray-900 text-2xl xl:text-3xl mb-1">Nama Pimpinan</h4>
                        <p class="text-sm xl:text-base text-primary font-bold uppercase tracking-wider mb-3">Jabatan Pimpinan</p>
                        <p class="text-gray-500 text-sm leading-relaxed">
                            Secara proaktif memastikan seluruh inisiatif sosial dan lingkungan perusahaan berjalan konsisten demi kesejahteraan bersama.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sisi Kanan: Slider Berita (Lebih lebar dan dominan) -->
            <div class="w-full lg:w-7/12">
                <div class="flex justify-between items-end mb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 tracking-tight">Kabar Terbaru</h3>
                    </div>
                    <a href="/berita" class="text-sm font-semibold text-primary hover:text-primary-dark flex items-center gap-1 transition-colors">
                        Lihat Semua <span class="material-icons text-sm">arrow_forward</span>
                    </a>
                </div>

                @if($beritas->count() > 0)
                    <div x-data="{ 
                        activeSlide: 0, 
                        slides: {{ $beritas->map(fn($b) => ['image' => $b->thumbnail ? asset('storage/'.$b->thumbnail) : 'https://images.unsplash.com/photo-1541888081622-6b970eb26c9f?auto=format&fit=crop&w=800&q=80', 'title' => $b->judul, 'date' => $b->published_at ? $b->published_at->format('d M Y') : $b->created_at->format('d M Y'), 'slug' => $b->slug])->toJson() }},
                        next() { this.activeSlide = (this.activeSlide + 1) % this.slides.length }, 
                        prev() { this.activeSlide = (this.activeSlide - 1 + this.slides.length) % this.slides.length } 
                        }" 
                        x-init="setInterval(() => next(), 5000)"
                        class="relative w-full h-[400px] lg:h-[480px] rounded-[2rem] overflow-hidden shadow-lg group">
                        
                        <template x-for="(slide, index) in slides" :key="index">
                            <div x-show="activeSlide === index" 
                                 x-transition:enter="transition ease-out duration-700"
                                 x-transition:enter-start="opacity-0 scale-105"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in-out duration-500 absolute top-0"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="w-full h-full relative bg-gray-200">
                                
                                <!-- Gambar -->
                                <img :src="slide.image" :alt="slide.title" class="absolute inset-0 w-full h-full object-cover">
                                
                                <!-- Overlay Gelap -->
                                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/30 to-transparent"></div>
                                
                                <!-- Teks -->
                                <div class="absolute bottom-0 left-0 w-full p-8 md:p-12">
                                    <span class="inline-block bg-primary text-white text-[10px] md:text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-widest mb-4" x-text="slide.date"></span>
                                    <h3 class="text-2xl md:text-3xl font-bold text-white leading-snug mb-6 line-clamp-3 drop-shadow-md" x-text="slide.title"></h3>
                                    
                                    <a :href="`/berita/${slide.slug}`" class="inline-flex items-center gap-2 text-white font-medium hover:text-primary transition-colors group/btn">
                                        Baca Artikel Lengkap
                                        <span class="material-icons text-base transition-transform group-hover/btn:translate-x-1">arrow_right_alt</span>
                                    </a>
                                </div>
                            </div>
                        </template>

                        <!-- Navigasi Slider Card Kanan -->
                        <div class="absolute top-6 right-6 flex gap-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <button @click="prev()" class="w-10 h-10 bg-white/20 hover:bg-white/40 backdrop-blur-md rounded-full text-white flex items-center justify-center transition">
                                <span class="material-icons text-xl">west</span>
                            </button>
                            <button @click="next()" class="w-10 h-10 bg-white/20 hover:bg-white/40 backdrop-blur-md rounded-full text-white flex items-center justify-center transition">
                                <span class="material-icons text-xl">east</span>
                            </button>
                        </div>

                        <!-- Progress Dots -->
                        <div class="absolute bottom-8 right-8 flex gap-2">
                            <template x-for="(slide, index) in slides" :key="index">
                                <button @click="activeSlide = index" 
                                        class="h-1.5 rounded-full transition-all duration-300"
                                        :class="activeSlide === index ? 'w-8 bg-white' : 'w-2 bg-white/40 hover:bg-white/70'">
                                </button>
                            </template>
                        </div>
                    </div>
                @else
                    <div class="w-full h-[400px] flex flex-col items-center justify-center bg-gray-50 border border-gray-200 rounded-[2rem]">
                        <span class="material-icons text-gray-300 text-5xl mb-4">newspaper</span>
                        <p class="text-gray-500 font-medium">Belum ada berita dipublikasikan.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</section>
