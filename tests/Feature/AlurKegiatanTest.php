<?php

namespace Tests\Feature;

use App\Models\FileLampiran;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\Realisasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlurKegiatanTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    /** Buat kegiatan oleh Budi lalu disetujui admin; anggaran lokasi 1 juta. */
    private function kegiatanDisetujui(): Kegiatan
    {
        $this->actingAs($this->user('budi@ppm.com'))->post('/kegiatan', [
            'program_id' => Program::first()->id,
            'nama' => 'Kegiatan Uji',
            'lokasis' => [['lokasi' => 'Desa A', 'target_output' => 10, 'satuan' => 'Orang', 'rencana_biaya' => 1000000]],
        ])->assertSessionHasNoErrors();

        $kegiatan = Kegiatan::where('nama', 'Kegiatan Uji')->firstOrFail();
        $this->actingAs($this->user('admin@ppm.com'))->post("/kegiatan/{$kegiatan->id}/approve");

        return $kegiatan->fresh();
    }

    private function inputRealisasi(Kegiatan $kegiatan, string $tanggal, float $biaya, float $output = 1)
    {
        return $this->actingAs($this->user('budi@ppm.com'))->post('/realisasi', [
            'kegiatan_id' => $kegiatan->id,
            'kegiatan_lokasi_id' => $kegiatan->lokasis()->first()->id,
            'tanggal_realisasi' => $tanggal,
            'realisasi_output' => $output,
            'realisasi_biaya' => $biaya,
        ]);
    }

    public function test_seeder_bisa_dijalankan_ulang(): void
    {
        $this->artisan('db:seed')->assertSuccessful();
    }

    public function test_user_divisi_melihat_kegiatan_contoh_miliknya(): void
    {
        $budi = $this->actingAs($this->user('budi@ppm.com'))->get('/kegiatan');
        $this->assertCount(2, $budi->viewData('page')['props']['kegiatans']);

        $siti = $this->actingAs($this->user('siti@ppm.com'))->get('/kegiatan');
        $this->assertCount(1, $siti->viewData('page')['props']['kegiatans']);
    }

    public function test_pimpinan_bisa_membuka_detail_dan_berkomentar(): void
    {
        $pimpinan = $this->user('pimpinan@ppm.com');
        $this->post('/login', ['email' => 'pimpinan@ppm.com', 'password' => 'password123'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('resume.pilar.index'));
        $this->assertAuthenticatedAs($pimpinan);
        $kegiatan = Kegiatan::first();

        $this->actingAs($pimpinan)->get("/kegiatan/{$kegiatan->id}")->assertOk();
        $this->actingAs($pimpinan)->post("/kegiatan/{$kegiatan->id}/comments", ['comment' => 'Lanjutkan'])
            ->assertSessionHasNoErrors();
        $this->assertSame(1, $kegiatan->comments()->count());

        // Halaman lain tetap dikunci untuk pimpinan.
        $this->actingAs($pimpinan)->get('/dashboard')->assertRedirect(route('resume.pilar.index'));
    }

    public function test_file_hanya_bisa_dihapus_pengunggah_atau_superadmin(): void
    {
        $kegiatan = Kegiatan::first();
        $file = FileLampiran::create([
            'kegiatan_id' => $kegiatan->id, 'file_path' => 'x.pdf', 'file_name' => 'x.pdf',
            'file_type' => 'application/pdf', 'file_size' => 1, 'kategori' => 'evidence',
            'uploaded_by' => $this->user('budi@ppm.com')->id,
        ]);

        $this->actingAs($this->user('siti@ppm.com'))->delete("/files/{$file->id}")->assertForbidden();
        $this->assertNotNull(FileLampiran::find($file->id));

        $this->actingAs($this->user('admin@ppm.com'))->delete("/files/{$file->id}")->assertRedirect();
        $this->assertNull(FileLampiran::find($file->id));
    }

    public function test_total_realisasi_tidak_boleh_melebihi_anggaran_lokasi(): void
    {
        $kegiatan = $this->kegiatanDisetujui();

        $this->inputRealisasi($kegiatan, '2026-03-05', 400000)->assertSessionHasNoErrors();
        $this->inputRealisasi($kegiatan, '2026-04-05', 900000)->assertSessionHasErrors('realisasi_biaya');
        $this->inputRealisasi($kegiatan, '2026-04-05', 600000)->assertSessionHasNoErrors();

        $this->assertEquals(1000000, $kegiatan->realisasis()->sum('realisasi_biaya'));

        // Edit input pertama: tidak dihitung dua kali, tapi tetap dibatasi total.
        $pertama = Realisasi::where('kegiatan_id', $kegiatan->id)->orderBy('id')->first();
        $this->actingAs($this->user('budi@ppm.com'))
            ->put("/realisasi/{$pertama->id}", ['tanggal_realisasi' => '2026-03-05', 'realisasi_biaya' => 400000])
            ->assertSessionHasNoErrors();
        $this->actingAs($this->user('budi@ppm.com'))
            ->put("/realisasi/{$pertama->id}", ['tanggal_realisasi' => '2026-03-05', 'realisasi_biaya' => 500000])
            ->assertSessionHasErrors('realisasi_biaya');
    }

    public function test_resume_menjumlahkan_semua_input_realisasi(): void
    {
        $kegiatan = $this->kegiatanDisetujui();
        $this->inputRealisasi($kegiatan, '2026-03-05', 400000);
        $this->inputRealisasi($kegiatan, '2026-04-05', 300000);

        $response = $this->actingAs($this->user('admin@ppm.com'))->get(route('resume.index', ['q' => 'Kegiatan Uji']));
        $summary = $response->viewData('page')['props']['resume']['summary'];

        $this->assertEquals(700000, $summary['total_realisasi']);
    }
}
