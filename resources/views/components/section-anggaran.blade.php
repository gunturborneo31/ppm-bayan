@php
    // Total anggaran: ambil dari DB jika ada, fallback ke data aktual Sustainability Report 2024
    $totalAnggaranDB = App\Models\LaporanCsr::sum('nominal');
    $totalAnggaran = $totalAnggaranDB > 0 ? $totalAnggaranDB : 124800000000; // Rp 124.8 miliar
    $anggaranMiliar = round($totalAnggaran / 1000000000, 1);
@endphp

<section class="py-16 md:py-24 bg-gray-50 relative overflow-hidden border-t border-gray-100">
    <!-- Decorative blurs -->
    <div class="absolute top-0 left-0 w-96 h-96 bg-[var(--color-primary)]/5 rounded-full blur-3xl -translate-y-1/2 -translate-x-1/3"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div x-data="{ shown: false, count: 0 }" x-intersect.half="shown = true"
             x-init="$watch('shown', value => {
                 if (value) {
                     let target = {{ $anggaranMiliar * 10 }};
                     let start = 0;
                     let duration = 2500;
                     let step = timestamp => {
                         if (!start) start = timestamp;
                         let progress = Math.min((timestamp - start) / duration, 1);
                         let eased = 1 - Math.pow(1 - progress, 3);
                         count = (eased * target / 10).toFixed(1);
                         if (progress < 1) window.requestAnimationFrame(step);
                     };
                     window.requestAnimationFrame(step);
                 }
             })">
            <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-8 md:p-12 shadow-[0_20px_60px_rgba(0,0,0,0.15)]">
                <!-- Decorative elements -->
                <div class="absolute top-0 right-0 w-96 h-96 bg-[var(--color-primary)]/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-[var(--color-primary)]/5 rounded-full blur-3xl translate-y-1/2 -translate-x-1/3"></div>
                <div class="absolute top-6 right-8 w-20 h-20 border border-white/5 rounded-full"></div>
                <div class="absolute bottom-8 right-24 w-12 h-12 border border-white/5 rounded-full"></div>

                <div class="relative z-10 flex flex-col lg:flex-row items-center lg:items-end justify-between gap-8">
                    <!-- Left: Main Budget Number -->
                    <div class="text-center lg:text-left">
                        <div class="flex items-center justify-center lg:justify-start gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-[var(--color-primary)] flex items-center justify-center shadow-lg shadow-[var(--color-primary)]/30">
                                <span class="material-icons text-white text-xl">account_balance_wallet</span>
                            </div>
                            <span class="text-[10px] md:text-xs font-bold uppercase tracking-[0.25em] text-gray-400">Total Realisasi Anggaran PPM</span>
                        </div>
                        
                        <div class="flex items-baseline justify-center lg:justify-start gap-3">
                            <span class="text-[var(--color-primary)] text-2xl md:text-3xl font-bold">Rp</span>
                            <span class="text-5xl md:text-7xl lg:text-8xl font-black text-white tracking-tighter leading-none" x-text="count">0</span>
                            <span class="text-2xl md:text-3xl font-bold text-gray-400">Miliar</span>
                        </div>
                        
                        <p class="text-gray-500 text-sm mt-3">Tahun {{ date('Y') }} · Seluruh anak perusahaan Bayan Group</p>
                    </div>

                    <!-- Right: Badge & Stats -->
                    <div class="flex flex-col items-center lg:items-end gap-4">
                        <!-- Kenaikan Badge -->
                        <div class="inline-flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-5 py-2.5 rounded-full">
                            <span class="material-icons text-lg">trending_up</span>
                            <span class="text-sm font-bold">+39%</span>
                            <span class="text-xs text-emerald-400/70">dari tahun lalu</span>
                        </div>
                        
                        <!-- Quick Stats Row -->
                        <div class="flex items-center gap-6 text-center">
                            <div>
                                <div class="text-2xl md:text-3xl font-black text-white">74</div>
                                <div class="text-[9px] text-gray-500 uppercase tracking-widest font-bold mt-1">Desa</div>
                            </div>
                            <div class="w-px h-10 bg-white/10"></div>
                            <div>
                                <div class="text-2xl md:text-3xl font-black text-white">18<span class="text-lg text-gray-400">rb+</span></div>
                                <div class="text-[9px] text-gray-500 uppercase tracking-widest font-bold mt-1">Penerima</div>
                            </div>
                            <div class="w-px h-10 bg-white/10"></div>
                            <div>
                                <div class="text-2xl md:text-3xl font-black text-[var(--color-primary)]">8</div>
                                <div class="text-[9px] text-gray-500 uppercase tracking-widest font-bold mt-1">Pilar PPM</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom bar indicator -->
                <div class="mt-8 pt-6 border-t border-white/5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <span class="material-icons text-[14px] text-[var(--color-primary)]">verified</span>
                            Sumber: Sustainability Report PT Bayan Resources Tbk
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Diperbarui berkala
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
