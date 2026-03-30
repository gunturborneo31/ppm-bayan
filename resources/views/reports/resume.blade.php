<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 22px 22px 48px 22px; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1f2937; }
        h1, h2, h3 { margin: 0; }
        .page-header { margin-bottom: 18px; border-bottom: 2px solid #0f3c73; padding-bottom: 12px; }
        .page-header table { margin-bottom: 0; }
        .brand-row { width: 100%; }
        .brand-icon-cell { width: 58px; }
        .brand-icon { width: 42px; height: 42px; object-fit: contain; }
        .brand-text { width: 150px; height: auto; object-fit: contain; margin-bottom: 6px; }
        .brand { font-size: 22px; font-weight: bold; color: #0f3c73; }
        .subtitle { font-size: 12px; color: #4b5563; margin-top: 4px; }
        .meta-box { text-align: right; font-size: 11px; color: #4b5563; }
        .section-title { margin: 18px 0 8px; font-size: 13px; font-weight: bold; color: #0f3c73; }
        .filter-table td { border: 1px solid #d1d5db; padding: 6px 8px; }
        .filter-table .label { width: 90px; background: #eff6ff; font-weight: bold; }
        .cards { width: 100%; margin-bottom: 16px; }
        .cards td { width: 25%; vertical-align: top; border: 1px solid #d1d5db; padding: 10px; }
        .cards .label { font-size: 10px; text-transform: uppercase; color: #6b7280; }
        .cards .value { font-size: 16px; font-weight: bold; color: #111827; margin-top: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; }
        th { background: #0f3c73; color: #fff; }
        .summary-head th { background: #1f4f87; }
        tr:nth-child(even) { background: #f9fafb; }
        .text-right { text-align: right; }
        .muted { color: #6b7280; }
        .footer-note { margin-top: 8px; font-size: 10px; color: #6b7280; }
        .page-footer { position: fixed; bottom: -28px; left: 0; right: 0; border-top: 1px solid #d1d5db; padding-top: 6px; font-size: 10px; color: #6b7280; }
        .page-footer .left { float: left; }
        .page-footer .right { float: right; }
        .clearfix { clear: both; }
    </style>
</head>
<body>
    @php($canRenderPngLogo = extension_loaded('gd'))

    <div class="page-footer">
        <div class="left">PT Bayan Resources Tbk - PPM Bayan</div>
        <div class="right">Halaman <span class="page-number"></span></div>
        <div class="clearfix"></div>
    </div>

    <div class="page-header">
        <table>
            <tr>
                <td style="border:0; padding:0; width:60%; vertical-align:top;">
                    <table class="brand-row">
                        <tr>
                            <td style="border:0; padding:0; vertical-align:top;" class="brand-icon-cell">
                                @if($canRenderPngLogo)
                                    <img src="{{ public_path('logo-bayan.png') }}" alt="Logo Bayan" class="brand-icon">
                                @else
                                    <div class="brand">B</div>
                                @endif
                            </td>
                            <td style="border:0; padding:0; vertical-align:top;">
                                @if($canRenderPngLogo)
                                    <img src="{{ public_path('logo-bayan-text.png') }}" alt="PPM Bayan" class="brand-text">
                                @else
                                    <div class="brand">Resume PPM Bayan</div>
                                @endif
                                <div class="subtitle">Laporan rekap anggaran, realisasi, pilar, divisi, dan detail kegiatan</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="border:0; padding:0; width:40%; vertical-align:top;" class="meta-box">
                    <div><strong>Tanggal Export</strong>: {{ now()->format('d/m/Y H:i') }}</div>
                    <div><strong>Dokumen</strong>: Resume PPM Bayan</div>
                    <div><strong>Format</strong>: PDF</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Parameter Laporan</div>
    <table class="filter-table">
        <tr>
            <td class="label">User</td>
            <td>{{ $filterSummary['user'] }}</td>
            <td class="label">Divisi</td>
            <td>{{ $filterSummary['divisi'] }}</td>
        </tr>
        <tr>
            <td class="label">Kategori</td>
            <td>{{ $filterSummary['kategori'] ?? '-' }}</td>
            <td class="label">Pilar</td>
            <td>{{ $filterSummary['pilar'] }}</td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td>{{ $filterSummary['status'] }}</td>
            <td class="label">Tahun</td>
            <td>{{ $filterSummary['tahun'] }}</td>
        </tr>
        <tr>
            <td class="label">Triwulan</td>
            <td>{{ $filterSummary['triwulan'] }}</td>
            <td class="label">Pencarian</td>
            <td>{{ $filterSummary['q'] }}</td>
        </tr>
    </table>

    <div class="section-title">Rekap Per Program</div>
    <table>
        <thead class="summary-head">
            <tr>
                <th>Program</th>
                <th>Kegiatan</th>
                <th>Anggaran</th>
                <th>Realisasi</th>
                <th>Sisa</th>
                <th>Serapan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($programSummary as $row)
                <tr>
                    <td>{{ $row['nama'] }}</td>
                    <td class="text-right">{{ $row['jumlah_kegiatan'] }}</td>
                    <td class="text-right">{{ number_format($row['total_anggaran'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['total_realisasi'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['sisa_anggaran'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['persentase_serapan'], 2, ',', '.') }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="muted">Belum ada data program.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="cards">
        <tr>
            <td>
                <div class="label">Total Pagu Program</div>
                <div class="value">Rp {{ number_format($summary['total_pagu'] ?? 0, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="label">Total Anggaran Kegiatan</div>
                <div class="value">Rp {{ number_format($summary['total_anggaran'] ?? 0, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="label">Total Realisasi</div>
                <div class="value">Rp {{ number_format($summary['total_realisasi'] ?? 0, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="label">Sisa Anggaran</div>
                <div class="value">Rp {{ number_format($summary['sisa_anggaran'] ?? 0, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Rekap Per Pilar</div>
    <table>
        <thead class="summary-head">
            <tr>
                <th>Pilar</th>
                <th>Program</th>
                <th>Kegiatan</th>
                <th>Anggaran</th>
                <th>Realisasi</th>
                <th>Sisa</th>
                <th>Serapan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pilarSummary as $row)
                <tr>
                    <td>{{ $row['nama'] }}</td>
                    <td class="text-right">{{ $row['jumlah_program'] }}</td>
                    <td class="text-right">{{ $row['jumlah_kegiatan'] }}</td>
                    <td class="text-right">{{ number_format($row['total_anggaran'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['total_realisasi'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['sisa_anggaran'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['persentase_serapan'], 2, ',', '.') }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="muted">Belum ada data pilar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Rekap Per Divisi</div>
    <table>
        <thead class="summary-head">
            <tr>
                <th>Divisi</th>
                <th>Program</th>
                <th>Kegiatan</th>
                <th>Anggaran</th>
                <th>Realisasi</th>
                <th>Sisa</th>
                <th>Serapan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($divisiSummary as $row)
                <tr>
                    <td>{{ $row['nama'] }}</td>
                    <td class="text-right">{{ $row['jumlah_program'] }}</td>
                    <td class="text-right">{{ $row['jumlah_kegiatan'] }}</td>
                    <td class="text-right">{{ number_format($row['total_anggaran'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['total_realisasi'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['sisa_anggaran'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['persentase_serapan'], 2, ',', '.') }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="muted">Belum ada data divisi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Rekap Per User</div>
    <table>
        <thead class="summary-head">
            <tr>
                <th>User</th>
                <th>Divisi</th>
                <th>Program</th>
                <th>Kegiatan</th>
                <th>Anggaran</th>
                <th>Realisasi</th>
                <th>Sisa</th>
                <th>Serapan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($userSummary as $row)
                <tr>
                    <td>{{ $row['nama'] }}</td>
                    <td>{{ $row['divisi'] ?? '-' }}</td>
                    <td class="text-right">{{ $row['jumlah_program'] }}</td>
                    <td class="text-right">{{ $row['jumlah_kegiatan'] }}</td>
                    <td class="text-right">{{ number_format($row['total_anggaran'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['total_realisasi'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['sisa_anggaran'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['persentase_serapan'], 2, ',', '.') }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="muted">Belum ada data user.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Detail Kegiatan</div>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Program</th>
                <th>Kegiatan</th>
                <th>Divisi</th>
                <th>Pilar</th>
                <th>User</th>
                <th>Status</th>
                <th>Anggaran</th>
                <th>Realisasi</th>
                <th>Sisa</th>
                <th>Serapan</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detailRows as $row)
                <tr>
                    <td>{{ $row['kode_ref'] }}</td>
                    <td>{{ $row['program'] }}</td>
                    <td>{{ $row['kegiatan'] }}</td>
                    <td>{{ $row['divisi'] }}</td>
                    <td>{{ $row['pilar'] ?: '-' }}</td>
                    <td>{{ $row['user_penginput'] }}</td>
                    <td>{{ $row['status_label'] }}</td>
                    <td class="text-right">{{ number_format($row['anggaran'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['realisasi'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['sisa_anggaran'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['persentase_serapan'], 2, ',', '.') }}%</td>
                    <td class="text-right">{{ number_format($row['progress'], 2, ',', '.') }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="muted">Belum ada data kegiatan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">
        Catatan: Nilai anggaran, realisasi, dan sisa pada laporan ini mengikuti filter aktif saat export dilakukan.
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font('Arial', 'normal');
            $pdf->page_text(740, 570, '{PAGE_NUM}', $font, 10, [0.42, 0.46, 0.52]);
        }
    </script>
</body>
</html>