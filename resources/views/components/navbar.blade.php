<nav x-data="{
        scrolled: false,
        mobileMenuOpen: false,
        activeDropdown: null,
        openDropdown(name) { this.activeDropdown = name },
        closeDropdown() { this.activeDropdown = null }
     }"
     @scroll.window="scrolled = (window.pageYOffset > 50)"
     :class="{ 'bg-white/98 backdrop-blur-xl shadow-lg border-b border-gray-100': scrolled, 'bg-transparent border-transparent': !scrolled }"
     class="fixed top-0 w-full z-50 transition-all duration-500">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">

            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center gap-4">
                <a href="/" class="flex items-center gap-4">
                    <div class="transition-all duration-300 flex items-center">
                        <img src="{{ asset('logo-bayan.png') }}"
                             alt="Bayan Group Logo"
                             class="h-7 md:h-9 w-auto object-contain">
                    </div>
                    <div class="flex flex-col transition-colors duration-300 border-l-2 pl-4"
                         :class="scrolled ? 'border-primary/20' : 'border-white/20'">
                        <span class="font-black text-lg md:text-xl leading-none tracking-tight"
                              :class="scrolled ? 'text-primary-dark' : 'text-white drop-shadow-md'">PPM</span>
                        <span class="text-[9px] font-bold uppercase tracking-[0.3em] mt-1"
                              :class="scrolled ? 'text-accent' : 'text-gray-200'">PT Bayan Resources


