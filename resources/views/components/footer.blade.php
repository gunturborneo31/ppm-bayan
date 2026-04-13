<footer class="bg-[#1a1a1a] pt-16 pb-8 border-t border-white/10 text-white relative overflow-hidden">
    <!-- Texture overlay -->
    <div class="absolute inset-0 opacity-5" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 lg:gap-16 mb-16">
            
            <!-- Kolom 1: Logo + Counter -->
            <div class="flex flex-col">
                <div class="flex flex-col items-start gap-4 mb-10 group">
                    <img src="{{ asset('logo-bayan.png') }}" 
                         alt="Bayan Group Logo" 
                         class="h-10 w-auto object-contain transform  group-hover:opacity-100 transition-opacity drop-shadow-[0_0_15px_rgba(255,255,255,0.1)]">
                    <div class="pl-3 border-l-2 border-[var(--color-primary)]/40 mt-1">
                        <span class="text-xl font-black text-white block leading-none tracking-tight">PPM Portal</span>
                        <span class="text-[9px] font-bold text-[var(--color-primary)] uppercase tracking-[0.2em] mt-2 block">Corporate Social Responsibility</span>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-white/5 to-transparent border border-white/10 rounded-[2rem] p-6 relative overflow-hidden shadow-2xl group/counter mt-auto">
                    <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-[var(--color-primary)]/5 rounded-full blur-3xl group-hover/counter:bg-[var(--color-primary)]/10 transition-colors"></div>
                    
                    @php
                        try {
                            $hariIni = App\Models\Kunjungan::where('tanggal', date('Y-m-d'))->value('jumlah') ?? 0;
                            $totalKon = App\Models\Kunjungan::sum('jumlah') ?? 0;
                        } catch (\Exception $e) { $hariIni = 16; $totalKon = 361; }
                    @endphp
                    
                    <div class="flex items-center gap-2 mb-6 cursor-default">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse shadow-[0_0_12px_rgba(34,197,94,1)]"></span>
                        <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Live Statistics</span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[9px] text-gray-500 font-bold uppercase tracking-widest mb-1">Today's</p>
                            <p class="text-3xl font-black text-white tracking-tighter">{{ number_format($hariIni, 0, ',', '.') }}</p>
                        </div>
                        <div class="pl-4 border-l border-white/5">
                            <p class="text-[9px] text-gray-500 font-bold uppercase tracking-widest mb-1">Total</p>
                            <p class="text-3xl font-black text-[var(--color-primary)] tracking-tighter">{{ number_format($totalKon, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom 2: Alamat -->
            <div class="flex flex-col">
                <h4 class="text-sm font-black mb-6 text-white tracking-widest uppercase opacity-80">Kantor Pusat</h4>
                <div class="flex items-start text-gray-300 mb-6 group cursor-default">
                    <div class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mr-4 shrink-0 group-hover:border-[var(--color-primary)]/50 transition-colors">
                        <span class="material-icons text-[var(--color-primary)] text-lg">location_on</span>
                    </div>
                    <p class="text-[13px] leading-relaxed text-gray-400">
                        <strong class="text-white block mb-1 text-sm">Office 8 Building, 37th Floor</strong>
                        Sudirman Central Business District (SCBD)<br>
                        Lot 28, Jl. Jend. Sudirman Kav. 52-53<br>
                        Jakarta 12190, Indonesia
                    </p>
                </div>
                <!-- Mini map link to Google Maps -->
                <a href="https://maps.app.goo.gl/uP9bYJ99K2W2rWq69" target="_blank" rel="noopener noreferrer" class="w-full h-32 bg-white/5 rounded-3xl overflow-hidden relative group cursor-pointer border border-white/10 shadow-xl mt-auto block">
                    <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Map View" class="absolute inset-0 w-full h-full object-cover grayscale opacity-30 group-hover:opacity-60 transition-all duration-700 group-hover:scale-110">
                    <div class="absolute inset-0 bg-black/40 group-hover:bg-black/20 transition-colors duration-500"></div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center z-10 drop-shadow-md">
                        <span class="material-icons text-white text-3xl mb-2 group-hover:scale-125 transition-transform duration-500 group-hover:text-[var(--color-primary)]">map</span>
                        <span class="text-[9px] font-black tracking-[0.2em] uppercase text-white/90">Buka di Google Maps</span>
                    </div>
                </a>
            </div>

            <!-- Kolom 3: Sosmed -->
            <div class="flex flex-col">
                <h4 class="text-sm font-black mb-6 text-white tracking-widest uppercase opacity-80">Jejaring Konneksi</h4>
                <div class="flex flex-col gap-3">
                    <a href="#" class="group flex items-center p-3.5 rounded-2xl bg-white/5 border border-white/5 hover:border-pink-500/30 hover:bg-white/10 transition-all duration-300 w-full">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-yellow-400 via-pink-500 to-purple-500 flex items-center justify-center text-white group-hover:scale-110 transition-transform shadow-md">
                            <i class="fa-brands fa-instagram text-lg"></i>
                        </div>
                        <div class="ml-4 flex flex-col">
                            <span class="text-sm font-bold text-gray-200 group-hover:text-white transition-colors">Instagram</span>
                            <span class="text-[10px] text-gray-500 group-hover:text-pink-400 transition-colors">@bayangroup</span>
                        </div>
                        <span class="material-icons ml-auto text-gray-600 text-sm group-hover:text-white transition-colors translate-x-2 opacity-0 group-hover:translate-x-0 group-hover:opacity-100">open_in_new</span>
                    </a>

                    <a href="#" class="group flex items-center p-3.5 rounded-2xl bg-white/5 border border-white/5 hover:border-red-500/30 hover:bg-white/10 transition-all duration-300 w-full">
                        <div class="w-10 h-10 rounded-xl bg-[#FF0000] flex items-center justify-center text-white group-hover:scale-110 transition-transform shadow-md">
                            <i class="fa-brands fa-youtube text-lg"></i>
                        </div>
                        <div class="ml-4 flex flex-col">
                            <span class="text-sm font-bold text-gray-200 group-hover:text-white transition-colors">YouTube</span>
                            <span class="text-[10px] text-gray-500 group-hover:text-red-400 transition-colors">Bayan Resources</span>
                        </div>
                        <span class="material-icons ml-auto text-gray-600 text-sm group-hover:text-white transition-colors translate-x-2 opacity-0 group-hover:translate-x-0 group-hover:opacity-100">open_in_new</span>
                    </a>

                    <a href="#" class="group flex items-center p-3.5 rounded-2xl bg-white/5 border border-white/5 hover:border-[#0077b5]/30 hover:bg-white/10 transition-all duration-300 w-full">
                        <div class="w-10 h-10 rounded-xl bg-[#0077b5] flex items-center justify-center text-white group-hover:scale-110 transition-transform shadow-md">
                            <i class="fa-brands fa-linkedin-in text-lg"></i>
                        </div>
                        <div class="ml-4 flex flex-col">
                            <span class="text-sm font-bold text-gray-200 group-hover:text-white transition-colors">LinkedIn</span>
                            <span class="text-[10px] text-gray-500 group-hover:text-[#0077b5] transition-colors">PT Bayan Resources Tbk</span>
                        </div>
                        <span class="material-icons ml-auto text-gray-600 text-sm group-hover:text-white transition-colors translate-x-2 opacity-0 group-hover:translate-x-0 group-hover:opacity-100">open_in_new</span>
                    </a>

                    <a href="http://www.bayan.com.sg/" target="_blank" rel="noopener noreferrer" class="group flex items-center p-3.5 rounded-2xl bg-white/5 border border-white/5 hover:border-[var(--color-primary)]/30 hover:bg-white/10 transition-all duration-300 w-full mt-2">
                        <div class="w-10 h-10 rounded-xl bg-[var(--color-primary)] flex items-center justify-center text-white group-hover:scale-110 transition-transform shadow-md">
                            <span class="material-icons">language</span>
                        </div>
                        <div class="ml-4 flex flex-col">
                            <span class="text-sm font-bold text-gray-200 group-hover:text-white transition-colors">Official Website</span>
                            <span class="text-[10px] text-gray-500 group-hover:text-[var(--color-primary)] transition-colors">www.bayan.com.sg</span>
                        </div>
                        <span class="material-icons ml-auto text-gray-600 text-sm group-hover:text-white transition-colors translate-x-2 opacity-0 group-hover:translate-x-0 group-hover:opacity-100">open_in_new</span>
                    </a>
                </div>
            </div>

        </div>
        
        <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
            <p>&copy; {{ date('Y') }} PT Bayan Resources Tbk. Hak Cipta Dilindungi.</p>
            <div class="flex space-x-6 mt-4 md:mt-0 font-medium">
                <a href="#" class="hover:text-white transition-colors drop-shadow-sm">Syarat & Ketentuan</a>
                <a href="#" class="hover:text-white transition-colors drop-shadow-sm">Kebijakan Privasi</a>
            </div>
        </div>
    </div>
</footer>
