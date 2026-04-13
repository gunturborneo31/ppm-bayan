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

        $query = Kegiatan::with(['program.pilar', 'divisi', 'lokasis', 'realisasis.periode'])
            ->when($divisi, fn($q) => $q->where('divisi_id', $divisi))
            ->when($pilar, fn($q) => $q->whereHas('program', fn($q2) => $q2->where('pilar_id', $pilar)))
            ->when($tahun, fn($q) => $q->whereHas('realisasis.periode', fn($q2) => $q2->where('tahun', $tahun)));

        $kegiatans = $query->get()->flatMap(function ($k) use ($tahun) {
            $realisasis = $k->realisasis
                ->filter(fn($r) => !$tahun || (string) $r->periode?->tahun === (string) $tahun);

            return $k->lokasis->map(function ($lokasi, $index) use ($k, $realisasis) {
                $perLokasi = $realisasis->where('kegiatan_lokasi_id', $lokasi->id);
                if ($perLokasi->isEmpty() && $index === 0) {
                    $perLokasi = $realisasis->whereNull('kegiatan_lokasi_id');
                }

                $targetOutput = (float) ($lokasi->target_output ?? 0);
                $realisasiOutput = (float) $perLokasi->sum('realisasi_output');
                $progress = $targetOutput > 0 ? round(($realisasiOutput / $targetOutput) * 100, 2) : 0;

                return [
                    'nama'             => $k->nama,
                    'lokasi'           => $lokasi->lokasi,
                    'program'          => $k->program?->nama,
                    'divisi'           => $k->divisi?->nama,
                    'status'           => $k->status->label(),
                    'target_output'    => $targetOutput,
                    'rencana_biaya'    => (float) ($lokasi->rencana_biaya ?? 0),
                    'realisasi_output' => $realisasiOutput,
                    'realisasi_biaya'  => (float) $perLokasi->sum('realisasi_biaya'),
                    'progress'         => min(100, max(0, $progress)),
                ];
            });
        })->values();

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
            fputcsv($handle, ['Nama Kegiatan', 'Lokasi', 'Program', 'Divisi', 'Status', 'Target Output', 'Rencana Biaya', 'Realisasi Output', 'Realisasi Biaya', 'Progress (%)']);
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
