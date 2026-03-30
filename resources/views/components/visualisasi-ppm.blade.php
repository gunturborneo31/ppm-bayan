@php
    // Get realisasi data grouped by pilar
    $pilarRealisasi = \App\Models\Pilar::with([
        'kegiatans.realisasis' => function ($q) {
            $q->select('kegiatan_id', 'realisasi_biaya')
              ->groupBy('kegiatan_id');
        },
        'kegiatans' => function ($q) {
            $q->select('id', 'nama', 'rencana_biaya', 'divisi_id')
              ->with('divisi:id,nama');
        }
    ])
    ->select('id', 'nama', 'deskripsi')
    ->orderBy('nama')
    ->get()
    ->map(function ($pilar) {
        $kegiatans = $pilar->kegiatans ?? [];
        $totalPagu = $kegiatans->sum('rencana_biaya') ?? 0;
        $totalRealisasi = $kegiatans->sum(function ($k) {
            return $k->realisasis->sum('realisasi_biaya') ?? 0;
        }) ?? 0;
        $sisaAnggaran = $totalPagu - $totalRealisasi;
        $serapan = $totalPagu > 0 ? ($totalRealisasi / $totalPagu * 100) : 0;
        
        return [
            'id' => $pilar->id,
            'nama' => $pilar->nama,
            'deskripsi' => $pilar->deskripsi,
            'totalPagu' => $totalPagu,
            'totalRealisasi' => $totalRealisasi,
            'sisaAnggaran' => $sisaAnggaran,
            'serapan' => $serapan,
            'jumlahKegiatan' => count($kegiatans)
        ];
    });
@endphp

