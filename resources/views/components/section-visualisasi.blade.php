@php
    $pilarColorMap = \App\Models\Pilar::colorMap();

    $chartRows = \Illuminate\Support\Facades\DB::table('pilars')
        ->leftJoin('programs', 'programs.pilar_id', '=', 'pilars.id')
        ->leftJoin('kegiatans', 'kegiatans.program_id', '=', 'programs.id')
        ->leftJoin('realisasis', 'realisasis.kegiatan_id', '=', 'kegiatans.id')
        ->select(
            'pilars.id',
            'pilars.nama',
            'pilars.rencana_biaya',
            \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(realisasis.realisasi_biaya), 0) as total_realisasi')
        )
        ->groupBy('pilars.id', 'pilars.nama', 'pilars.rencana_biaya')
        ->orderBy('pilars.nama')
        ->get();

    $pilarLabels = $chartRows->pluck('nama')->values();
    $pilarBudgets = $chartRows->pluck('rencana_biaya')->map(fn($v) => (float) $v)->values();
    $pilarRealisasi = $chartRows->pluck('total_realisasi')->map(fn($v) => (float) $v)->values();
    $pilarColors = $chartRows->map(fn($row) => $pilarColorMap[$row->nama] ?? '#64748b')->values();
@endphp

<section id="visualisasi" class="py-16 md:py-24 bg-white relative overflow-hidden max-w-7xl mx-auto">
    <div class="px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16" x-data="{ shown: false }" x-intersect.half="shown = true">
            <span class="text-[var(--color-primary)] font-bold uppercase tracking-[0.3em] text-[10px] mb-4 block">Data Insights</span>
            <h2 class="text-3xl md:text-5xl font-extrabold text-gray-900 mb-6 tracking-tight">Visualisasi Data PPM</h2>
            <p class="text-gray-500 max-w-2xl mx-auto text-base md:text-lg">Analisis grafis kontribusi pengembangan dan pemberdayaan masyarakat berdasarkan kategori program.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
            <!-- Chart Doughnut -->
            <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center hover:shadow-lg transition duration-300">
                <h3 class="text-xl font-bold text-primary-dark mb-6 self-start flex items-center"><span class="material-icons mr-2 text-accent">pie_chart</span> Distribusi Anggaran per Bidang</h3>
                <div class="w-full max-w-md mx-auto aspect-square relative">
                    <canvas id="chartPie"></canvas>
                </div>
            </div>

            <!-- Chart Bar Horizontal -->
            <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col hover:shadow-lg transition duration-300">
                <h3 class="text-xl font-bold text-primary-dark mb-6 flex items-center"><span class="material-icons mr-2 text-accent">bar_chart</span> Realisasi Anggaran PPM</h3>
                <div class="w-full h-full min-h-[350px] relative">
                    <canvas id="chartBar"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Script for chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function bootChartsWhenReady() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initCharts, { once: true });
                return;
            }

            initCharts();
        }

        // Inisialisasi saat halaman static (tanpa Livewire) selesai dirender.
        bootChartsWhenReady();
        document.addEventListener('alpine:initialized', initCharts);

        let pieChartInstance = null;
        let barChartInstance = null;

        function initCharts() {
            const pilarData = {
                labels: @json($pilarLabels),
                anggaran: @json($pilarBudgets),
                realisasi: @json($pilarRealisasi),
                colors: @json($pilarColors),
            };

            const colors = pilarData.colors;

            const ctxPie = document.getElementById('chartPie')?.getContext('2d');
            const ctxBar = document.getElementById('chartBar')?.getContext('2d');

            if (ctxPie) {
                if(pieChartInstance) pieChartInstance.destroy();
                pieChartInstance = new Chart(ctxPie, {
                    type: 'doughnut',
                    data: {
                        labels: pilarData.labels,
                        datasets: [{
                            data: pilarData.anggaran,
                            backgroundColor: colors,
                            borderWidth: 0,
                            hoverOffset: 15
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { 
                                        padding: 20,
                                    usePointStyle: true,
                                            font: { family: "'Plus Jakarta Sans', sans-serif", weight: 'semibold', size: 11 }
                                }
                            },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return context.label + ': Rp ' + Number(context.raw || 0).toLocaleString('id-ID');
                                            }
                                        }
                                    }
                        },
                        cutout: '70%',
                        animation: { animateRotate: true, animateScale: true }
                    }
                });
            }

            if (ctxBar) {
                if(barChartInstance) barChartInstance.destroy();
                barChartInstance = new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: pilarData.labels,
                        datasets: [{
                            label: 'Realisasi Anggaran (Rp)',
                            data: pilarData.realisasi,
                            backgroundColor: colors,
                            borderRadius: 12,
                            maxBarThickness: 35
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' Rp ' + Number(context.raw || 0).toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { borderDash: [5, 5], drawBorder: false },
                                ticks: { 
                                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 10 },
                                    callback: function(value) {
                                        if (value >= 1000000000) return (value / 1000000000) + ' M';
                                        if (value >= 1000000) return (value / 1000000) + ' jt';
                                        return value;
                                    }
                                }
                            },
                            y: { 
                                grid: { display: false },
                                ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", weight: 'bold' }, color: '#4b5563' }
                            }
                        }
                    }
                });
            }
        }
    </script>
</section>
