<?php

namespace App\Livewire;

use App\Models\LaporanCsr;
use App\Models\Perusahaan;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class StatistikTable extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch() { $this->resetPage(); }

    public function getChartDataProperty()
    {
        $kategoriData = DB::table('laporan_csrs')
            ->join('bidang_csrs', 'laporan_csrs.bidang_id', '=', 'bidang_csrs.id')
            ->select('bidang_csrs.nama', DB::raw('SUM(laporan_csrs.nominal) as total'))
            ->groupBy('bidang_csrs.id', 'bidang_csrs.nama')
            ->get();

        return [
            'labels' => $kategoriData->pluck('nama')->toArray(),
            'data' => $kategoriData->pluck('total')->toArray(),
        ];
    }

    public function render()
    {
        $query = Perusahaan::withCount('laporanCsrs')
            ->withSum('laporanCsrs', 'nominal');

        if ($this->search) {
            $query->where('nama', 'like', '%' . $this->search . '%');
        }

        return view('livewire.statistik-table', [
            'perusahaans' => $query->paginate(10),
        ]);
    }
}
