<div>
    <!-- Header + Filter -->
    <div class="mb-10 flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
        <div>
            <span class="text-[var(--color-primary)] font-bold uppercase tracking-[0.3em] text-[10px] mb-2 block">Portal Informasi</span>
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Berita & Artikel</h2>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">

            <!-- Kategori Filter - Custom Alpine Dropdown -->
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <!-- Trigger Button -->
                <button @click="open = !open"
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl shadow-sm text-sm font-semibold text-gray-700 hover:border-[var(--color-primary)]/50 hover:bg-orange-50/30 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]/20 transition-all duration-200 w-full sm:w-auto justify-between min-w-[180px]">
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-[16px] text-[var(--color-primary)]">label</span>
                        <span>{{ $kategori ? $kategoris->firstWhere('id', $kategori)?->nama : 'Semua Kategori' }}</span>
                    </div>
                    <span class="material-icons text-[18px] text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''">expand_more</span>
                </button>

                <!-- Dropdown Panel -->
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                     class="absolute left-0 mt-2 w-56 z-50 bg-white rounded-2xl shadow-[0_20px_60px_rgba(0,0,0,0.12)] border border-gray-100 overflow-hidden"
                     style="display:none;">

                    <!-- All Categories Option -->
                    <button type="button"
                            wire:click="$set('kategori', '')"
                            @click="open = false"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold transition-colors {{ !$kategori ? 'bg-[var(--color-primary)] text-white' : 'text-gray-700 hover:bg-orange-50 hover:text-[var(--color-primary)]' }}">
                        <span class="material-icons text-[16px]">apps</span>
                        Semua Kategori
                        @if(!$kategori)
                            <span class="material-icons text-[16px] ml-auto">check</span>
                        @endif
                    </button>

                    <div class="h-px bg-gray-100 mx-3"></div>

                    <!-- Individual Categories -->
                    @foreach($kategoris as $kat)
                    <button type="button"
                            wire:click="$set('kategori', '{{ $kat->id }}')"
                            @click="open = false"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold transition-colors {{ $kategori == $kat->id ? 'bg-[var(--color-primary)] text-white' : 'text-gray-700 hover:bg-orange-50 hover:text-[var(--color-primary)]' }}">
                        <span class="material-icons text-[16px]">{{ $kat->id == $kategori ? 'radio_button_checked' : 'radio_button_unchecked' }}</span>
                        {{ $kat->nama }}
                        @if($kategori == $kat->id)
                            <span class="material-icons text-[16px] ml-auto">check</span>
                        @endif
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Search -->
            <div class="relative w-full md:w-64">
                <span class="absolute inset-y-0 left-4 flex items-center text-gray-400 pointer-events-none">
                    <span class="material-icons text-sm">search</span>
                </span>
                <input wire:model.live="search"
                       type="text"
                       placeholder="Cari berita..."
                       class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]/30 focus:border-[var(--color-primary)] transition shadow-sm hover:border-gray-300">
                <div wire:loading wire:target="search" class="absolute inset-y-0 right-3 flex items-center">
                    <svg class="animate-spin h-4 w-4 text-[var(--color-primary)]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Berita -->
    <div wire:loading.remove wire:target="search, kategori"
         class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($beritas as $berita)
            <a href="/berita/{{ $berita->slug }}"
               class="group flex flex-col bg-white rounded-[1.5rem] overflow-hidden border border-gray-100 shadow-sm hover:shadow-[0_12px_40px_rgba(232,97,10,0.1)] hover:border-orange-100 transition-all duration-300 hover:-translate-y-1">

                <!-- Thumbnail -->
                <div class="relative overflow-hidden bg-gray-100" style="height:200px;">
                    @if($berita->thumbnail)
                        @php $thumbSrc = Str::startsWith($berita->thumbnail, 'http') ? $berita->thumbnail : asset('storage/' . $berita->thumbnail); @endphp
                        <img src="{{ $thumbSrc }}"
                             alt="{{ $berita->judul }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-orange-50 to-orange-100 flex items-center justify-center">
                            <span class="material-icons text-5xl text-orange-200">newspaper</span>
                        </div>
                    @endif

                    @if($berita->kategori)
                        <div class="absolute top-3 left-3">
                            <span class="inline-flex items-center gap-1 bg-[var(--color-primary)] text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow">
                                {{ $berita->kategori->nama }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Content -->
                <div class="flex flex-col flex-1 p-5">
                    <div class="flex items-center gap-2 text-[11px] text-gray-400 mb-3">
                        <span class="material-icons text-[13px] text-[var(--color-primary)]">schedule</span>
                        {{ $berita->published_at ? $berita->published_at->format('d M Y') : $berita->created_at->format('d M Y') }}
                        <span class="text-gray-200">·</span>
                        <span>{{ ceil(str_word_count(strip_tags($berita->isi)) / 200) }} menit baca</span>
                    </div>

                    <h3 class="text-base font-extrabold text-gray-900 mb-2 line-clamp-2 leading-tight group-hover:text-[var(--color-primary)] transition">
                        {{ $berita->judul }}
                    </h3>

                    <p class="text-gray-500 text-sm line-clamp-2 mb-4 flex-1 leading-relaxed">
                        {{ $berita->excerpt ?? Str::limit(strip_tags($berita->isi), 100) }}
                    </p>

                    <div class="pt-3 border-t border-gray-50 flex items-center justify-between">
                        <span class="text-[var(--color-primary)] text-sm font-bold inline-flex items-center gap-1 group-hover:gap-2 transition-all">
                            Baca Selengkapnya
                            <span class="material-icons text-sm transition-transform group-hover:translate-x-1">arrow_forward</span>
                        </span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-1 md:col-span-3 text-center py-20">
                <div class="w-20 h-20 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-5">
                    <span class="material-icons text-4xl text-orange-300">newspaper</span>
                </div>
                <p class="text-gray-500 font-semibold text-lg mb-1">Tidak Ada Hasil</p>
                <p class="text-gray-400 text-sm">Coba kata kunci atau kategori yang berbeda.</p>
            </div>
        @endforelse
    </div>

    <!-- Skeleton Loading -->
    <div wire:loading wire:target="search, kategori"
         class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 w-full">
        @for($i=0; $i<6; $i++)
            <div class="bg-white rounded-[1.5rem] border border-gray-100 overflow-hidden flex flex-col animate-pulse">
                <div class="w-full bg-gray-100" style="height:200px;"></div>
                <div class="p-5 flex flex-col gap-3">
                    <div class="h-3 bg-gray-100 rounded-full w-1/3"></div>
                    <div class="h-4 bg-gray-100 rounded-full w-full"></div>
                    <div class="h-4 bg-gray-100 rounded-full w-3/4"></div>
                    <div class="h-3 bg-gray-100 rounded-full w-full"></div>
                    <div class="h-3 bg-gray-100 rounded-full w-2/3"></div>
                </div>
            </div>
        @endfor
    </div>

    <!-- Pagination -->
    <div class="mt-10">
        {{ $beritas->links() }}
    </div>
</div>