<section class="my-16">
    <div class="mx-auto max-w-7xl px-6 lg:px-10">
        <!-- Header -->
        <div class="mb-12">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-8 bg-gradient-to-b from-[#d7561e] to-transparent rounded-full"></div>
                <h2 class="text-3xl md:text-4xl font-black text-[#12263f] tracking-tight">
                    Visualisasi Data PPM
                </h2>
            </div>
            <p class="text-slate-600 max-w-2xl">
                Ringkasan realisasi program dan kegiatan per pilar keberlanjutan dengan analisis pagu, realisasi, dan persentase serapan anggaran.
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-12">
            <div class="rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 p-6 shadow-sm">
                <p class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-2">Total Pilar</p>
                <p class="text-4xl font-black text-[#0f3c73]">{{ $pilarRealisasi->count() }}</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 p-6 shadow-sm">
                <p class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-2">Total Kegiatan</p>
                <p class="text-4xl font-black text-[#d7561e]">{{ $pilarRealisasi->sum('jumlahKegiatan') }}</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 p-6 shadow-sm">
                <p class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-2">Total Anggaran</p>
                <p class="text-2xl font-black text-emerald-700">Rp {{ number_format($pilarRealisasi->sum('totalPagu') / 1000000000, 1, ',', '.') }} M</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 p-6 shadow-sm">
                <p class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-2">Rata-rata Serapan</p>
                <p class="text-2xl font-black text-purple-700">{{ $pilarRealisasi->count() > 0 ? number_format($pilarRealisasi->avg('serapan'), 1, ',', '.') : '0' }}%</p>
            </div>
        </div>

        <!-- Pilar Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($pilarRealisasi as $pilar)
                @php
                    $iconMap = [
                        'Kesehatan' => 'favorite',
                        'Pendidikan' => 'school',
                        'Lingkungan' => 'eco',
                        'Pemberdayaan Masyarakat' => 'group',
                        'Pemberdayaan Ekonomi' => 'trending_up',
                        'Infrastruktur' => 'engineering',
                        'Keselamatan Kerja' => 'security',
                        'Energi Terbarukan' => 'flash_on',
                    ];
                    $icon = $iconMap[$pilar['nama']] ?? 'assessment';
                    $colors = [
                        'Kesehatan' => 'bg-red-100 text-red-600 border-red-200',
                        'Pendidikan' => 'bg-blue-100 text-blue-600 border-blue-200',
                        'Lingkungan' => 'bg-green-100 text-green-600 border-green-200',
                        'Pemberdayaan Masyarakat' => 'bg-amber-100 text-amber-600 border-amber-200',
                        'Pemberdayaan Ekonomi' => 'bg-purple-100 text-purple-600 border-purple-200',
                        'Infrastruktur' => 'bg-slate-100 text-slate-600 border-slate-200',
                        'Keselamatan Kerja' => 'bg-orange-100 text-orange-600 border-orange-200',
                        'Energi Terbarukan' => 'bg-cyan-100 text-cyan-600 border-cyan-200',
                    ];
                    $colorClass = $colors[$pilar['nama']] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                @endphp
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-lg transition-shadow overflow-hidden group">
                    <!-- Card Header -->
                    <div class="p-6 {{ $colorClass }} relative overflow-hidden">
                        <div class="absolute -right-8 -top-8 w-32 h-32 opacity-10" style="background: currentColor; border-radius: 50%;"></div>
                        <div class="flex items-start justify-between relative z-10">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-10 h-10 rounded-lg {{ $colorClass }} flex items-center justify-center">
                                        <span class="material-icons text-lg">{{ $icon }}</span>
                                    </div>
                                    <h3 class="font-bold text-lg text-gray-800">{{ $pilar['nama'] }}</h3>
                                </div>
                                <p class="text-xs text-gray-600 line-clamp-2">{{ $pilar['deskripsi'] ?? 'Pilar keberlanjutan' }}</p>
                            </div>
                            <div class="text-right ml-4">
                                <p class="text-2xl font-black text-current">{{ $pilar['jumlahKegiatan'] }}</p>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-current opacity-80">Kegiatan</p>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6 space-y-4">
                        <!-- Pagu -->
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Anggaran (Pagu)</p>
                                <p class="text-lg font-bold text-slate-900">Rp {{ number_format($pilar['totalPagu'] / 1000000000, 1, ',', '.') }} M</p>
                            </div>
                            <span class="material-icons text-slate-300">account_balance_wallet</span>
                        </div>

                        <!-- Realisasi -->
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Realisasi</p>
                                <p class="text-lg font-bold text-slate-900">Rp {{ number_format($pilar['totalRealisasi'] / 1000000000, 1, ',', '.') }} M</p>
                            </div>
                            <span class="material-icons text-slate-300">check_circle</span>
                        </div>

                        <!-- Sisa Anggaran -->
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Sisa Anggaran</p>
                                <p class="text-lg font-bold {{ $pilar['sisaAnggaran'] < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    Rp {{ number_format($pilar['sisaAnggaran'] / 1000000000, 1, ',', '.') }} M
                                </p>
                            </div>
                            <span class="material-icons {{ $pilar['sisaAnggaran'] < 0 ? 'text-red-300' : 'text-emerald-300' }}">{{ $pilar['sisaAnggaran'] < 0 ? 'warning' : 'done_all' }}</span>
                        </div>

                        <!-- Progress Bar - Serapan -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Serapan Anggaran</p>
                                <p class="text-sm font-bold text-slate-700">{{ number_format($pilar['serapan'], 1, ',', '.') }}%</p>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 {{ 
                                    $pilar['serapan'] >= 80 ? 'bg-emerald-500' : 
                                    ($pilar['serapan'] >= 50 ? 'bg-amber-500' : 'bg-orange-500')
                                }}" style="width: {{ min($pilar['serapan'], 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Legend / Info -->
        <div class="mt-12 rounded-2xl bg-gradient-to-r from-slate-50 to-blue-50 border border-slate-200 p-6 md:p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                        <span class="material-icons text-emerald-600">trending_up</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900 mb-1">Serapan Tinggi (≥80%)</p>
                        <p class="text-xs text-slate-600">Indikasi realisasi berjalan optimal</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <span class="material-icons text-amber-600">schedule</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900 mb-1">Serapan Sedang (50-80%)</p>
                        <p class="text-xs text-slate-600">Realisasi dalam tahap pelaksanaan</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
                        <span class="material-icons text-orange-600">hourglass_empty</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900 mb-1">Serapan Rendah (<50%)</p>
                        <p class="text-xs text-slate-600">Memerlukan akselerasi realisasi</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
