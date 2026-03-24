<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisiController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\PilarController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\RealisasiController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master data – superadmin only
    Route::middleware('superadmin')->group(function () {
        Route::resource('divisi', DivisiController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('pilar', PilarController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('periode', PeriodeController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('program', ProgramController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    // Kegiatan
    Route::resource('kegiatan', KegiatanController::class)->only(['index', 'store', 'update', 'destroy', 'show']);
    Route::post('kegiatan/{kegiatan}/submit', [KegiatanController::class, 'submit'])->name('kegiatan.submit');
    Route::post('kegiatan/{kegiatan}/approve', [KegiatanController::class, 'approve'])->middleware('superadmin')->name('kegiatan.approve');
    Route::post('kegiatan/{kegiatan}/reject', [KegiatanController::class, 'reject'])->middleware('superadmin')->name('kegiatan.reject');

    // Realisasi
    Route::resource('realisasi', RealisasiController::class)->only(['index', 'store', 'update', 'destroy']);

    // Files
    Route::post('files/upload', [FileController::class, 'upload'])->name('files.upload');
    Route::delete('files/{file}', [FileController::class, 'destroy'])->name('files.destroy');
    Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');

    // Activity Log
    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

    // Reports
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
});
