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
            // Data Dummy PPM Bayan Group
            const dummyData = {
                labels: [
                    'Pendidikan', 
                    'Kesehatan', 
                    'Infrastruktur', 
                    'Kemandirian Ekonomi', 
                    'Sosial Budaya', 
                    'Lingkungan', 
                    'Kelembagaan', 
                    'Pendapatan Riil'
                ],
                data: [4500000000, 3800000000, 5200000000, 2900000000, 2100000000, 1500000000, 1200000000, 3100000000]
            };
            
            // Palette warna: Hijau (Utama), Merah (Urgent/Penting), Biru (Fasilitas), Sisanya variasi
            const colors = [
                '#22c55e', // Green (Pendidikan)
                '#ef4444', // Red (Kesehatan)
                '#3b82f6', // Blue (Infrastruktur)
                '#f59e0b', // Amber/Orange
                '#8b5cf6', // Violet
                '#06b6d4', // Cyan
                '#ec4899', // Pink
                '#10b981'  // Emerald
            ];

            const ctxPie = document.getElementById('chartPie')?.getContext('2d');
            const ctxBar = document.getElementById('chartBar')?.getContext('2d');

            if (ctxPie) {
                if(pieChartInstance) pieChartInstance.destroy();
                pieChartInstance = new Chart(ctxPie, {
                    type: 'doughnut',
                    data: {
                        labels: dummyData.labels,
                        datasets: [{
                            data: dummyData.data,
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
                        labels: dummyData.labels,
                        datasets: [{
                            label: 'Realisasi Anggaran (Rp)',
                            data: dummyData.data,
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
                                        return ' Rp ' + context.raw.toLocaleString('id-ID');
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
