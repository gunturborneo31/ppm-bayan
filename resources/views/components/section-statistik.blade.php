<section id="statistik" class="py-16 md:py-24 bg-white relative border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16" x-data="{ shown: false }" x-intersect.half="shown = true">
            <span class="text-[var(--color-primary)] font-bold uppercase tracking-[0.3em] text-[10px] mb-4 block">Our Impact in Numbers</span>
            <h2 class="text-3xl md:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Statistik Pencapaian</h2>
            <p class="text-gray-500 max-w-2xl mx-auto text-base md:text-lg">Ringkasan capaian berdasarkan total program, kegiatan, pagu, realisasi, dan sisa anggaran.</p>
        </div>

        @php
            $totalProgram = (int) App\Models\Program::query()->count();
            $totalKegiatan = (int) App\Models\Kegiatan::query()->count();
            $totalPagu = (float) (App\Models\Program::query()->sum('rencana_biaya') ?? 0);
            $totalRealisasi = (float) (App\Models\Realisasi::query()->sum('realisasi_biaya') ?? 0);
            $sisaAnggaran = $totalPagu - $totalRealisasi;
            $totalPaguMiliar = $totalPagu / 1000000000;
            $totalRealisasiMiliar = $totalRealisasi / 1000000000;
            $sisaAnggaranMiliar = $sisaAnggaran / 1000000000;
        @endphp

        <!-- Counters -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 md:gap-6 mb-20">

            <!-- Box 1: Program -->
            <div class="relative group bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_60px_rgba(232,97,10,0.12)] transition-all duration-500 hover:-translate-y-2 overflow-hidden text-center">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent to-[var(--color-primary)]/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="text-4xl md:text-5xl font-black text-gray-900 mb-3 tracking-tighter">{{ number_format($totalProgram, 0, ',', '.') }}</div>
                    <div class="text-[10px] md:text-xs text-gray-400 font-bold uppercase tracking-[0.2em]">Total Program</div>
                </div>
            </div>

            <!-- Box 2: Kegiatan -->
            <div class="relative group bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_60px_rgba(232,97,10,0.12)] transition-all duration-500 hover:-translate-y-2 overflow-hidden text-center">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent to-[var(--color-primary)]/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="text-4xl md:text-5xl font-black text-gray-900 mb-3 tracking-tighter">{{ number_format($totalKegiatan, 0, ',', '.') }}</div>
                    <div class="text-[10px] md:text-xs text-gray-400 font-bold uppercase tracking-[0.2em]">Total Kegiatan</div>
                </div>
            </div>

            <!-- Box 3: Total Pagu -->
            <div class="relative group bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_60px_rgba(232,97,10,0.12)] transition-all duration-500 hover:-translate-y-2 overflow-hidden text-center">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent to-[var(--color-primary)]/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="text-2xl md:text-3xl font-black text-gray-900 mb-3 tracking-tight">Rp {{ number_format($totalPaguMiliar, 1, ',', '.') }} M</div>
                    <div class="text-[10px] md:text-xs text-gray-400 font-bold uppercase tracking-[0.2em]">Total Pagu</div>
                </div>
            </div>

            <!-- Box 4: Total Realisasi -->
            <div class="relative group bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_60px_rgba(232,97,10,0.12)] transition-all duration-500 hover:-translate-y-2 overflow-hidden text-center">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent to-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="text-2xl md:text-3xl font-black text-gray-900 mb-3 tracking-tight">Rp {{ number_format($totalRealisasiMiliar, 1, ',', '.') }} M</div>
                    <div class="text-[10px] md:text-xs text-gray-400 font-bold uppercase tracking-[0.2em]">Total Realisasi</div>
                </div>
            </div>

            <!-- Box 5: Sisa Anggaran -->
            <div class="relative group bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_60px_rgba(232,97,10,0.12)] transition-all duration-500 hover:-translate-y-2 overflow-hidden text-center">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent to-amber-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="text-2xl md:text-3xl font-black mb-3 tracking-tight {{ $sisaAnggaran < 0 ? 'text-red-600' : 'text-gray-900' }}">Rp {{ number_format($sisaAnggaranMiliar, 1, ',', '.') }} M</div>
                    <div class="text-[10px] md:text-xs text-gray-400 font-bold uppercase tracking-[0.2em]">Sisa Anggaran</div>
                </div>
            </div>
            
        </div>

        <!-- Livewire Component: Tabel Statistik -->
        <livewire:statistik-table />
    </div>
</section>
