<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Realisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function export(Request $request)
    {
        $format  = $request->get('format', 'excel');
        $divisi  = $request->get('divisi_id');
        $pilar   = $request->get('pilar_id');
        $tahun   = $request->get('tahun');

        $query = Kegiatan::with(['program', 'divisi', 'pilars', 'realisasis.periode'])
            ->when($divisi, fn($q) => $q->where('divisi_id', $divisi))
            ->when($pilar, fn($q) => $q->whereHas('pilars', fn($q2) => $q2->where('pilars.id', $pilar)))
            ->when($tahun, fn($q) => $q->whereHas('realisasis.periode', fn($q2) => $q2->where('tahun', $tahun)));

        $kegiatans = $query->get()->map(fn($k) => [
            'nama'             => $k->nama,
            'program'          => $k->program?->nama,
            'divisi'           => $k->divisi?->nama,
            'status'           => $k->status->label(),
            'target_output'    => $k->target_output,
            'rencana_biaya'    => $k->rencana_biaya,
            'realisasi_output' => $k->realisasis->sum('realisasi_output'),
            'realisasi_biaya'  => $k->realisasis->sum('realisasi_biaya'),
            'progress'         => $k->progress,
        ]);

        if ($format === 'excel') {
            return $this->exportExcel($kegiatans);
        }

        return $this->exportPdf($kegiatans);
    }

    private function exportExcel($data)
    {
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="laporan-ppm.csv"'];

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nama Kegiatan', 'Program', 'Divisi', 'Status', 'Target Output', 'Rencana Biaya', 'Realisasi Output', 'Realisasi Biaya', 'Progress (%)']);
            foreach ($data as $row) {
                fputcsv($handle, array_values($row));
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportPdf($data)
    {
        $html = view('reports.kegiatan', ['kegiatans' => $data])->render();
        $pdf  = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        return $pdf->download('laporan-ppm.pdf');
    }
}
