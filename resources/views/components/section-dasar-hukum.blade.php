<section id="dasar-hukum" class="py-16 md:py-24 bg-white relative" x-data="{ modalOpen: false, modalData: null }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 relative z-10">
            <span class="text-[var(--color-primary)] font-bold uppercase tracking-[0.3em] text-[10px] mb-4 block">Regulatory Framework</span>
            <h2 class="text-3xl md:text-5xl font-extrabold text-gray-900 mb-6 tracking-tight">Dasar Hukum Pelaksanaan</h2>
            <p class="text-gray-500 max-w-2xl mx-auto text-base md:text-lg">
                Seluruh program dan pelaporan Tanggung Jawab Sosial dan Lingkungan (TJSL) kami didasarkan pada payung hukum yang kuat:
            </p>
        </div>

        @php
            $regulasi = App\Models\Regulasi::all();
            if($regulasi->isEmpty()) {
                $regulasi = [
                    (object)['judul' => 'UU No. 3 Tahun 2020', 'nomor_regulasi' => 'Perubahan atas UU Minerba No. 4/2009 tentang pertambangan mineral dan batubara.', 'link_dokumen' => '#', 'icon' => 'account_balance'],
                    (object)['judul' => 'UU No. 6 Tahun 2023', 'nomor_regulasi' => 'Undang-Undang Cipta Kerja terkait penyederhanaan perizinan berusaha berbasis risiko (OSS).', 'link_dokumen' => '#', 'icon' => 'edit_document'],
                    (object)['judul' => 'PP No. 96 Tahun 2021', 'nomor_regulasi' => 'Pelaksanaan kegiatan usaha pertambangan minerba serta perpanjangan PKP2B menjadi IUPK.', 'link_dokumen' => '#', 'icon' => 'settings_suggest'],
                    (object)['judul' => 'Permen ESDM No. 26 Tahun 2018', 'nomor_regulasi' => 'Penerapan Good Mining Practice (Kaidah Teknik Pertambangan yang Baik) secara berkelanjutan.', 'link_dokumen' => '#', 'icon' => 'verified'],
                    (object)['judul' => 'Kepmen ESDM No. 1824 K/2018', 'nomor_regulasi' => 'Pedoman Pelaksanaan Pengembangan dan Pemberdayaan Masyarakat (PPM) "Bayan Peduli".', 'link_dokumen' => '#', 'icon' => 'groups'],
                    (object)['judul' => 'Permen ESDM No. 10 Tahun 2023', 'nomor_regulasi' => 'Tata cara penyusunan, penyampaian, dan persetujuan Rencana Kerja dan Anggaran Biaya (RKAB).', 'link_dokumen' => '#', 'icon' => 'insert_chart'],
                ];
            }
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($regulasi as $index => $item)
            @php
                $itemData = [
                    'judul' => $item->judul,
                    'deskripsi' => $item->nomor_regulasi,
                    'icon' => $item->icon ?? 'gavel',
                    'link' => $item->link_dokumen ?? '#'
                ];
            @endphp
            <div class="group relative bg-white p-8 md:p-10 rounded-[2rem] border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_60px_rgba(232,97,10,0.12)] transition-all duration-500 hover:-translate-y-2 overflow-hidden cursor-pointer"
                 x-data="{ shown: false }" 
                 x-intersect.half="shown = true"
                 :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'" 
                 style="transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); transition-delay: {{ $index * 50 }}ms;"
                 @click='modalData = @json($itemData); modalOpen = true;'>
                
                <!-- Decorative Icon Background -->
                <div class="absolute -top-6 -right-6 w-32 h-32 bg-[var(--color-primary)]/5 rounded-full blur-2xl group-hover:bg-[var(--color-primary)]/10 transition-colors"></div>
                
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mb-6 text-[var(--color-primary)] group-hover:bg-[var(--color-primary)]  transition-all duration-500 shadow-sm transform group-hover:rotate-6">
                        <span class="material-icons text-2xl">{{ $item->icon ?? 'gavel' }}</span>
                    </div>
                    
                    <h3 class="text-xl font-extrabold text-gray-900 leading-tight group-hover:text-[var(--color-primary)] transition-colors">{{ $item->judul }}</h3>
                    <p class="mt-3 inline-flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400 group-hover:text-[var(--color-primary)] transition-colors">
                        <span class="material-icons text-sm">visibility</span>
                        Klik untuk preview
                    </p>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Modal Popup -->
        <div x-show="modalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;">
            <div @click.away="modalOpen = false" class="bg-white rounded-3xl w-full max-w-4xl shadow-2xl relative flex flex-col"
                 style="max-height: 90vh;"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95">
                
                <!-- Header -->
                <div class="flex items-center gap-4 p-6 border-b border-gray-100 shrink-0">
                    <div class="w-12 h-12 shrink-0 bg-[var(--color-primary)]/10 text-[var(--color-primary)] rounded-xl flex items-center justify-center">
                        <span class="material-icons text-2xl" x-text="modalData?.icon"></span>
                    </div>
                    <div class="flex flex-col flex-1">
                        <span class="text-[var(--color-primary)] font-bold uppercase tracking-widest text-[9px] mb-0.5">Regulasi PPM</span>
                        <h3 class="text-lg font-extrabold text-gray-900 leading-tight" x-text="modalData?.judul"></h3>
                        <p class="text-gray-400 text-xs mt-1" x-text="modalData?.deskripsi"></p>
                    </div>
                    <!-- Close Button -->
                    <button @click="modalOpen = false" class="shrink-0 w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:text-gray-900 hover:bg-gray-200 transition-colors">
                        <span class="material-icons text-lg">close</span>
                    </button>
                </div>

                <!-- Document Preview Area -->
                <div class="flex-1 overflow-hidden bg-gray-50 relative" style="min-height: 480px;">
                    
                    <!-- Loading indicator -->
                    <div class="absolute inset-0 flex items-center justify-center z-0">
                        <div class="text-center">
                            <span class="material-icons text-5xl text-gray-300 mb-3 block">description</span>
                            <p class="text-gray-400 text-sm">Memuat pratinjau dokumen...</p>
                        </div>
                    </div>

                    <!-- Iframe preview -->
                    <template x-if="modalData?.link && modalData.link !== '#'">
                        <iframe
                            :src="'https://docs.google.com/viewer?url=' + encodeURIComponent(modalData.link) + '&embedded=true'"
                            class="absolute inset-0 w-full h-full border-0 z-10"
                            allowfullscreen
                            loading="lazy"
                        ></iframe>
                    </template>

                    <!-- Placeholder jika link belum tersedia -->
                    <template x-if="!modalData?.link || modalData.link === '#'">
                        <div class="absolute inset-0 flex flex-col items-center justify-center z-10 bg-gray-50">
                            <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                                <span class="material-icons text-4xl text-gray-300">folder_open</span>
                            </div>
                            <p class="text-gray-500 font-semibold text-sm">Dokumen belum tersedia</p>
                            <p class="text-gray-400 text-xs mt-1">Link dokumen akan segera ditambahkan</p>
                        </div>
                    </template>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-between gap-3 p-4 border-t border-gray-100 shrink-0 bg-white rounded-b-3xl">
                    <p class="text-[11px] text-gray-400">Pratinjau dokumen melalui Google Docs Viewer</p>
                    <a :href="modalData?.link" target="_blank" rel="noopener noreferrer"
                       x-show="modalData?.link && modalData.link !== '#'"
                       class="inline-flex items-center gap-2 bg-[var(--color-primary)] hover:bg-[var(--color-primary-dark)] text-white px-5 py-2.5 rounded-xl font-bold text-xs tracking-widest uppercase transition-all shadow-md hover:shadow-lg">
                        Buka Dokumen <span class="material-icons text-sm">open_in_new</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
