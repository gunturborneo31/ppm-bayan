@php
    $totalAnggaranDB = App\Models\Program::sum('rencana_biaya');
    $totalAnggaran = $totalAnggaranDB > 0 ? $totalAnggaranDB : 124800000000;
    $anggaranMiliar = round($totalAnggaran / 1000000000, 1);
    $anggaranMiliarFormatted = number_format($totalAnggaranDB,2, ',', '.');
@endphp

<section id="tentang" class="py-20 lg:py-28 bg-gray-50 overflow-hidden relative">
    <!-- Decorative patterns -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-accent/10 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-10 lg:gap-14 items-stretch" 
             x-data="{ shown: false }" 
             x-intersect.half="shown = true">
             
            <!-- LEFT COLUMN: Teks + Budget Card (2/5 width) -->
            <div class="order-2 lg:order-1 lg:col-span-2" 
                 :class="shown ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-12'" 
                 style="transition: all 1s cubic-bezier(0.16, 1, 0.3, 1);">
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-6 leading-[1.1] tracking-tight">
                    Tentang <br><span class="text-[var(--color-primary)]">PPM Bayan Group</span>
                </h1>
                <div class="space-y-3 text-gray-600 mb-8 leading-relaxed text-sm md:text-base">
                    <p>
                        Sistem Informasi Pengembangan dan Pemberdayaan Masyarakat (PPM) Bayan Group adalah portal komunikasi publik yang menyajikan informasi komprehensif terkait pelaksanaan tanggung jawab sosial dan lingkungan perusahaan secara dinamis dan terintegrasi.
                    </p>
                    <p>
                        Tujuan utama portal ini adalah untuk memastikan keterbukaan informasi kepada masyarakat, pemerintah, dan pemangku kepentingan lainnya mengenai dedikasi Bayan Group dalam memberdayakan masyarakat di sekitar area operasional tambang.
                    </p>
                </div>
                
                <!-- ══════════════════════════════════════════ -->
                <!-- COMPACT BUDGET CARD                        -->
                <!-- ══════════════════════════════════════════ -->
                <div x-data="{ count: 0 }"
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
                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-6 md:p-8 shadow-[0_15px_40px_rgba(0,0,0,0.12)]">
                        <!-- Decorative glow -->
                        <div class="absolute top-0 right-0 w-40 h-40 bg-[var(--color-primary)]/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>

                        <div class="relative z-10">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-8 h-8 rounded-lg bg-[var(--color-primary)] flex items-center justify-center shadow-lg shadow-[var(--color-primary)]/30">
                                    <span class="material-icons text-white text-base">account_balance_wallet</span>
                                </div>
                                <span class="text-[6px] md:text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400">Total Pagu PPM</span>
                            </div>
                            
                            <div class="flex items-baseline gap-2 mb-2">
                                <span class="text-white text-2xl font-bold">Rp</span>
                                <span class="text-4xl md:text-5xl font-black text-white tracking-tighter leading-none">{{ $anggaranMiliarFormatted }}</span>
                                {{-- <span class="text-2xl font-bold text-gray-400">Miliar</span> --}}
                            </div>

                            <div class="flex items-center justify-between mt-4">
                                <div class="flex items-center gap-3">
                                    <div class="inline-flex items-center gap-1.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-3 py-1 rounded-full">
                                        <span class="material-icons text-sm">trending_up</span>
                                        <span class="text-xs font-bold">+39%</span>
                                    </div>
                                    <span class="text-[10px] text-gray-500 hidden sm:inline-block">dari tahun sebelumnya</span>
                                </div>

                                <div class="flex items-center gap-1.5 text-[10px] text-gray-400 font-medium">
                                    <span class="material-icons text-[12px] text-[var(--color-primary)]">verified</span>
                                    Sustainability Report {{ date('Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- RIGHT COLUMN: Peta Kalimantan Besar (3/5 width) -->
            <div class="order-1 lg:order-2 lg:col-span-3 relative"
                 :class="shown ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-12'" 
                 style="transition: all 1s cubic-bezier(0.16, 1, 0.3, 1); transition-delay: 200ms;">
                
                <div class="relative w-full bg-white rounded-3xl border border-gray-100 shadow-[0_20px_60px_rgba(0,0,0,0.05)] p-2 group/map overflow-hidden"
                     x-data="{
                        active: null,
                        naturalW: 640, naturalH: 640,
                        renderedW: 0, renderedH: 0,
                        setActive(name) { this.active = name },
                        clearActive() { this.active = null },
                        initMap() {
                            const img = this.$refs.mapImg;
                            const update = () => requestAnimationFrame(() => {
                                if (img.naturalWidth > 0) this.naturalW = img.naturalWidth;
                                if (img.naturalHeight > 0) this.naturalH = img.naturalHeight;
                                if (img.offsetWidth > 0)  this.renderedW = img.offsetWidth;
                                if (img.offsetHeight > 0) this.renderedH = img.offsetHeight;
                            });
                            if (img.complete && img.naturalWidth > 0) update();
                            else img.addEventListener('load', update);
                            window.addEventListener('resize', update);
                        },
                        pin(px, py) {
                            const rW = this.renderedW || this.$refs.mapImg.offsetWidth || 560;
                            const rH = this.renderedH || this.$refs.mapImg.offsetHeight || 560;
                            const scaleX = rW / this.naturalW;
                            const scaleY = rH / this.naturalH;
                            return 'left:' + (px * scaleX) + 'px;top:' + (py * scaleY) + 'px;';
                        }
                     }"
                     x-init="$nextTick(() => this.initMap())">
                    <!-- Kalimantan Map Image -->
                    <div class="relative">
                    <img x-ref="mapImg"
                         src="{{ asset('kalimantan_map_ppm.png') }}"
                         alt="Peta Operasional Bayan Group"
                         class="w-full h-auto block rounded-3xl">

                    <div class="absolute inset-0 bg-gradient-to-br from-white via-transparent to-[var(--color-primary)]/5 opacity-30 rounded-3xl pointer-events-none"></div>

                    <!-- Operational Markers Overlay -->
                    <div class="absolute inset-0 z-10 overflow-visible" style="pointer-events:none">

                        <!-- ═══════════════════════════════════════════════════ -->
                        <!-- Site Location: Tabang Project                       -->
                        <!-- Koordinat: 0.56°N · 116.13°E                       -->
                        <!-- DIKOREKSI: left 57.8% → 40.0%, top tetap 36.7%    -->
                        <!-- ═══════════════════════════════════════════════════ -->
                        <div class="absolute w-8 h-8 -ml-4 -mt-4 transition-all duration-200 cursor-pointer hover:scale-125 pointer-events-auto"
                             :class="active === 'tabang' ? 'z-50' : 'z-10'"
                             style="left:60%;top:38%;"
                             @mouseenter="setActive('tabang')" @mouseleave="clearActive()">
                            <div class="relative flex items-center justify-center w-full h-full">
                                <span class="absolute inline-flex h-12 w-12 rounded-full bg-[#E8610A]/20 animate-ping"></span>
                                <svg class="w-8 h-8 drop-shadow-lg" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2C8.13 2 5 5.13 5 9C5 14.25 12 22 12 22C12 22 19 14.25 19 9C19 5.13 15.87 2 12 2Z" fill="#E8610A" stroke="white" stroke-width="1.5"/>
                                    <circle cx="12" cy="9" r="3" fill="white"/>
                                </svg>
                            </div>
                            <!-- Tooltip Tabang -->
                            <div x-show="active === 'tabang'"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                 class="absolute z-50 pointer-events-none"
                                 style="top: calc(100% + 15px); left: 50%; transform: translateX(-50%);">
                                <div class="w-3 h-3 bg-gray-900/90 rotate-45 mx-auto -mb-1.5 border-l border-t border-white/10"></div>
                                <div class="bg-gray-900/90 backdrop-blur-md text-white rounded-2xl shadow-2xl border border-white/10 px-5 py-3 min-w-[200px]">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-2 h-2 rounded-full bg-[#E8610A] animate-pulse"></div>
                                        <span class="text-[11px] font-bold uppercase tracking-widest leading-none">Tabang Project</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 font-mono">0.56°N · 116.13°E</p>
                                    <p class="text-[10px] text-gray-300 mt-1">Wilayah Operasional Utama PPM</p>
                                    <div class="mt-2 pt-2 border-t border-white/10 flex items-center gap-2">
                                        <span class="material-icons text-[#E8610A] text-[12px]">location_on</span>
                                        <span class="text-[9px] text-gray-400 uppercase tracking-wider">Kutai Kartanegara</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ═══════════════════════════════════════════════════ -->
                        <!-- Site Location: East Kutai Sangatta                  -->
                        <!-- Koordinat: 0.53°N · 117.52°E  — SUDAH AKURAT      -->
                        <!-- ═══════════════════════════════════════════════════ -->
                        <div class="absolute w-6 h-6 -ml-3 -mt-3 transition-all duration-200 cursor-pointer hover:scale-125 pointer-events-auto"
                             :class="active === 'east-kutai' ? 'z-50' : 'z-10'"
                             style="left:69%;top:33%;"
                             @mouseenter="setActive('east-kutai')" @mouseleave="clearActive()">
                            <svg class="w-6 h-6 drop-shadow-md" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C8.13 2 5 5.13 5 9C5 14.25 12 22 12 22C12 22 19 14.25 19 9C19 5.13 15.87 2 12 2Z" fill="white" stroke="#E8610A" stroke-width="1.5"/>
                                <circle cx="12" cy="9" r="3" fill="#E8610A"/>
                            </svg>
                            <div x-show="active === 'east-kutai'"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 class="absolute z-50 pointer-events-none"
                                 style="top: calc(100% + 15px); left: 50%; transform: translateX(-50%);">
                                <div class="w-3 h-3 bg-gray-900/90 rotate-45 mx-auto -mb-1.5 border-l border-t border-white/10"></div>
                                <div class="bg-gray-900/90 backdrop-blur-md text-white rounded-2xl shadow-2xl border border-white/10 px-5 py-3 min-w-[200px]">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-2 h-2 rounded-full bg-[#E8610A] animate-pulse"></div>
                                        <span class="text-[11px] font-bold uppercase tracking-widest leading-none">East Kutai Sangatta</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 font-mono">0.53°N · 117.52°E</p>
                                    <p class="text-[10px] text-gray-300 mt-1">Area Eksplorasi Timur</p>
                                    <div class="mt-2 pt-2 border-t border-white/10 flex items-center gap-2">
                                        <span class="material-icons text-[#E8610A] text-[12px]">location_on</span>
                                        <span class="text-[9px] text-gray-400 uppercase tracking-wider">Kutai Timur</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ═══════════════════════════════════════════════════ -->
                        <!-- Site Location: West Kutai Sendawar                  -->
                        <!-- Koordinat: 0.17°S · 115.65°E  — SUDAH AKURAT      -->
                        <!-- ═══════════════════════════════════════════════════ -->
                        <div class="absolute w-6 h-6 -ml-3 -mt-3 transition-all duration-200 cursor-pointer hover:scale-125 pointer-events-auto"
                             :class="active === 'west-kutai' ? 'z-50' : 'z-10'"
                             style="left:58%;top:50%;"
                             @mouseenter="setActive('west-kutai')" @mouseleave="clearActive()">
                            <svg class="w-6 h-6 drop-shadow-md" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C8.13 2 5 5.13 5 9C5 14.25 12 22 12 22C12 22 19 14.25 19 9C19 5.13 15.87 2 12 2Z" fill="white" stroke="#E8610A" stroke-width="1.5"/>
                                <circle cx="12" cy="9" r="3" fill="#E8610A"/>
                            </svg>
                            <div x-show="active === 'west-kutai'"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 class="absolute z-50 pointer-events-none"
                                 style="bottom: calc(100% + 15px); left: 50%; transform: translateX(-50%);">
                                <div class="bg-gray-900/90 backdrop-blur-md text-white rounded-2xl shadow-2xl border border-white/10 px-5 py-3 min-w-[200px]">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-2 h-2 rounded-full bg-[#E8610A] animate-pulse"></div>
                                        <span class="text-[11px] font-bold uppercase tracking-widest leading-none">West Kutai Sendawar</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 font-mono">0.17°S · 115.65°E</p>
                                    <p class="text-[10px] text-gray-300 mt-1">Area Eksplorasi Barat</p>
                                    <div class="mt-2 pt-2 border-t border-white/10 flex items-center gap-2">
                                        <span class="material-icons text-[#E8610A] text-[12px]">location_on</span>
                                        <span class="text-[9px] text-gray-400 uppercase tracking-wider">Kutai Barat</span>
                                    </div>
                                </div>
                                <div class="w-3 h-3 bg-gray-900/90 rotate-45 mx-auto -mt-1.5 border-r border-b border-white/10"></div>
                            </div>
                        </div>

                        <!-- ═══════════════════════════════════════════════════ -->
                        <!-- Site Location: Satui (Kalsel)                       -->
                        <!-- Koordinat: 3.65°S · 115.51°E  — SUDAH AKURAT      -->
                        <!-- ═══════════════════════════════════════════════════ -->
                        <div class="absolute w-6 h-6 -ml-3 -mt-3 transition-all duration-200 cursor-pointer hover:scale-125 pointer-events-auto"
                             :class="active === 'satui' ? 'z-50' : 'z-10'"
                             style="left:60%;top:80%;"
                             @mouseenter="setActive('satui')" @mouseleave="clearActive()">
                            <svg class="w-6 h-6 drop-shadow-md" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C8.13 2 5 5.13 5 9C5 14.25 12 22 12 22C12 22 19 14.25 19 9C19 5.13 15.87 2 12 2Z" fill="white" stroke="#E8610A" stroke-width="1.5"/>
                                <circle cx="12" cy="9" r="3" fill="#E8610A"/>
                            </svg>
                            <div x-show="active === 'satui'"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 class="absolute z-50 pointer-events-none"
                                 style="bottom: calc(100% + 15px); left: 50%; transform: translateX(-50%);">
                                <div class="bg-gray-900/90 backdrop-blur-md text-white rounded-2xl shadow-2xl border border-white/10 px-5 py-3 min-w-[200px]">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-2 h-2 rounded-full bg-[#E8610A] animate-pulse"></div>
                                        <span class="text-[11px] font-bold uppercase tracking-widest leading-none">Satui Project (Kalsel)</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 font-mono">3.65°S · 115.51°E</p>
                                    <p class="text-[10px] text-gray-300 mt-1">Proyek Batubara Selatan</p>
                                    <div class="mt-2 pt-2 border-t border-white/10 flex items-center gap-2">
                                        <span class="material-icons text-[#E8610A] text-[12px]">location_on</span>
                                        <span class="text-[9px] text-gray-400 uppercase tracking-wider">Kalimantan Selatan</span>
                                    </div>
                                </div>
                                <div class="w-3 h-3 bg-gray-900/90 rotate-45 mx-auto -mt-1.5 border-r border-b border-white/10"></div>
                            </div>
                        </div>

                        <!-- ═══════════════════════════════════════════════════ -->
                        <!-- Facility: Terminal BCT Balikpapan                   -->
                        <!-- Koordinat: 1.20°S · 116.78°E                       -->
                        <!-- DIKOREKSI: left 64.7%→49.6%, top 55.9%→56.7%      -->
                        <!-- ═══════════════════════════════════════════════════ -->
                        <div class="absolute w-8 h-8 -ml-4 -mt-4 transition-all duration-200 cursor-pointer hover:scale-125 pointer-events-auto"
                             :class="active === 'bct' ? 'z-50' : 'z-10'"
                             style="left:65%;top:57%;"
                             @mouseenter="setActive('bct')" @mouseleave="clearActive()">
                            <div class="w-8 h-8 rounded-full bg-blue-600 border-2 border-white shadow-lg flex items-center justify-center animate-pulse">
                                <span class="material-icons text-white text-[16px]">directions_boat</span>
                            </div>
                            <div x-show="active === 'bct'"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 class="absolute z-50 pointer-events-none"
                                 style="bottom: calc(100% + 15px); left: 50%; transform: translateX(-50%);">
                                <div class="bg-gray-900/90 backdrop-blur-md text-white rounded-2xl shadow-2xl border border-white/10 px-5 py-3 min-w-[200px]">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                                        <span class="text-[11px] font-bold uppercase tracking-widest leading-none">BCT Balikpapan</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 font-mono">1.20°S · 116.78°E</p>
                                    <p class="text-[10px] text-gray-300 mt-1">Balikpapan Coal Terminal</p>
                                    <div class="mt-2 pt-2 border-t border-white/10 flex items-center gap-2">
                                        <span class="material-icons text-blue-500 text-[12px]">warehouse</span>
                                        <span class="text-[9px] text-gray-400 uppercase tracking-wider">Balikpapan</span>
                                    </div>
                                </div>
                                <div class="w-3 h-3 bg-gray-900/90 rotate-45 mx-auto -mt-1.5 border-r border-b border-white/10"></div>
                            </div>
                        </div>

                        <!-- ═══════════════════════════════════════════════════ -->
                        <!-- Facility: KFT (Offshore Floating Transfer)          -->
                        <!-- Koordinat: 1.30°S · 117.00°E                       -->
                        <!-- DIKOREKSI: left 68.5%→54.5%, top 57.5% tetap      -->
                        <!-- ═══════════════════════════════════════════════════ -->
                        <div class="absolute w-8 h-8 -ml-4 -mt-4 transition-all duration-200 cursor-pointer hover:scale-125 pointer-events-auto"
                             :class="active === 'kft' ? 'z-50' : 'z-10'"
                             style="left:71%;top:59%;"
                             @mouseenter="setActive('kft')" @mouseleave="clearActive()">
                            <div class="w-8 h-8 rounded-full bg-gray-800 border-2 border-white shadow-lg flex items-center justify-center animate-pulse" style="animation-delay: 1.5s">
                                <span class="material-icons text-white text-[16px]">anchor</span>
                            </div>
                            <div x-show="active === 'kft'"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 class="absolute z-50 pointer-events-none"
                                 style="bottom: calc(100% + 15px); left: 50%; transform: translateX(-50%);">
                                <div class="bg-gray-900/90 backdrop-blur-md text-white rounded-2xl shadow-2xl border border-white/10 px-5 py-3 min-w-[200px]">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-2 h-2 rounded-full bg-gray-400 animate-pulse"></div>
                                        <span class="text-[11px] font-bold uppercase tracking-widest leading-none">KFT (Offshore)</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 font-mono">1.30°S · 117.00°E</p>
                                    <p class="text-[10px] text-gray-300 mt-1">Floating Transfer Point</p>
                                    <div class="mt-2 pt-2 border-t border-white/10 flex items-center gap-2">
                                        <span class="material-icons text-gray-400 text-[12px]">sailing</span>
                                        <span class="text-[9px] text-gray-400 uppercase tracking-wider">Selat Makassar</span>
                                    </div>
                                </div>
                                <div class="w-3 h-3 bg-gray-900/90 rotate-45 mx-auto -mt-1.5 border-r border-b border-white/10"></div>
                            </div>
                        </div>

                    </div>
                    </div>{{-- close .relative image wrapper --}}
                    
                    <!-- Map Legend -->
                    <div class="flex flex-wrap gap-x-4 gap-y-2 bg-white/95 backdrop-blur-sm p-3 rounded-xl border border-gray-100 shadow-lg z-20 mt-2">
                        <div class="flex items-center gap-2">
                            <span class="material-icons text-[14px] text-orange-500">location_on</span>
                            <span class="text-[9px] font-bold text-gray-600 uppercase tracking-wider">Site Location</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-blue-600 shadow-sm shadow-blue-500"></div>
                            <span class="text-[9px] font-bold text-gray-600 uppercase tracking-wider">Terminal (BCT)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-gray-800 shadow-sm shadow-black"></div>
                            <span class="text-[9px] font-bold text-gray-600 uppercase tracking-wider">Floating Transfer (KFT)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>