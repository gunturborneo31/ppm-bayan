<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Kegiatan;
use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperadmin()) {
            $stats = [
                'total_program'   => Program::count(),
                'total_kegiatan'  => Kegiatan::count(),
                'kegiatan_status' => Kegiatan::selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status'),
                'recent_logs'     => ActivityLog::with('user')->latest()->limit(10)->get(),
            ];
        } else {
            $divisiId = $user->divisi_id;
            $stats = [
                'total_kegiatan'  => Kegiatan::where('divisi_id', $divisiId)->count(),
                'kegiatan_status' => Kegiatan::where('divisi_id', $divisiId)
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status'),
                'kegiatan_list'   => Kegiatan::where('divisi_id', $divisiId)
                    ->with('program')
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(fn($k) => array_merge($k->toArray(), ['progress' => $k->progress])),
            ];
        }

        return Inertia::render('Dashboard/Index', compact('stats'));
    }
}