</span>
                    </div>
                </a>
            </div>

            <!-- Desktop Nav -->
            <div class="hidden md:flex md:items-center md:gap-1 lg:gap-2">

                <!-- Beranda -->
                <a href="/#beranda"
                   class="px-3 py-2 rounded-lg font-bold text-[11px] lg:text-[13px] uppercase tracking-widest transition-all duration-200 hover:bg-white/10"
                   :class="scrolled ? 'text-gray-700 hover:text-primary hover:bg-primary/5' : 'text-white/90 hover:text-white'">
                    Beranda
                </a>

                <!-- Tentang Kami (Dropdown) -->
                <div class="relative" @mouseenter="openDropdown('tentang')" @mouseleave="closeDropdown()">
                    <button class="flex items-center gap-1 px-3 py-2 rounded-lg font-bold text-[11px] lg:text-[13px] uppercase tracking-widest transition-all duration-200"
                            :class="scrolled ? 'text-gray-700 hover:text-primary hover:bg-primary/5' : 'text-white/90 hover:text-white hover:bg-white/10'">
                        Tentang Kami
                        <span class="material-icons text-sm transition-transform duration-200"
                              :class="activeDropdown === 'tentang' ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <!-- Dropdown Panel -->
                    <div x-show="activeDropdown === 'tentang'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                         class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden"
                         style="display: none;">
                        <div class="p-2">
                            <a href="/#tentang" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-orange-50 text-gray-700 hover:text-orange-600 transition group">
                                <span class="material-icons text-lg text-orange-400">info</span>
                                <div>
                                    <div class="font-semibold text-sm">Profil Perusahaan</div>
                                    <div class="text-xs text-gray-400">Visi, misi & operasional</div>
                                </div>
                            </a>
                            <a href="/#dasar-hukum" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-orange-50 text-gray-700 hover:text-orange-600 transition group">
                                <span class="material-icons text-lg text-orange-400">gavel</span>
                                <div>
                                    <div class="font-semibold text-sm">Dasar Hukum</div>
                                    <div class="text-xs text-gray-400">Regulasi & payung hukum</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Program PPM (Dropdown) -->
                <div class="relative" @mouseenter="openDropdown('program')" @mouseleave="closeDropdown()">
                    <button class="flex items-center gap-1 px-3 py-2 rounded-lg font-bold text-[11px] lg:text-[13px] uppercase tracking-widest transition-all duration-200"
                            :class="scrolled ? 'text-gray-700 hover:text-primary hover:bg-primary/5' : 'text-white/90 hover:text-white hover:bg-white/10'">
                        Program PPM
                        <span class="material-icons text-sm transition-transform duration-200"
                              :class="activeDropdown === 'program' ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="activeDropdown === 'program'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                         class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden"
                         style="display: none;">
                        <div class="p-2">
                            <div class="px-4 py-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pilar PPM</div>
                            <a href="/#bidang" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-orange-50 text-gray-700 hover:text-orange-600 transition">
                                <span class="material-icons text-lg text-orange-400">category</span>
                                <div>
                                    <div class="font-semibold text-sm">Bidang Program</div>
                                    <div class="text-xs text-gray-400">8 pilar pemberdayaan</div>
                                </div>
                            </a>
                            <a href="/#anggaran" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-orange-50 text-gray-700 hover:text-orange-600 transition">
                                <span class="material-icons text-lg text-orange-400">account_balance_wallet</span>
                                <div>
                                    <div class="font-semibold text-sm">Anggaran PPM</div>
                                    <div class="text-xs text-gray-400">Realisasi & rencana</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistik -->
                <a href="/#statistik"
                   class="px-3 py-2 rounded-lg font-bold text-[11px] lg:text-[13px] uppercase tracking-widest transition-all duration-200"
                   :class="scrolled ? 'text-gray-700 hover:text-primary hover:bg-primary/5' : 'text-white/90 hover:text-white hover:bg-white/10'">
                    Statistik
                </a>

                <!-- Berita (Dropdown) -->
                <div class="relative" @mouseenter="openDropdown('berita')" @mouseleave="closeDropdown()">
                    <button class="flex items-center gap-1 px-3 py-2 rounded-lg font-bold text-[11px] lg:text-[13px] uppercase tracking-widest transition-all duration-200"
                            :class="scrolled ? 'text-gray-700 hover:text-primary hover:bg-primary/5' : 'text-white/90 hover:text-white hover:bg-white/10'">
                        Berita
                        <span class="material-icons text-sm transition-transform duration-200"
                              :class="activeDropdown === 'berita' ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="activeDropdown === 'berita'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                         class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden"
                         style="display: none;">
                        <div class="p-2">
                            <a href="/#berita" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-orange-50 text-gray-700 hover:text-orange-600 transition">
                                <span class="material-icons text-lg text-orange-400">home</span>
                                <div>
                                    <div class="font-semibold text-sm">Berita Terkini</div>
                                    <div class="text-xs text-gray-400">Di halaman utama</div>
                                </div>
                            </a>
                            <a href="/berita" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-orange-50 text-gray-700 hover:text-orange-600 transition">
                                <span class="material-icons text-lg text-orange-400">newspaper</span>
                                <div>
                                    <div class="font-semibold text-sm">Semua Berita</div>
                                    <div class="text-xs text-gray-400">Arsip lengkap artikel</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Hamburger -->
            <div class="flex items-center gap-3">

                <!-- Hamburger -->
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="md:hidden w-10 h-10 flex items-center justify-center rounded-xl transition-all duration-200"
                        :class="scrolled ? 'text-gray-700 hover:bg-gray-100' : 'text-white hover:bg-white/10'">
                    <span class="material-icons text-2xl transition-transform duration-200"
                          :class="mobileMenuOpen ? 'rotate-90' : ''"
                          x-text="mobileMenuOpen ? 'close' : 'menu'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         @click.away="mobileMenuOpen = false"
         class="md:hidden absolute top-20 left-0 w-full bg-white/98 backdrop-blur-xl border-b border-gray-100 shadow-2xl"
         style="display: none;">

        <div class="max-w-7xl mx-auto px-4 py-4 space-y-1">

            <!-- Nav Groups -->
            <a @click="mobileMenuOpen = false" href="/"
               class="flex items-center gap-3 px-4 py-3.5 rounded-xl text-gray-800 hover:text-orange-600 hover:bg-orange-50 transition font-semibold">
                <span class="material-icons text-lg text-orange-400">home</span> Beranda
            </a>

            <!-- Divider Label -->
            <div class="px-4 pt-3 pb-1">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tentang</p>
            </div>
            <a @click="mobileMenuOpen = false" href="/#tentang"
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:text-orange-600 hover:bg-orange-50 transition">
                <span class="material-icons text-lg text-orange-400">info</span>
                <div>
                    <div class="font-semibold text-sm">Profil Perusahaan</div>
                    <div class="text-xs text-gray-400">Visi, misi & peta operasional</div>
                </div>
            </a>
            <a @click="mobileMenuOpen = false" href="/#dasar-hukum"
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:text-orange-600 hover:bg-orange-50 transition">
                <span class="material-icons text-lg text-orange-400">gavel</span>
                <div>
                    <div class="font-semibold text-sm">Dasar Hukum</div>
                    <div class="text-xs text-gray-400">Regulasi & payung hukum</div>
                </div>
            </a>

            <div class="px-4 pt-3 pb-1">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Program</p>
            </div>
            <a @click="mobileMenuOpen = false" href="/#bidang"
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:text-orange-600 hover:bg-orange-50 transition">
                <span class="material-icons text-lg text-orange-400">category</span>
                <div>
                    <div class="font-semibold text-sm">Bidang Program</div>
                    <div class="text-xs text-gray-400">8 pilar pemberdayaan</div>
                </div>
            </a>
            <a @click="mobileMenuOpen = false" href="/#statistik"
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:text-orange-600 hover:bg-orange-50 transition">
                <span class="material-icons text-lg text-orange-400">bar_chart</span>
                <div>
                    <div class="font-semibold text-sm">Statistik & Data</div>
                    <div class="text-xs text-gray-400">Visualisasi program PPM</div>
                </div>
            </a>

            <div class="px-4 pt-3 pb-1">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Informasi</p>
            </div>
            <a @click="mobileMenuOpen = false" href="/berita"
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:text-orange-600 hover:bg-orange-50 transition">
                <span class="material-icons text-lg text-orange-400">newspaper</span>
                <div>
                    <div class="font-semibold text-sm">Semua Berita</div>
                    <div class="text-xs text-gray-400">Arsip artikel & informasi</div>
                </div>
            </a>


        </div>
    </div>
</nav>
