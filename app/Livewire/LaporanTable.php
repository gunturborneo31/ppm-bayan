<?php

namespace App\Livewire;

use App\Models\LaporanCsr;
use App\Models\BidangCsr;
use Livewire\Component;
use Livewire\WithPagination;

class LaporanTable extends Component
{
    use WithPagination;

    public $search = '';
    public $tahun = '';
    public $bidang = '';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingTahun() { $this->resetPage(); }
    public function updatingBidang() { $this->resetPage(); }

    public function render()
    {
        $query = LaporanCsr::with(['bidang', 'program', 'perusahaan'])
            ->latest('tahun');

        if ($this->search) {
            $query->whereHas('perusahaan', function($q) {
                $q->where('nama', 'like', '%' . $this->search . '%');
            })->orWhereHas('program', function($q) {
                $q->where('nama', 'like', '%' . $this->search . '%');
            })->orWhere('lokasi', 'like', '%' . $this->search . '%');
        }

        if ($this->tahun) {
            $query->where('tahun', $this->tahun);
        }

        if ($this->bidang) {
            $query->where('bidang_id', $this->bidang);
        }

        return view('livewire.laporan-table', [
            'laporans' => $query->paginate(10),
            'bidangs' => BidangCsr::all(),
            'tahuns' => LaporanCsr::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun')
        ]);
    }
}
