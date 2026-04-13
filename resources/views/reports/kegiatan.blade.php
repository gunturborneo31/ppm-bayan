<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; }
        th { background-color: #1e40af; color: white; }
        tr:nth-child(even) { background-color: #f3f4f6; }
        h2 { color: #1e40af; }
    </style>
</head>
<body>
    <h2>Laporan Kegiatan PPM Bayan</h2>
    <p>Tanggal: {{ date('d/m/Y') }}</p>
    <table>
        <thead>
            <tr>
                <th>Nama Kegiatan</th>
                <th>Lokasi</th>
                <th>Program</th>
                <th>Divisi</th>
                <th>Status</th>
                <th>Target Output</th>
                <th>Rencana Biaya</th>
                <th>Realisasi Output</th>
                <th>Realisasi Biaya</th>
                <th>Progress (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kegiatans as $k)
            <tr>
                <td>{{ $k['nama'] }}</td>
                <td>{{ $k['lokasi'] ?? '-' }}</td>
                <td>{{ $k['program'] }}</td>
                <td>{{ $k['divisi'] }}</td>
                <td>{{ $k['status'] }}</td>
                <td>{{ number_format($k['target_output'], 2) }}</td>
                <td>{{ number_format($k['rencana_biaya'], 2) }}</td>
                <td>{{ number_format($k['realisasi_output'], 2) }}</td>
                <td>{{ number_format($k['realisasi_biaya'], 2) }}</td>
                <td>{{ $k['progress'] }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
