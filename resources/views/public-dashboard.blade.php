<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Publik PPM Bayan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite('resources/js/app.js')
</head>
<body class="min-h-screen bg-slate-50 text-slate-800" style="font-family:'Plus Jakarta Sans',sans-serif;">
    @php
        $selectedModeLabel = collect($options['period_modes'])->firstWhere('value', $filters['period_mode'])['label'] ?? ucfirst($filters['period_mode']);
        $selectedPeriodLabel = 'Semua';

        if (!empty($filters['period_value'])) {
            $selectedPeriodLabel = collect($options['period_options'][$filters['period_mode']] ?? [])
                ->firstWhere('value', (int) $filters['period_value'])['label'] ?? $filters['period_value'];
        }
    @endphp

    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                <img src="{{ asset('logo-bayan.png') }}" alt="Logo Bayan" class="h-9 w-auto object-contain">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-slate-500">PPM</p>
                    <p class="text-sm font-semibold text-slate-800">Dashboard Publik Bayan</p>
                </div>
            </a>

            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Landing Page</a>
                <a href="{{ route('login') }}" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600">Login</a>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <section class="rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-orange-700 p-6 text-white shadow-xl">
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Dashboard Realisasi Anggaran, Pilar, Program dan Kegiatan</h1>
            <p class="mt-2 text-sm text-white/85 sm:text-base">Halaman ini bersifat publik, dapat diakses tanpa login, dan dirancang untuk pemantauan cepat serta transparansi data PPM.</p>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-600">Ringkasan Anggaran Pilar, Program, dan Sisa</h2>
                <div class="mt-4 h-72">
                    <canvas id="overviewChart"></canvas>
                </div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-600">Anggaran Pilar dan Yang Telah Direncanakan</h2>
                <div class="mt-4 h-72">
                    <canvas id="allocationChart"></canvas>
                </div>
            </article>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wide text-slate-600">Filter Global Dashboard</h2>
                    <p class="mt-1 text-sm text-slate-500">Semua grafik dan tabel di bawah mengikuti filter yang dipilih.</p>
                    <p class="mt-2 text-sm font-medium text-slate-700">Filter aktif: Tahun {{ $filters['tahun'] }}, Mode {{ $selectedModeLabel }}, Periode {{ $selectedPeriodLabel }}</p>
                </div>
                <div class="rounded-full bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">
                    Tahun {{ $filters['tahun'] }} · {{ $selectedModeLabel }} · {{ $selectedPeriodLabel }}
                </div>
            </div>

            <form method="GET" action="{{ route('public.dashboard') }}" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Tahun</label>
                    <select name="tahun" class="w-full rounded-lg border-slate-300 text-sm focus:border-orange-400 focus:ring-orange-300">
                        @foreach($options['years'] as $year)
                            <option value="{{ $year }}" @selected((int)$filters['tahun'] === (int)$year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Mode</label>
                    <select name="period_mode" id="period_mode" class="w-full rounded-lg border-slate-300 text-sm focus:border-orange-400 focus:ring-orange-300">
                        @foreach($options['period_modes'] as $mode)
                            <option value="{{ $mode['value'] }}" @selected($filters['period_mode'] === $mode['value'])>{{ $mode['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Periode</label>
                    <select name="period_value" id="period_value" class="w-full rounded-lg border-slate-300 text-sm focus:border-orange-400 focus:ring-orange-300">
                        <option value="">Semua</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600">Terapkan</button>
                    <a href="{{ route('public.dashboard') }}" class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">Reset</a>
                </div>
            </form>

            <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Legend Warna Pilar</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($pilarSummary as $pilar)
                        <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-xs font-medium text-slate-700 ring-1 ring-slate-200">
                            <span class="inline-block h-2.5 w-2.5 rounded-full" style="background-color: {{ $pilar['color'] }};"></span>
                            {{ $pilar['nama'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-600">Grafik Anggaran per Pilar dan Realisasi</h2>
                <div class="mt-4 h-72">
                    <canvas id="pilarChart"></canvas>
                </div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-600">Grafik Realisasi Berdasarkan {{ ucfirst($filters['period_mode']) }}</h2>
                <div class="mt-4 h-72">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </article>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-600">Komposisi Anggaran</h2>
                <div class="mt-4 h-72">
                    <canvas id="budgetPieChart"></canvas>
                </div>
                <div class="mt-4 rounded-xl bg-slate-50 p-3 text-xs text-slate-600">
                    <p class="font-semibold text-slate-700">Informasi Grafik Pie</p>
                    <p class="mt-1">Menunjukkan perbandingan total realisasi terhadap sisa rencana biaya pada periode yang dipilih.</p>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-600">Capaian Output</h2>
                <div class="mt-4 h-72">
                    <canvas id="outputDoughnutChart"></canvas>
                </div>
                <div class="mt-4 rounded-xl bg-slate-50 p-3 text-xs text-slate-600">
                    <p class="font-semibold text-slate-700">Informasi Grafik Rounded</p>
                    <p class="mt-1">Menampilkan progress output: bagian terisi adalah output terealisasi dan bagian lain adalah sisa target output.</p>
                </div>
            </article>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-600">Distribusi Anggaran per Bidang</h2>
                <div class="mt-4 h-72">
                    <canvas id="distributionChart"></canvas>
                </div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-600">Realisasi Anggaran PPM</h2>
                <div class="mt-4 h-72">
                    <canvas id="realisasiBarChart"></canvas>
                </div>
            </article>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-600">Tabel Monitoring Program</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[760px] text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Pilar</th>
                            <th class="px-3 py-2">Program</th>
                            <th class="px-3 py-2 text-right whitespace-nowrap">Jumlah Kegiatan</th>
                            <th class="px-3 py-2 text-right whitespace-nowrap">Rencana Biaya</th>
                            <th class="px-3 py-2 text-right whitespace-nowrap">Realisasi Biaya</th>
                            <th class="px-3 py-2 text-right whitespace-nowrap">Capaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($programTable as $row)
                            <tr>
                                <td class="px-3 py-2 text-slate-600">
                                    <span class="inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-xs font-semibold"
                                          style="background-color: {{ $row['pilar_color'] }}1A; color: {{ $row['pilar_color'] }};">
                                        <span class="inline-block h-2 w-2 rounded-full" style="background-color: {{ $row['pilar_color'] }};"></span>
                                        {{ $row['pilar'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 font-medium text-slate-800">{{ $row['program'] }}</td>
                                <td class="px-3 py-2 text-right text-slate-600 whitespace-nowrap">{{ $row['jumlah_kegiatan'] }}</td>
                                <td class="px-3 py-2 text-right text-slate-700 whitespace-nowrap">Rp {{ number_format($row['rencana_biaya'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right text-slate-700 whitespace-nowrap">Rp {{ number_format($row['realisasi_biaya'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right font-semibold whitespace-nowrap {{ $row['capaian_persen'] >= 100 ? 'text-emerald-600' : 'text-orange-600' }}">
                                    {{ number_format($row['capaian_persen'], 2, ',', '.') }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-slate-400">Belum ada data program.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartCenterLabel = {
            id: 'chartCenterLabel',
            afterDraw(chart, args, pluginOptions) {
                if (!pluginOptions || pluginOptions.value === undefined || pluginOptions.value === null) {
                    return;
                }

                const meta = chart.getDatasetMeta(0);
                if (!meta || !meta.data || !meta.data.length) {
                    return;
                }

                const x = meta.data[0].x;
                const y = meta.data[0].y;
                const ctx = chart.ctx;
                const value = Number(pluginOptions?.value ?? 0);
                const label = pluginOptions?.label || 'Capaian';
                const valueColor = pluginOptions?.valueColor || '#0f172a';
                const labelColor = pluginOptions?.labelColor || '#64748b';

                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                ctx.fillStyle = valueColor;
                ctx.font = '700 22px Plus Jakarta Sans';
                ctx.fillText(`${value.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}%`, x, y - 8);

                ctx.fillStyle = labelColor;
                ctx.font = '600 11px Plus Jakarta Sans';
                ctx.fillText(label, x, y + 16);
                ctx.restore();
            },
        };

        Chart.register(chartCenterLabel);

        const periodOptions = @json($options['period_options']);
        const currentMode = @json($filters['period_mode']);
        const currentValue = @json($filters['period_value']);

        const modeEl = document.getElementById('period_mode');
        const valueEl = document.getElementById('period_value');

        function rebuildPeriodOptions(mode, selectedValue) {
            valueEl.innerHTML = '<option value="">Semua</option>';
            const options = periodOptions[mode] || [];
            options.forEach((opt) => {
                const option = document.createElement('option');
                option.value = String(opt.value);
                option.textContent = opt.label;
                if (selectedValue !== null && selectedValue !== '' && Number(selectedValue) === Number(opt.value)) {
                    option.selected = true;
                }
                valueEl.appendChild(option);
            });
        }

        rebuildPeriodOptions(currentMode, currentValue);
        modeEl.addEventListener('change', (event) => rebuildPeriodOptions(event.target.value, null));

        const overviewCtx = document.getElementById('overviewChart');
        const allocationCtx = document.getElementById('allocationChart');
        const pilarCtx = document.getElementById('pilarChart');
        const monthlyCtx = document.getElementById('monthlyChart');
        const budgetPieCtx = document.getElementById('budgetPieChart');
        const outputDoughnutCtx = document.getElementById('outputDoughnutChart');
        const distributionCtx = document.getElementById('distributionChart');
        const realisasiBarCtx = document.getElementById('realisasiBarChart');
        const pilarColors = @json($charts['pilar_colors']);

        const currencyTick = (value) => {
            if (value >= 1000000000) return (value / 1000000000).toLocaleString('id-ID') + ' M';
            if (value >= 1000000) return (value / 1000000).toLocaleString('id-ID') + ' Jt';
            return Number(value).toLocaleString('id-ID');
        };

        new Chart(overviewCtx, {
            type: 'bar',
            data: {
                labels: ['Ringkasan'],
                datasets: [
                    {
                        label: 'Total Anggaran Pilar',
                        data: [@json($summary['total_anggaran_pilar'])],
                        backgroundColor: '#0f172a',
                        borderRadius: 10,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Sisa Anggaran Pilar',
                        data: [@json($summary['total_sisa_anggaran_pilar'])],
                        backgroundColor: '#22c55e',
                        borderRadius: 10,
                        yAxisID: 'y',
                    },
                    {
                        type: 'line',
                        label: 'Program Dianggarkan',
                        data: [@json($summary['program_dianggarkan_count'])],
                        borderColor: '#f97316',
                        backgroundColor: '#f97316',
                        pointRadius: 5,
                        pointHoverRadius: 6,
                        tension: 0.3,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.label === 'Program Dianggarkan') {
                                    return Number(context.raw || 0).toLocaleString('id-ID') + ' program';
                                }
                                return 'Rp ' + Number(context.raw || 0).toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: { callback: currencyTick }
                    },
                    y1: {
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: {
                            callback: function(value) {
                                return Number(value).toLocaleString('id-ID');
                            }
                        }
                    }
                }
            },
        });

        new Chart(allocationCtx, {
            type: 'bar',
            data: {
                labels: @json($charts['allocation_labels']),
                datasets: [{
                    label: 'Nilai Anggaran',
                    data: @json($charts['allocation_values']),
                    backgroundColor: ['#334155', '#f59e0b', '#10b981'],
                    borderRadius: 10,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        ticks: { callback: currencyTick }
                    }
                }
            },
        });

        new Chart(pilarCtx, {
            type: 'bar',
            data: {
                labels: @json($charts['pilar_labels']),
                datasets: [
                    {
                        label: 'Anggaran Pilar',
                        data: @json($charts['pilar_anggaran']),
                        backgroundColor: pilarColors.map((color) => color + '99'),
                        borderRadius: 8,
                    },
                    {
                        label: 'Realisasi',
                        data: @json($charts['pilar_realisasi']),
                        backgroundColor: pilarColors,
                        borderRadius: 8,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                },
                scales: {
                    y: {
                        ticks: { callback: currencyTick }
                    }
                }
            },
        });

        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: @json($charts['monthly_labels']),
                datasets: [
                    {
                        label: 'Realisasi Biaya',
                        data: @json($charts['monthly_realisasi']),
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.18)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                },
                scales: {
                    y: {
                        ticks: { callback: currencyTick }
                    }
                }
            },
        });

        new Chart(budgetPieCtx, {
            type: 'pie',
            data: {
                labels: @json($charts['pie_labels']),
                datasets: [
                    {
                        data: @json($charts['pie_values']),
                        backgroundColor: ['#f97316', '#cbd5e1'],
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    chartCenterLabel: {
                        value: @json($summary['serapan_persen']),
                        label: 'Serapan Anggaran',
                        valueColor: '#c2410c',
                        labelColor: '#9a3412',
                    },
                },
            },
        });

        new Chart(outputDoughnutCtx, {
            type: 'doughnut',
            data: {
                labels: @json($charts['doughnut_labels']),
                datasets: [
                    {
                        data: @json($charts['doughnut_values']),
                        backgroundColor: ['#0ea5e9', '#e2e8f0'],
                        borderWidth: 0,
                        borderRadius: 10,
                        spacing: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '64%',
                plugins: {
                    legend: { position: 'bottom' },
                    chartCenterLabel: {
                        value: @json($summary['target_output_persen']),
                        label: 'Capaian Output',
                        valueColor: '#0369a1',
                        labelColor: '#0f766e',
                    },
                },
            },
        });

        new Chart(distributionCtx, {
            type: 'pie',
            data: {
                labels: @json($charts['distribution_labels']),
                datasets: [{
                    data: @json($charts['distribution_values']),
                    backgroundColor: pilarColors,
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
            },
        });

        new Chart(realisasiBarCtx, {
            type: 'bar',
            data: {
                labels: @json($charts['realisasi_bar_labels']),
                datasets: [{
                    label: 'Realisasi Anggaran',
                    data: @json($charts['realisasi_bar_values']),
                    backgroundColor: pilarColors,
                    borderRadius: 8,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        ticks: { callback: currencyTick }
                    }
                }
            },
        });

    </script>
</body>
</html>