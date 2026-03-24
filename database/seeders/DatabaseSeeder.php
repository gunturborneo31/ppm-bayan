<?php

namespace Database\Seeders;

use App\Models\Divisi;
use App\Models\Kegiatan;
use App\Models\Periode;
use App\Models\Pilar;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Superadmin
        $admin = User::create([
            'name'     => 'Admin CDO',
            'email'    => 'admin@ppm.com',
            'password' => Hash::make('password123'),
            'role'     => 'superadmin',
        ]);

        // Divisi
        $divisiA = Divisi::create(['nama' => 'Divisi Operasional', 'deskripsi' => 'Divisi Operasional Tambang', 'status' => true]);
        $divisiB = Divisi::create(['nama' => 'Divisi Lingkungan', 'deskripsi' => 'Divisi Lingkungan & CSR', 'status' => true]);

        // Divisi users
        $userA = User::create([
            'name'      => 'Budi Santoso',
            'email'     => 'budi@ppm.com',
            'password'  => Hash::make('password123'),
            'role'      => 'divisi',
            'divisi_id' => $divisiA->id,
        ]);
        $userB = User::create([
            'name'      => 'Siti Rahayu',
            'email'     => 'siti@ppm.com',
            'password'  => Hash::make('password123'),
            'role'      => 'divisi',
            'divisi_id' => $divisiB->id,
        ]);

        // Pilars
        $pilar1 = Pilar::create(['nama' => 'Pemberdayaan Ekonomi', 'deskripsi' => 'Program pemberdayaan ekonomi masyarakat']);
        $pilar2 = Pilar::create(['nama' => 'Pendidikan', 'deskripsi' => 'Program di bidang pendidikan']);
        $pilar3 = Pilar::create(['nama' => 'Kesehatan', 'deskripsi' => 'Program di bidang kesehatan']);
        $pilar4 = Pilar::create(['nama' => 'Lingkungan', 'deskripsi' => 'Program pelestarian lingkungan']);

        // Periodes
        $periode1 = Periode::create(['tahun' => 2025, 'triwulan' => 'Q1', 'status' => true]);
        $periode2 = Periode::create(['tahun' => 2025, 'triwulan' => 'Q2', 'status' => true]);
        $periode3 = Periode::create(['tahun' => 2025, 'triwulan' => 'Q3', 'status' => true]);
        $periode4 = Periode::create(['tahun' => 2025, 'triwulan' => 'Q4', 'status' => true]);

        // Programs
        $program1 = Program::create([
            'nama'          => 'Program CSR 2025 Operasional',
            'deskripsi'     => 'Program CSR divisi operasional tahun 2025',
            'target_output' => 100,
            'satuan'        => 'Orang',
            'rencana_biaya' => 500000000,
            'user_id'       => $admin->id,
        ]);
        $program2 = Program::create([
            'nama'          => 'Program Lingkungan Hidup 2025',
            'deskripsi'     => 'Program pelestarian lingkungan hidup',
            'target_output' => 50,
            'satuan'        => 'Kegiatan',
            'rencana_biaya' => 300000000,
            'user_id'       => $admin->id,
        ]);

        // Kegiatan
        $keg1 = Kegiatan::create([
            'program_id'    => $program1->id,
            'divisi_id'     => $divisiA->id,
            'nama'          => 'Pelatihan UMKM Masyarakat',
            'deskripsi'     => 'Pelatihan keterampilan untuk pelaku UMKM',
            'target_output' => 50,
            'rencana_biaya' => 150000000,
            'status'        => 'disetujui',
            'version'       => 1,
        ]);
        $keg1->pilars()->attach([$pilar1->id, $pilar2->id]);

        $keg2 = Kegiatan::create([
            'program_id'    => $program1->id,
            'divisi_id'     => $divisiA->id,
            'nama'          => 'Beasiswa Pendidikan',
            'deskripsi'     => 'Beasiswa bagi siswa berprestasi dari keluarga tidak mampu',
            'target_output' => 30,
            'rencana_biaya' => 100000000,
            'status'        => 'diajukan',
            'version'       => 1,
        ]);
        $keg2->pilars()->attach([$pilar2->id]);

        $keg3 = Kegiatan::create([
            'program_id'    => $program2->id,
            'divisi_id'     => $divisiB->id,
            'nama'          => 'Penanaman Pohon',
            'deskripsi'     => 'Penanaman pohon di area reklamasi tambang',
            'target_output' => 1000,
            'rencana_biaya' => 80000000,
            'status'        => 'draft',
            'version'       => 1,
        ]);
        $keg3->pilars()->attach([$pilar4->id]);

        // Realisasi for keg1
        \App\Models\Realisasi::create([
            'kegiatan_id'      => $keg1->id,
            'periode_id'       => $periode1->id,
            'realisasi_output' => 20,
            'realisasi_biaya'  => 45000000,
            'keterangan'       => 'Pelatihan batch pertama berhasil dilaksanakan',
        ]);
        \App\Models\Realisasi::create([
            'kegiatan_id'      => $keg1->id,
            'periode_id'       => $periode2->id,
            'realisasi_output' => 15,
            'realisasi_biaya'  => 40000000,
            'keterangan'       => 'Pelatihan batch kedua',
        ]);
    }
}
