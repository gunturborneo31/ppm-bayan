<div class="relative w-full h-full flex flex-col">
    @if(count($berita) > 0)
        <!-- Slider Content Light Theme -->
        <div class="relative flex-1 overflow-hidden rounded-2xl bg-white shadow-[0_10px_40px_rgba(0,0,0,0.05)] border border-gray-100 group">
            @foreach($berita as $index => $slide)
                <div x-show="$wire.active === {{ $index }}" 
                     x-transition:enter="transition ease-out duration-700"
                     x-transition:enter-start="opacity-0 translate-y-8"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-500 absolute top-0"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute inset-0 w-full h-full flex flex-col"
                     @if($active !== $index) style="display: none;" @endif>
                     
                    <!-- Thumbnail -->
                    <div class="relative h-[200px] lg:h-[240px] shrink-0 bg-gray-100 overflow-hidden">
                        @if($slide['thumbnail'])
                            <img src="{{ $slide['thumbnail'] }}" alt="{{ $slide['judul'] }}" class="absolute inset-0 w-full h-full object-cover transition duration-700 group-hover:scale-105">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="material-icons text-gray-300 text-6xl">image</span>
                            </div>
                        @endif
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-transparent to-transparent"></div>

                        <!-- Badge Kategori -->
                        <span class="absolute top-4 left-4 bg-[var(--color-primary)] text-white text-[9px] font-bold px-3 py-1.5 rounded-sm uppercase tracking-widest shadow-md">
                            {{ $slide['kategori'] }}
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="p-6 lg:p-8 flex flex-col justify-between flex-1">
                        <div>
                            <span class="text-xs text-gray-500 font-semibold mb-2 flex items-center gap-1.5 uppercase tracking-widest">
                                <span class="material-icons text-[14px]">calendar_today</span>
                                {{ $slide['tanggal'] }}
                            </span>
                            <h3 class="text-lg lg:text-xl font-bold text-gray-900 line-clamp-2 leading-snug">{{ $slide['judul'] }}</h3>
                        </div>
                        
                        <div class="mt-4 flex justify-between items-center group/bottom">
                            <a href="/berita/{{ $slide['slug'] }}" class="inline-flex items-center gap-2 text-[var(--color-primary)] text-sm font-bold hover:text-[var(--color-primary-dark)] transition-colors group/link pb-1 border-b border-[var(--color-primary)]/30 hover:border-b-[var(--color-primary)]">
                                BACA ARTIKEL <span class="material-icons text-[16px] transition-transform group-hover/link:translate-x-1">arrow_right_alt</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Controls (Light Theme, Arrows Only) -->
        <div class="flex items-center justify-end mt-6 gap-3">
            <button wire:click.prevent="prev" class="w-12 h-12 flex items-center justify-center rounded-full bg-white text-gray-600 border border-gray-200 hover:bg-[var(--color-primary)] hover:text-white hover:border-[var(--color-primary)] shadow-sm hover:shadow-md transition-all duration-300 group/btn">
                <span class="material-icons text-[16px] transition-transform group-hover/btn:-translate-x-1">west</span>
            </button>
            <button wire:click.prevent="next" class="w-12 h-12 flex items-center justify-center rounded-full bg-white text-gray-600 border border-gray-200 hover:bg-[var(--color-primary)] hover:text-white hover:border-[var(--color-primary)] shadow-sm hover:shadow-md transition-all duration-300 group/btn">
                <span class="material-icons text-[16px] transition-transform group-hover/btn:translate-x-1">east</span>
            </button>
        </div>
    @endif
</div>
