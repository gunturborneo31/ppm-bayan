<section id="bidang" class="py-16 md:py-24 bg-gray-50 relative border-t border-gray-100" :class="modalOpen ? 'z-[100]' : ''" x-data="{ modalOpen: false, modalData: null }">
    @php
        $iconMap = [
            'pendidikan' => 'school',
            'kesehatan' => 'medical_services',
            'pendapatan riil atau pekerjaan' => 'trending_up',
            'kemandirian ekonomi' => 'payments',
            'sosial dan budaya' => 'groups',
            'lingkungan' => 'eco',
            'kelembagaan komunitas masyarakat' => 'hub',
            'infrastruktur' => 'construction',
        ];

        $pilars = App\Models\Pilar::query()
            ->with([
                'kegiatans' => function ($query) {
                    $query->with([
                        'program:id,nama,rencana_biaya',
                        'divisi:id,nama',
                    ])->orderBy('program_id')->orderBy('nama');
                },
            ])
            ->orderBy('nama')
            ->get();

        if ($pilars->isEmpty()) {
            $pilars = collect([
                (object) ['id' => 1, 'nama' => 'Pendidikan', 'deskripsi' => 'Data pilar belum tersedia.', 'kegiatans' => collect([])],
                (object) ['id' => 2, 'nama' => 'Kesehatan', 'deskripsi' => 'Data pilar belum tersedia.', 'kegiatans' => collect([])],
                (object) ['id' => 3, 'nama' => 'Pendapatan Riil atau Pekerjaan', 'deskripsi' => 'Data pilar belum tersedia.', 'kegiatans' => collect([])],
                (object) ['id' => 4, 'nama' => 'Kemandirian Ekonomi', 'deskripsi' => 'Data pilar belum tersedia.', 'kegiatans' => collect([])],
                (object) ['id' => 5, 'nama' => 'Sosial dan Budaya', 'deskripsi' => 'Data pilar belum tersedia.', 'kegiatans' => collect([])],
                (object) ['id' => 6, 'nama' => 'Lingkungan', 'deskripsi' => 'Data pilar belum tersedia.', 'kegiatans' => collect([])],
                (object) ['id' => 7, 'nama' => 'Kelembagaan Komunitas Masyarakat', 'deskripsi' => 'Data pilar belum tersedia.', 'kegiatans' => collect([])],
                (object) ['id' => 8, 'nama' => 'Infrastruktur', 'deskripsi' => 'Data pilar belum tersedia.', 'kegiatans' => collect([])],
            ]);
        }
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16 relative" x-data="{ shown: false }" x-intersect.half="shown = true">
            <span class="text-[var(--color-primary)] font-bold uppercase tracking-[0.3em] text-[10px] mb-4 block">{{ $pilars->count() }} Pilar PPM Tambang</span>
            <h2 class="text-3xl md:text-5xl font-extrabold text-gray-900 mb-6 tracking-tight">Bidang Program <span class="text-[var(--color-primary)]">PPM</span></h2>
            <p class="text-gray-500 max-w-2xl mx-auto text-base md:text-lg">Komitmen kami dalam pilar Pengembangan dan Pemberdayaan Masyarakat (PPM) berbasis data untuk keberlanjutan lokal.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
            @foreach($pilars as $index => $pilar)
            @php
                $kegiatans = collect($pilar->kegiatans ?? []);
                $programGroups = $kegiatans
                    ->filter(fn($kegiatan) => $kegiatan->program)
                    ->groupBy('program_id');

                $programList = $programGroups->map(function ($group) {
                    $program = $group->first()->program;
                    $programPagu = (float) ($program->rencana_biaya ?? 0);
                    $totalPaguKegiatan = (float) $group->sum('rencana_biaya');

                    return [
                        'id' => $program->id,
                        'nama' => $program->nama,
                        'pagu' => 'Rp ' . number_format($programPagu, 0, ',', '.'),
                        'totalPaguKegiatan' => 'Rp ' . number_format($totalPaguKegiatan, 0, ',', '.'),
                        'jumlahKegiatan' => $group->count(),
                        'kegiatans' => $group->map(function ($kegiatan) {
                            return [
                                'nama' => $kegiatan->nama,
                                'divisi' => $kegiatan->divisi?->nama ?? '-',
                                'pagu' => 'Rp ' . number_format((float) ($kegiatan->rencana_biaya ?? 0), 0, ',', '.'),
                            ];
                        })->values()->all(),
                    ];
                })->values();

                $totalPaguProgram = (float) $programGroups
                    ->map(fn ($group) => (float) ($group->first()->program->rencana_biaya ?? 0))
                    ->sum();

                $iconKey = strtolower(trim((string) $pilar->nama));
                $itemData = [
                    'nama' => $pilar->nama,
                    'deskripsi' => $pilar->deskripsi ?? 'Detail belum tersedia',
                    'icon' => $iconMap[$iconKey] ?? 'category',
                    'totalProgram' => $programGroups->count(),
                    'totalKegiatan' => $kegiatans->count(),
                    'totalPagu' => 'Rp ' . number_format($totalPaguProgram, 0, ',', '.'),
                    'programs' => $programList->toArray()
                ];
            @endphp
            <div @click='modalData = @json($itemData); modalOpen = true;'
                 class="group relative bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_60px_rgba(232,97,10,0.12)] transition-all duration-500 hover:-translate-y-3 cursor-pointer overflow-hidden flex flex-col h-full"
                 x-data="{ shown: false }"
                 x-intersect.half="shown = true"
                 :class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'"
                 style="transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); transition-delay: {{ $index * 50 }}ms;">

                <!-- Subtle Icon Background -->
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-[var(--color-primary)]/5 rounded-full blur-3xl group-hover:bg-[var(--color-primary)]/10 transition-colors"></div>

                <div class="relative z-10 flex flex-col h-full">
                    <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center text-[var(--color-primary)] mb-6 transition-all duration-500 group-hover:bg-[var(--color-primary)] group-hover:text-white transform group-hover:scale-110 shadow-sm">
                        <span class="material-icons text-3xl">{{ $iconMap[strtolower(trim((string) $pilar->nama))] ?? 'category' }}</span>
                    </div>

                    <h3 class="text-xl font-extrabold text-gray-900 mb-6 tracking-tight group-hover:text-[var(--color-primary)] transition-colors leading-tight">{{ $pilar->nama }}</h3>
                    
                    <div class="mt-auto flex items-center gap-2 text-[10px] font-bold text-gray-400 group-hover:text-[var(--color-primary)] uppercase tracking-widest transition-colors">
                        Klik Untuk Detail <span class="material-icons text-sm transition-transform group-hover:translate-x-1">east</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    <!-- END Grid -->

    <!-- Modal Popup (Teleported to body to fix navbar overlap) -->
    <template x-teleport="body">
        <div x-show="modalOpen" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;">
            <!-- Background overlay that closes modal -->
            <div @click="modalOpen = false" class="absolute inset-0"></div>

            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl flex flex-col max-h-[90vh] z-10 overflow-hidden transform transition-all"
                 @click.stop
                 x-show="modalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95">
                
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-4 pr-4">
                        <div class="w-12 h-12 shrink-0 rounded-xl bg-[var(--color-primary)]/10 text-[var(--color-primary)] flex items-center justify-center">
                            <span class="material-icons text-2xl" x-text="modalData?.icon"></span>
                        </div>
                        <div>
                            <span class="text-[var(--color-primary)] font-bold uppercase tracking-widest text-[10px] mb-1 block">Detail Bidang</span>
                            <h3 class="text-xl font-extrabold text-gray-900 leading-tight" x-text="modalData?.nama"></h3>
                        </div>
                    </div>
                    <button @click="modalOpen = false" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-xl text-sm w-10 h-10 inline-flex justify-center items-center transition-colors">
                        <span class="material-icons">close</span>
                    </button>
                </div>
                
                <!-- Body -->
                <div class="p-6 overflow-y-auto w-full custom-scrollbar">
                    <div class="mb-6">
                        <p class="text-gray-600 text-base leading-relaxed" x-text="modalData?.deskripsi"></p>
                    </div>

                    <!-- Summary Boxes -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                            <div class="bg-blue-100/50 p-3 rounded-xl text-blue-600">
                                <span class="material-icons text-2xl">diversity_3</span>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Total Program</div>
                                <div class="text-2xl font-black text-gray-900 leading-none"><span x-text="modalData?.totalProgram"></span> <span class="text-sm font-medium text-gray-500">Program</span></div>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                            <div class="bg-purple-100/50 p-3 rounded-xl text-purple-600">
                                <span class="material-icons text-2xl">task_alt</span>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Total Kegiatan</div>
                                <div class="text-2xl font-black text-gray-900 leading-none"><span x-text="modalData?.totalKegiatan"></span> <span class="text-sm font-medium text-gray-500">Kegiatan</span></div>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 flex items-center gap-4 hover:shadow-md transition-shadow">
                            <div class="bg-green-100/50 p-3 rounded-xl text-green-600">
                                <span class="material-icons text-2xl">account_balance_wallet</span>
                            </div>
                            <div>
                                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Total Pagu Program</div>
                                <div class="text-xl font-black text-green-600 leading-none" x-text="modalData?.totalPagu"></div>
                            </div>
                        </div>
                    </div>

                    <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="material-icons text-[18px] text-[var(--color-primary)]">list_alt</span> Daftar Program dan Kegiatan
                    </h4>
                    
                    <template x-if="modalData?.programs && modalData.programs.length > 0">
                        <div class="space-y-4">
                            <template x-for="program in modalData.programs">
                                <div class="border border-gray-100 p-5 rounded-2xl hover:border-gray-200 hover:shadow-md transition-all bg-white group/card">
                                    <div class="flex justify-between items-start mb-3 gap-4">
                                        <h5 class="font-bold text-gray-900 text-lg leading-tight group-hover/card:text-[var(--color-primary)] transition-colors" x-text="program.nama"></h5>
                                        <span class="text-[10px] bg-blue-50 px-2.5 py-1 rounded-md text-blue-600 font-bold whitespace-nowrap border border-blue-100 shadow-sm" x-text="program.jumlahKegiatan + ' kegiatan'"></span>
                                    </div>
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <span class="inline-flex items-center text-xs font-bold text-green-700 bg-green-50 px-3 py-1.5 rounded-lg border border-green-100">
                                            <span class="material-icons text-[14px] mr-1.5 text-green-600">payments</span> Pagu Program: <span class="ml-1" x-text="program.pagu"></span>
                                        </span>
                                        <span class="inline-flex items-center text-xs font-medium text-blue-700 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100">
                                            <span class="material-icons text-[14px] mr-1.5 text-blue-600">summarize</span> Total Pagu Kegiatan: <span class="ml-1 font-bold" x-text="program.totalPaguKegiatan"></span>
                                        </span>
                                    </div>

                                    <div class="rounded-xl border border-gray-100 overflow-hidden">
                                        <div class="bg-gray-50 px-4 py-2 text-[11px] font-bold text-gray-500 uppercase tracking-wide">Daftar Kegiatan</div>
                                        <div class="divide-y divide-gray-100">
                                            <template x-for="kegiatan in program.kegiatans" :key="kegiatan.nama + kegiatan.divisi">
                                                <div class="px-4 py-3 flex items-start justify-between gap-4">
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-800" x-text="kegiatan.nama"></p>
                                                        <p class="text-xs text-gray-500 mt-0.5">Divisi: <span x-text="kegiatan.divisi"></span></p>
                                                    </div>
                                                    <span class="text-xs font-bold text-green-700" x-text="kegiatan.pagu"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    
                    <template x-if="!modalData?.programs || modalData.programs.length === 0">
                        <div class="text-center py-10 px-4 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="material-icons text-3xl text-gray-400">inbox</span>
                            </div>
                            <h5 class="text-gray-900 font-bold mb-1">Belum Ada Program</h5>
                            <p class="text-sm text-gray-500 max-w-xs mx-auto">Saat ini belum ada rincian program yang ditambahkan untuk bidang ini.</p>
                        </div>
                    </template>
                </div>
                
                <!-- Footer -->
                <div class="p-5 border-t border-gray-100 bg-white rounded-b-2xl">
                    <button @click="modalOpen = false" class="w-full sm:w-auto sm:float-right inline-flex justify-center items-center text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 font-bold rounded-xl text-sm px-6 py-3 transition-colors">
                        Tutup <span class="material-icons text-lg ml-2">close</span>
                    </button>
                    <div class="clear-both"></div>
                </div>
            </div>
        </div>
    </template>
    </div>
</section>
