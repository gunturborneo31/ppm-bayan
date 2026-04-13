<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Kegiatan;
use App\Models\Periode;
use App\Models\Program;
use App\Models\Realisasi;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResumeFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_open_resume_page(): void
    {
        $user = User::factory()->createOne([
            'role' => 'superadmin',
            'divisi_id' => null,
        ]);
        /** @var Authenticatable $authUser */
        $authUser = $user;

        $response = $this->actingAs($authUser)->get(route('resume.index'));

        $response->assertOk();
        $response->assertSee('Resume');
    }

    public function test_superadmin_can_export_resume_excel(): void
    {
        $user = User::factory()->createOne([
            'role' => 'superadmin',
            'divisi_id' => null,
        ]);
        /** @var Authenticatable $authUser */
        $authUser = $user;

        $response = $this->actingAs($authUser)->get(route('resume.export', ['format' => 'excel']));

        $response->assertOk();
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('content-disposition'));
    }

    public function test_superadmin_can_export_resume_pdf(): void
    {
        $user = User::factory()->createOne([
            'role' => 'superadmin',
            'divisi_id' => null,
        ]);
        /** @var Authenticatable $authUser */
        $authUser = $user;

        $response = $this->actingAs($authUser)->get(route('resume.export', ['format' => 'pdf']));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_resume_supports_year_bulan_and_pagination_filters(): void
    {
        $user = User::factory()->createOne([
            'role' => 'superadmin',
            'divisi_id' => null,
        ]);
        /** @var Authenticatable $authUser */
        $authUser = $user;

        $divisi = Divisi::query()->create([
            'nama' => 'Operasional',
            'deskripsi' => 'Divisi operasional',
            'status' => true,
        ]);

        $program = Program::query()->create([
            'nama' => 'Program Tahunan',
            'deskripsi' => 'Program untuk pengujian resume',
            'target_output' => 100,
            'satuan' => 'Unit',
            'rencana_biaya' => 5000000,
            'user_id' => $user->id,
        ]);

        $kegiatan = Kegiatan::query()->create([
            'program_id' => $program->id,
            'divisi_id' => $divisi->id,
            'nama' => 'Kegiatan Filter Resume',
            'deskripsi' => 'Pengujian filter tahun dan bulan',
            'target_output' => 10,
            'satuan' => 'Unit',
            'rencana_biaya' => 1000000,
            'status' => 'disetujui',
        ]);

        $periode = Periode::query()->create([
            'tahun' => 2026,
            'bulan' => 1,
            'triwulan' => 'Tw 1',
            'status' => true,
        ]);

        Realisasi::query()->create([
            'kegiatan_id' => $kegiatan->id,
            'periode_id' => $periode->id,
            'realisasi_output' => 4,
            'realisasi_biaya' => 250000,
            'keterangan' => 'Realisasi bulan pertama',
        ]);

        $response = $this->actingAs($authUser)->get(route('resume.index', [
            'tahun' => 2026,
            'bulan' => 1,
            'per_page' => 15,
        ]));

        $response->assertOk();
        $response->assertSee('Jan');
        $response->assertSee('2026');
        $response->assertSee('Kegiatan Filter Resume');
    }

    public function test_resume_supports_sorting_detail_columns(): void
    {
        $user = User::factory()->createOne([
            'role' => 'superadmin',
            'divisi_id' => null,
        ]);
        /** @var Authenticatable $authUser */
        $authUser = $user;

        $divisi = Divisi::query()->create([
            'nama' => 'Finance',
            'deskripsi' => 'Divisi finance',
            'status' => true,
        ]);

        $program = Program::query()->create([
            'nama' => 'Program Sort',
            'deskripsi' => 'Program sorting',
            'target_output' => 100,
            'satuan' => 'Unit',
            'rencana_biaya' => 10000000,
            'user_id' => $user->id,
        ]);

        Kegiatan::query()->create([
            'program_id' => $program->id,
            'divisi_id' => $divisi->id,
            'nama' => 'AAA Kegiatan',
            'deskripsi' => 'Kegiatan awal',
            'target_output' => 10,
            'satuan' => 'Unit',
            'rencana_biaya' => 100000,
            'status' => 'disetujui',
        ]);

        Kegiatan::query()->create([
            'program_id' => $program->id,
            'divisi_id' => $divisi->id,
            'nama' => 'ZZZ Kegiatan',
            'deskripsi' => 'Kegiatan akhir',
            'target_output' => 10,
            'satuan' => 'Unit',
            'rencana_biaya' => 200000,
            'status' => 'disetujui',
        ]);

        $response = $this->actingAs($authUser)->get(route('resume.index', [
            'sort_by' => 'kegiatan',
            'sort_direction' => 'desc',
        ]));

        $response->assertOk();
        $response->assertSee('ZZZ Kegiatan');
    }
}