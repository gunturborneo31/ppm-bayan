<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisiController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\PilarController;
use App\Http\Controllers\PublicDashboardController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\RealisasiController;
use App\Http\Controllers\RegulasiController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Models\Kunjungan;
use App\Models\Berita;

// Auth
Route::get('/', function () {
    try {
        $today = date('Y-m-d');
        $kunjungan = Kunjungan::firstOrCreate(
            ['tanggal' => $today],
            ['jumlah' => 0]
        );
        $kunjungan->increment('jumlah');
    } catch (\Exception $e) {}
    return view('welcome');
})->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

// Berita routes
Route::get('/berita', function () {
    $beritas = Berita::with('kategori')->latest('published_at')->paginate(9);
    return view('berita.index', compact('beritas'));
})->name('berita.index');

Route::get('/berita/{slug}', function ($slug) {
    $berita = Berita::with('kategori')->where('slug', $slug)->firstOrFail();
    $related = Berita::with('kategori')
        ->where('id', '!=', $berita->id)
        ->where('kategori_id', $berita->kategori_id)
        ->latest('published_at')
        ->take(3)
        ->get();
    return view('berita.show', compact('berita', 'related'));
})->name('berita.show');


Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/dashboard-publik', [PublicDashboardController::class, 'index'])->name('public.dashboard');

Route::middleware(['auth', 'pimpinan.resume-only'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/resume', [ResumeController::class, 'index'])->name('resume.index');
    Route::get('/resume/pilar', [ResumeController::class, 'pilar'])->name('resume.pilar.index');
    Route::get('/resume/program', [ResumeController::class, 'program'])->name('resume.program.index');
    Route::get('/resume/divisi', [ResumeController::class, 'divisi'])->name('resume.divisi.index');
    Route::get('/resume/user', [ResumeController::class, 'user'])->name('resume.user.index');
    Route::get('/resume/export/{type?}', [ResumeController::class, 'export'])
        ->where('type', 'pilar|program|divisi|user')
        ->name('resume.export');

    // Master data – superadmin only
    Route::middleware('superadmin')->group(function () {
        Route::resource('divisi', DivisiController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('pilar', PilarController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('periode', PeriodeController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('lokasi', LokasiController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('lokasi/{lokasi}/merge', [LokasiController::class, 'merge'])->name('lokasi.merge');
        Route::resource('program', ProgramController::class)->only(['index', 'store', 'update', 'destroy', 'show']);
        Route::resource('user', UserController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('pengumuman', PengumumanController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('regulasi', RegulasiController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings/{setting}', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('settings/toggle-planning-lock', [SettingsController::class, 'togglePlanningLock'])->name('settings.togglePlanningLock');
    });

    // Kegiatan
    Route::resource('kegiatan', KegiatanController::class)->only(['index', 'store', 'update', 'destroy', 'show']);
    Route::post('kegiatan/{kegiatan}/comments', [KegiatanController::class, 'storeComment'])->name('kegiatan.comments.store');
    Route::get('perencanaan', [KegiatanController::class, 'perencanaan'])->name('perencanaan.index');
    Route::post('kegiatan/{kegiatan}/submit', [KegiatanController::class, 'submit'])->name('kegiatan.submit');
    Route::post('kegiatan/{kegiatan}/approve', [KegiatanController::class, 'approve'])->middleware('superadmin')->name('kegiatan.approve');
    Route::post('kegiatan/{kegiatan}/reject', [KegiatanController::class, 'reject'])->middleware('superadmin')->name('kegiatan.reject');

    // Realisasi
    Route::resource('realisasi', RealisasiController::class)->only(['index', 'store', 'update', 'destroy']);

    // Files
    Route::post('files/upload', [FileController::class, 'upload'])->name('files.upload');
    Route::delete('files/{file}', [FileController::class, 'destroy'])->name('files.destroy');
    Route::get('files/{file}/preview', [FileController::class, 'preview'])->name('files.preview');
    Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');

    // Activity Log
    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

    // Reports
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
});
