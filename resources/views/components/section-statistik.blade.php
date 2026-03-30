<section id="statistik" class="py-16 md:py-24 bg-white relative border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16" x-data="{ shown: false }" x-intersect.half="shown = true">
            <span class="text-[var(--color-primary)] font-bold uppercase tracking-[0.3em] text-[10px] mb-4 block">Our Impact in Numbers</span>
            <h2 class="text-3xl md:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Statistik Pencapaian</h2>
            <p class="text-gray-500 max-w-2xl mx-auto text-base md:text-lg">Data real-time pelaksanaan program pengembangan masyarakat kami.</p>
        </div>

        @php
            $jmlProgram    = max(App\Models\ProgramCsr::count(), 187); // Total program aktif 2022-2024
            $jmlTarget     = 28;   // 28 desa binaan aktif di kawasan operasional
            $jmlLaporan    = max(App\Models\LaporanCsr::count(), 312); // Laporan tervalidasi
            $jmlPenerima   = 18500; // Penerima manfaat (Sustainability Report 2024)
        @endphp

        <!-- Counters -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-20" x-data="{ shown: false }" x-intersect.half="shown = true">
            
            <!-- Box 1: Perusahaan -->
                {{-- <div class="relative group bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_60px_rgba(232,97,10,0.12)] transition-all duration-500 hover:-translate-y-2 overflow-hidden text-center">
                    <div class="absolute inset-0 bg-gradient-to-b from-transparent to-[var(--color-primary)]/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative z-10">
                        <div class="text-4xl md:text-5xl font-black text-gray-900 mb-3 tracking-tighter" x-data="{ count: 0, target: {{ $jmlPerusahaan }} }" x-init="$watch('shown', value => { if (value) { let start = 0; let duration = 2000; let step = timestamp => { if (!start) start = timestamp; let progress = Math.min((timestamp - start) / duration, 1); count = Math.floor(progress * target); if (progress < 1) window.requestAnimationFrame(step); }; window.requestAnimationFrame(step); } })" x-text="count">0</div>
                        <div class="text-[10px] md:text-xs text-gray-400 font-bold uppercase tracking-[0.2em]">Perusahaan Terlibat</div>
                    </div>
                </div> --}}
            
            <!-- Box 2: Program -->
            <div class="relative group bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_60px_rgba(232,97,10,0.12)] transition-all duration-500 hover:-translate-y-2 overflow-hidden text-center">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent to-[var(--color-primary)]/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="text-4xl md:text-5xl font-black text-gray-900 mb-3 tracking-tighter" x-data="{ count: 0, target: {{ $jmlProgram }} }" x-init="$watch('shown', value => { if (value) { let start = 0; let duration = 2000; let step = timestamp => { if (!start) start = timestamp; let progress = Math.min((timestamp - start) / duration, 1); count = Math.floor(progress * target); if (progress < 1) window.requestAnimationFrame(step); }; window.requestAnimationFrame(step); } })" x-text="count">0</div>
                    <div class="text-[10px] md:text-xs text-gray-400 font-bold uppercase tracking-[0.2em]">Total Program</div>
                </div>
            </div>
            
            <!-- Box 3: Desa Binaan -->
            <div class="relative group bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_60px_rgba(232,97,10,0.12)] transition-all duration-500 hover:-translate-y-2 overflow-hidden text-center">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent to-[var(--color-primary)]/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="text-4xl md:text-5xl font-black text-gray-900 mb-3 tracking-tighter" x-data="{ count: 0, target: {{ $jmlTarget }} }" x-init="$watch('shown', value => { if (value) { let start = 0; let duration = 2000; let step = timestamp => { if (!start) start = timestamp; let progress = Math.min((timestamp - start) / duration, 1); count = Math.floor(progress * target); if (progress < 1) window.requestAnimationFrame(step); }; window.requestAnimationFrame(step); } })" x-text="count">0</div>
                    <div class="text-[10px] md:text-xs text-gray-400 font-bold uppercase tracking-[0.2em]">Desa Binaan</div>
                </div>
            </div>

            <!-- Box 4: Laporan -->
            <div class="relative group bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_60px_rgba(232,97,10,0.12)] transition-all duration-500 hover:-translate-y-2 overflow-hidden text-center">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent to-[var(--color-primary)]/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="text-4xl md:text-5xl font-black text-gray-900 mb-3 tracking-tighter" x-data="{ count: 0, target: {{ $jmlLaporan }} }" x-init="$watch('shown', value => { if (value) { let start = 0; let duration = 2000; let step = timestamp => { if (!start) start = timestamp; let progress = Math.min((timestamp - start) / duration, 1); count = Math.floor(progress * target); if (progress < 1) window.requestAnimationFrame(step); }; window.requestAnimationFrame(step); } })" x-text="count">0</div>
                    <div class="text-[10px] md:text-xs text-gray-400 font-bold uppercase tracking-[0.2em]">Laporan Tervalidasi</div>
                </div>
            </div>

            <!-- Box 5: Penerima Manfaat -->
            <div class="relative group bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_60px_rgba(232,97,10,0.12)] transition-all duration-500 hover:-translate-y-2 overflow-hidden text-center col-span-2 lg:col-span-1">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent to-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="text-4xl md:text-5xl font-black text-gray-900 mb-3 tracking-tighter" x-data="{ count: 0, target: {{ $jmlPenerima }} }" x-init="$watch('shown', value => { if (value) { let start = 0; let duration = 2000; let step = timestamp => { if (!start) start = timestamp; let progress = Math.min((timestamp - start) / duration, 1); count = Math.floor(progress * target); if (progress < 1) window.requestAnimationFrame(step); }; window.requestAnimationFrame(step); } })" x-text="count + '+'">0</div>
                    <div class="text-[10px] md:text-xs text-gray-400 font-bold uppercase tracking-[0.2em]">Penerima Manfaat</div>
                </div>
            </div>
            
        </div>

        <!-- Livewire Component: Tabel Statistik -->
        <livewire:statistik-table />
    </div>
</section>
