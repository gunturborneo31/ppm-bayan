<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE kegiatans MODIFY COLUMN status ENUM('draft','diajukan','diajukan_ulang','revisi','disetujui','ditolak','selesai') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE kegiatans SET status = 'diajukan' WHERE status = 'diajukan_ulang'");
            DB::statement("ALTER TABLE kegiatans MODIFY COLUMN status ENUM('draft','diajukan','revisi','disetujui','ditolak','selesai') NOT NULL DEFAULT 'draft'");
        }
    }
};
