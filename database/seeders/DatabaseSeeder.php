<?php

namespace Database\Seeders;

use App\Models\Divisi;
use App\Models\Kegiatan;
use App\Models\Periode;
use App\Models\Pilar;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\KategoriBerita;
use App\Models\Berita;
use App\Models\BidangCsr;
use App\Models\Perusahaan;
use App\Models\ProgramCsr;
use App\Models\LaporanCsr;
use App\Models\Regulasi;
use App\Models\Pengumuman;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Superadmin
        $admin = User::updateOrCreate(
            ['email' => 'admin@ppm.com'],
            [
                'name'     => 'Admin CDO',
                'password' => Hash::make('password123'),
                'role'     => 'superadmin',
            ]
        );

        // Divisi
        $divisiA = Divisi::updateOrCreate(
            ['nama' => 'Divisi Operasional'],
            ['deskripsi' => 'Divisi Operasional Tambang', 'status' => true]
        );
        $divisiB = Divisi::updateOrCreate(
            ['nama' => 'Divisi Lingkungan'],
            ['deskripsi' => 'Divisi Lingkungan & CSR', 'status' => true]
        );

        // Divisi users
        $userA = User::updateOrCreate(
            ['email' => 'budi@ppm.com'],
            [
                'name'      => 'Budi Santoso',
                'password'  => Hash::make('password123'),
                'role'      => 'divisi',
                'divisi_id' => $divisiA->id,
            ]
        );
        $userB = User::updateOrCreate(
            ['email' => 'siti@ppm.com'],
            [
                'name'      => 'Siti Rahayu',
                'password'  => Hash::make('password123'),
                'role'      => 'divisi',
                'divisi_id' => $divisiB->id,
            ]
        );

        // Pilars
        $pilar1 = Pilar::updateOrCreate(['nama' => 'Pemberdayaan Ekonomi'], ['deskripsi' => 'Program pemberdayaan ekonomi masyarakat']);
        $pilar2 = Pilar::updateOrCreate(['nama' => 'Pendidikan'], ['deskripsi' => 'Program di bidang pendidikan']);
        $pilar3 = Pilar::updateOrCreate(['nama' => 'Kesehatan'], ['deskripsi' => 'Program di bidang kesehatan']);
        $pilar4 = Pilar::updateOrCreate(['nama' => 'Lingkungan'], ['deskripsi' => 'Program pelestarian lingkungan']);

        // Periodes
        $periode1 = Periode::updateOrCreate(['tahun' => 2025, 'triwulan' => 'Tw 1'], ['status' => true]);
        $periode2 = Periode::updateOrCreate(['tahun' => 2025, 'triwulan' => 'Tw 2'], ['status' => true]);
        $periode3 = Periode::updateOrCreate(['tahun' => 2025, 'triwulan' => 'Tw 3'], ['status' => true]);
        $periode4 = Periode::updateOrCreate(['tahun' => 2025, 'triwulan' => 'Tw 4'], ['status' => true]);

        // Programs
        $program1 = Program::updateOrCreate(
            ['nama' => 'Program CSR 2025 Operasional'],
            [
                'deskripsi'     => 'Program CSR divisi operasional tahun 2025',
                'target_output' => 100,
                'satuan'        => 'Orang',
                'rencana_biaya' => 500000000,
                'user_id'       => $admin->id,
            ]
        );
        $program2 = Program::updateOrCreate(
            ['nama' => 'Program Lingkungan Hidup 2025'],
            [
                'deskripsi'     => 'Program pelestarian lingkungan hidup',
                'target_output' => 50,
                'satuan'        => 'Kegiatan',
                'rencana_biaya' => 300000000,
                'user_id'       => $admin->id,
            ]
        );

        // Kegiatan
        $keg1 = Kegiatan::updateOrCreate(
            [
                'program_id' => $program1->id,
                'divisi_id' => $divisiA->id,
                'nama' => 'Pelatihan UMKM Masyarakat',
            ],
            [
                'deskripsi' => 'Pelatihan keterampilan untuk pelaku UMKM',
                'target_output' => 50,
                'rencana_biaya' => 150000000,
                'status' => 'disetujui',
                'version' => 1,
            ]
        );
        $keg1->pilars()->syncWithoutDetaching([$pilar1->id, $pilar2->id]);

        $keg2 = Kegiatan::updateOrCreate(
            [
                'program_id' => $program1->id,
                'divisi_id' => $divisiA->id,
                'nama' => 'Beasiswa Pendidikan',
            ],
            [
                'deskripsi' => 'Beasiswa bagi siswa berprestasi dari keluarga tidak mampu',
                'target_output' => 30,
                'rencana_biaya' => 100000000,
                'status' => 'diajukan',
                'version' => 1,
            ]
        );
        $keg2->pilars()->syncWithoutDetaching([$pilar2->id]);

        $keg3 = Kegiatan::updateOrCreate(
            [
                'program_id' => $program2->id,
                'divisi_id' => $divisiB->id,
                'nama' => 'Penanaman Pohon',
            ],
            [
                'deskripsi' => 'Penanaman pohon di area reklamasi tambang',
                'target_output' => 1000,
                'rencana_biaya' => 80000000,
                'status' => 'draft',
                'version' => 1,
            ]
        );
        $keg3->pilars()->syncWithoutDetaching([$pilar4->id]);

        // Realisasi for keg1
        \App\Models\Realisasi::updateOrCreate(
            [
                'kegiatan_id' => $keg1->id,
                'periode_id' => $periode1->id,
            ],
            [
                'realisasi_output' => 20,
                'realisasi_biaya' => 45000000,
                'keterangan' => 'Pelatihan batch pertama berhasil dilaksanakan',
            ]
        );
        \App\Models\Realisasi::updateOrCreate(
            [
                'kegiatan_id' => $keg1->id,
                'periode_id' => $periode2->id,
            ],
            [
                'realisasi_output' => 15,
                'realisasi_biaya' => 40000000,
                'keterangan' => 'Pelatihan batch kedua',
            ]
        );
    
    
        User::factory()->create([
            'name'     => 'Admin Bayan',
            'email'    => 'admin@bayangroup.com',
            'password' => bcrypt('password'),
        ]);

        // ===== KATEGORI BERITA =====
        $katCSR     = KategoriBerita::create(['nama' => 'Berita CSR',              'slug' => 'berita-csr']);
        $katLingk   = KategoriBerita::create(['nama' => 'Lingkungan',              'slug' => 'lingkungan']);
        $katSosial  = KategoriBerita::create(['nama' => 'Sosial & Pendidikan',     'slug' => 'sosial-pendidikan']);
        $katPemberd = KategoriBerita::create(['nama' => 'Pemberdayaan Masyarakat', 'slug' => 'pemberdayaan-masyarakat']);

        // ===== BERITA =====
        $articles = [
            [
                'judul'        => 'Bayan Group Salurkan Beasiswa Tabang untuk 120 Pelajar Berprestasi 2026',
                'slug'         => 'bayan-group-salurkan-beasiswa-tabang-120-pelajar-2026',
                'kategori_id'  => $katSosial->id,
                'thumbnail'    => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=900&q=80',
                'excerpt'      => 'Sebanyak 120 pelajar dari kawasan operasional Tabang, Kutai Kartanegara, menerima beasiswa penuh dari program PPM Bayan Group tahun 2026.',
                'isi'          => '<p>PT Bayan Resources Tbk melalui program Pengembangan dan Pemberdayaan Masyarakat (PPM) kembali menyalurkan beasiswa pendidikan kepada 120 pelajar berprestasi dari kawasan Tabang, Kutai Kartanegara, Kalimantan Timur.</p><p>Beasiswa senilai total Rp 2,4 miliar ini diberikan dalam bentuk biaya pendidikan penuh mulai dari tingkat SMP hingga perguruan tinggi. Para penerima beasiswa dipilih berdasarkan prestasi akademik dan kondisi ekonomi keluarga.</p><h2>Komitmen Jangka Panjang</h2><p>Direktur CSR PT Bayan Resources Tbk menyatakan bahwa program beasiswa ini merupakan bagian dari komitmen jangka panjang perusahaan untuk mencerdaskan generasi penerus di kawasan operasional tambang.</p><ul><li>Siswa SMP dan SMA dari Kecamatan Tabang</li><li>Mahasiswa D3/S1 jurusan teknik, kesehatan, dan pertanian</li><li>Guru desa yang ingin meningkatkan kualifikasi pendidikan</li></ul>',
                'published_at' => now()->subDays(2),
            ],
            [
                'judul'        => 'Program Klinik Keliling PPM Jangkau 3.200 Warga Terpencil di Kutai Barat',
                'slug'         => 'klinik-keliling-ppm-jangkau-3200-warga-kutai-barat',
                'kategori_id'  => $katCSR->id,
                'thumbnail'    => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=900&q=80',
                'excerpt'      => 'Klinik keliling PPM Bayan Group berhasil menjangkau 3.200 warga di 12 desa terpencil di Kutai Barat yang selama ini minim akses layanan kesehatan.',
                'isi'          => '<p>Program Klinik Keliling yang diselenggarakan oleh PPM PT Bayan Resources Tbk telah berhasil memberikan layanan kesehatan gratis kepada 3.200 warga di 12 desa terpencil di Kutai Barat, Kalimantan Timur.</p><ul><li>Pemeriksaan kesehatan umum</li><li>Imunisasi anak balita</li><li>Pemeriksaan gizi ibu hamil</li><li>Distribusi obat-obatan gratis</li><li>Penyuluhan PHBS</li></ul>',
                'published_at' => now()->subDays(5),
            ],
            [
                'judul'        => 'Penanaman 50.000 Pohon di Area Pascatambang Tabang — Komitmen Reklamasi Bayan',
                'slug'         => 'penanaman-50000-pohon-pascatambang-tabang-2026',
                'kategori_id'  => $katLingk->id,
                'thumbnail'    => 'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?w=900&q=80',
                'excerpt'      => 'PT Bayan Resources Tbk menanam 50.000 pohon endemik Kalimantan di lahan pascatambang Tabang sebagai bagian dari program reklamasi dan rehabilitasi lingkungan 2026.',
                'isi'          => '<p>Sebagai wujud komitmen terhadap keberlanjutan lingkungan, PT Bayan Resources Tbk melaksanakan program penanaman 50.000 pohon endemik Kalimantan di lahan pascatambang Tabang.</p><ul><li><strong>Ulin</strong> — kayu besi endemik Kalimantan</li><li><strong>Meranti Merah</strong> — pohon hutan hujan tropis</li><li><strong>Sengon</strong> — pemulihan cepat lahan kritis</li><li><strong>Durian Hutan</strong> — ketahanan pangan lokal</li></ul>',
                'published_at' => now()->subDays(9),
            ],
            [
                'judul'        => 'UMKM Madu Hutan Desa Muara Bengkal Tembus Pasar Ekspor Berkat Dukungan PPM',
                'slug'         => 'umkm-madu-hutan-muara-bengkal-ekspor-ppm-bayan',
                'kategori_id'  => $katPemberd->id,
                'thumbnail'    => 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=900&q=80',
                'excerpt'      => 'Kelompok UMKM Madu Hutan Desa Muara Bengkal binaan PPM Bayan berhasil menembus pasar ekspor ke Malaysia dan Singapura dengan nilai transaksi Rp 1,2 miliar pada 2026.',
                'isi'          => '<p>Kisah sukses datang dari Desa Muara Bengkal, Kutai Timur. Kelompok UMKM Madu Hutan yang dibina oleh program PPM PT Bayan Resources Tbk berhasil menembus pasar ekspor internasional untuk pertama kalinya.</p><p>Produk madu hutan liar dari hutan Kalimantan Timur ini kini resmi diekspor ke Malaysia dan Singapura, dengan nilai transaksi mencapai Rp 1,2 miliar sepanjang tahun 2026.</p>',
                'published_at' => now()->subDays(14),
            ],
            [
                'judul'        => 'Infrastruktur Jalan Desa Ritan Baru Selesai Dibangun — Akses 5 Desa Kini Terbuka',
                'slug'         => 'infrastruktur-jalan-desa-ritan-baru-selesai-2026',
                'kategori_id'  => $katPemberd->id,
                'thumbnail'    => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=900&q=80',
                'excerpt'      => 'Pembangunan jalan penghubung sepanjang 12 km di Desa Ritan Baru telah selesai dikerjakan PPM Bayan, membuka akses bagi 5 desa yang selama ini terisolir.',
                'isi'          => '<p>Sebuah pencapaian besar bagi masyarakat di pedalaman Kutai Barat — jalan penghubung sepanjang 12 kilometer kini telah selesai dibangun oleh program PPM PT Bayan Resources Tbk.</p><ul><li>Harga bahan pokok turun 30-40%</li><li>Hasil pertanian bisa dipasarkan ke kota</li><li>Layanan ambulans kini bisa menjangkau wilayah ini</li></ul>',
                'published_at' => now()->subDays(20),
            ],
            [
                'judul'        => 'Bayan Group Raih Penghargaan PROPER Hijau dari KLHK 2026',
                'slug'         => 'bayan-group-proper-hijau-klhk-2026',
                'kategori_id'  => $katLingk->id,
                'thumbnail'    => 'https://images.unsplash.com/photo-1542601906897-ecd46e2e9b8d?w=900&q=80',
                'excerpt'      => 'PT Bayan Resources Tbk kembali meraih penghargaan PROPER Hijau dari Kementerian Lingkungan Hidup dan Kehutanan atas komitmen pengelolaan lingkungan yang melampaui standar regulasi.',
                'isi'          => '<p>PT Bayan Resources Tbk kembali membuktikan diri sebagai perusahaan tambang yang bertanggung jawab terhadap lingkungan, meraih PROPER Hijau untuk ketiga kalinya berturut-turut.</p><ul><li>Zero discharge limbah ke badan air</li><li>Energi terbarukan di seluruh camp operasional</li><li>Program biodiversity monitoring di kawasan konservasi</li></ul>',
                'published_at' => now()->subDays(25),
            ],
            [
                'judul'        => 'Pelatihan Petani Kakao PPM Bayan: 300 Petani Kuasai Teknik Fermentasi Modern',
                'slug'         => 'pelatihan-petani-kakao-ppm-bayan-fermentasi-modern',
                'kategori_id'  => $katPemberd->id,
                'thumbnail'    => 'https://images.unsplash.com/photo-1495467033336-2effd8753d51?w=900&q=80',
                'excerpt'      => '300 petani kakao di Kalimantan Timur menguasai teknik fermentasi modern hasil pelatihan PPM Bayan Group, meningkatkan nilai jual produk kakao hingga 3x lipat.',
                'isi'          => '<p>Tiga ratus petani kakao dari 15 desa di Kalimantan Timur kini telah menguasai teknik fermentasi kakao modern berkat program pelatihan PPM Bayan.</p><ul><li>Harga jual kakao naik dari Rp 25.000 menjadi Rp 75.000/kg</li><li>Kualitas flavor grade A tercapai secara konsisten</li><li>Pembeli premium dari Eropa mulai menjajaki kerjasama langsung</li></ul>',
                'published_at' => now()->subDays(33),
            ],
            [
                'judul'        => 'Forum Musyawarah PPM 2026: Masyarakat Tentukan Prioritas Program Bersama',
                'slug'         => 'forum-musyawarah-ppm-2026-prioritas-program',
                'kategori_id'  => $katCSR->id,
                'thumbnail'    => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=900&q=80',
                'excerpt'      => 'Forum Musyawarah PPM 2026 melibatkan 450 perwakilan masyarakat dari 28 desa untuk bersama menentukan prioritas program pemberdayaan tahun anggaran 2026-2027.',
                'isi'          => '<p>PPM PT Bayan Resources Tbk menyelenggarakan Forum Musyawarah Perencanaan PPM 2026 yang dihadiri oleh 450 perwakilan masyarakat dari 28 desa.</p><ol><li>Peningkatan akses air bersih di 8 desa</li><li>Pembangunan 3 gedung sekolah dasar</li><li>Program beasiswa S1 bidang kesehatan</li><li>Penguatan kelompok tani kakao dan karet</li><li>Rehabilitasi sumber mata air</li></ol>',
                'published_at' => now()->subDays(40),
            ],
            [
                'judul'        => 'Sistem Irigasi Modern PPM Bayan Airi 1.200 Hektar Sawah di Kabupaten Paser',
                'slug'         => 'irigasi-modern-ppm-bayan-1200-hektar-paser',
                'kategori_id'  => $katPemberd->id,
                'thumbnail'    => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=900&q=80',
                'excerpt'      => 'Sistem irigasi modern yang dibangun program PPM Bayan berhasil mengairi 1.200 hektar lahan pertanian di Kabupaten Paser, meningkatkan produksi padi hingga 180%.',
                'isi'          => '<p>Lahan sawah yang dulunya hanya bisa panen setahun sekali, kini bisa panen tiga kali setahun berkat jaringan irigasi modern PPM Bayan di Kabupaten Paser.</p><ul><li>Produksi padi naik dari 2,8 ton/ha menjadi 7,8 ton/ha</li><li>Pendapatan petani naik dari Rp 15 juta menjadi Rp 42 juta/tahun</li><li>380 keluarga tani menjadi penerima manfaat</li></ul>',
                'published_at' => now()->subDays(50),
            ],
        ];
        foreach ($articles as $a) Berita::create($a);

        // ===== REGULASI =====
        $regulasiData = [
            ['judul' => 'UU No. 3 Tahun 2020',           'nomor_regulasi' => 'Perubahan atas UU Minerba No. 4/2009 tentang pertambangan mineral dan batubara.',              'link_dokumen' => '#'],
            ['judul' => 'UU No. 6 Tahun 2023',           'nomor_regulasi' => 'Undang-Undang Cipta Kerja terkait penyederhanaan perizinan berusaha berbasis risiko (OSS).',  'link_dokumen' => '#'],
            ['judul' => 'PP No. 96 Tahun 2021',          'nomor_regulasi' => 'Pelaksanaan kegiatan usaha pertambangan minerba serta perpanjangan PKP2B menjadi IUPK.',      'link_dokumen' => '#'],
            ['judul' => 'Permen ESDM No. 26 Tahun 2018', 'nomor_regulasi' => 'Penerapan Good Mining Practice (Kaidah Teknik Pertambangan yang Baik) secara berkelanjutan.', 'link_dokumen' => '#'],
            ['judul' => 'Kepmen ESDM No. 1824 K/2018',   'nomor_regulasi' => 'Pedoman Pelaksanaan Pengembangan dan Pemberdayaan Masyarakat (PPM) "Bayan Peduli".',          'link_dokumen' => '#'],
            ['judul' => 'Permen ESDM No. 10 Tahun 2023', 'nomor_regulasi' => 'Tata cara penyusunan, penyampaian, dan persetujuan Rencana Kerja dan Anggaran Biaya (RKAB).', 'link_dokumen' => '#'],
        ];
        foreach ($regulasiData as $r) Regulasi::create($r);

        // ===== PENGUMUMAN =====
        $pengumumans = [
            ['judul' => 'Pendaftaran Beasiswa PPM Bayan 2026 Telah Dibuka',        'isi' => 'Program beasiswa PPM Bayan 2026 resmi dibuka untuk pelajar SMP, SMA, dan mahasiswa D3/S1 dari kawasan operasional Tabang, Kutai Barat, dan Paser. Kuota tersedia untuk 150 penerima. Pendaftaran online melalui portal PPM mulai 1 April hingga 30 April 2026.',             'prioritas' => 'penting', 'is_active' => true, 'published_at' => now()->subDays(1),  'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1400&q=85'],
            ['judul' => 'Jadwal Klinik Keliling PPM – April 2026',                 'isi' => 'Tim medis PPM Bayan akan mengunjungi 12 desa di Kutai Barat pada 5-25 April 2026. Layanan meliputi pemeriksaan umum, imunisasi, dan konseling gizi. Tidak dipungut biaya apapun.',                                                                                                'prioritas' => 'info',    'is_active' => true, 'published_at' => now()->subDays(3),  'image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=1400&q=85'],
            ['judul' => 'Rekrutmen Fasilitator Program Pertanian PPM 2026',        'isi' => 'PPM Bayan membuka rekrutmen 20 fasilitator pertanian untuk mendampingi kelompok tani di Paser dan Kutai Timur. Persyaratan: lulusan S1 Pertanian/Kehutanan, bersedia ditempatkan di lapangan. Deadline: 15 April 2026.',                                                           'prioritas' => 'info',    'is_active' => true, 'published_at' => now()->subDays(5),  'image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=1400&q=85'],
            ['judul' => 'Laporan Realisasi PPM Kuartal I 2026 Telah Diterbitkan',  'isi' => 'Laporan realisasi program PPM Kuartal I 2026 mencakup realisasi anggaran sebesar Rp 23,4 miliar dari target Rp 85 miliar. Laporan dapat diunduh di menu Laporan portal ini.',                                                                                                    'prioritas' => 'umum',    'is_active' => true, 'published_at' => now()->subDays(7),  'image' => 'https://images.unsplash.com/photo-1542601906897-ecd46e2e9b8d?w=1400&q=85'],
            ['judul' => 'Forum Musyawarah PPM Kabupaten Paser – 20 April 2026',    'isi' => 'PPM Bayan mengundang seluruh kepala desa dan perwakilan masyarakat di Kabupaten Paser untuk hadir dalam Forum Musyawarah PPM 2026 yang akan diselenggarakan pada 20 April 2026 di Aula Pendopo Kabupaten Paser.',                                                                 'prioritas' => 'penting', 'is_active' => true, 'published_at' => now()->subDays(10), 'image' => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=1400&q=85'],
        ];
        foreach ($pengumumans as $p) Pengumuman::create($p);

        // ===== 8 BIDANG PPM =====
        $bidangPendidikan = BidangCsr::create(['nama' => 'Pendidikan',                   'icon' => 'school',            'deskripsi' => 'Beasiswa penuh, pelatihan guru, dan pembangunan fasilitas pendidikan di kawasan operasional.']);
        $bidangKesehatan  = BidangCsr::create(['nama' => 'Kesehatan',                    'icon' => 'health_and_safety', 'deskripsi' => 'Klinik keliling, posyandu, dan distribusi obat gratis untuk masyarakat.']);
        $bidangEkonomi    = BidangCsr::create(['nama' => 'Ekonomi & UMKM',               'icon' => 'storefront',        'deskripsi' => 'Pemberdayaan UMKM, pelatihan kewirausahaan, dan akses permodalan.']);
        $bidangInfra      = BidangCsr::create(['nama' => 'Infrastruktur',                'icon' => 'construction',      'deskripsi' => 'Pembangunan jalan desa, jembatan, dan fasilitas air bersih.']);
        $bidangLingkungan = BidangCsr::create(['nama' => 'Lingkungan Hidup',             'icon' => 'park',              'deskripsi' => 'Reklamasi pascatambang, penghijauan, dan pengelolaan lingkungan berkelanjutan.']);
        $bidangPertanian  = BidangCsr::create(['nama' => 'Pertanian & Ketahanan Pangan', 'icon' => 'agriculture',       'deskripsi' => 'Irigasi, pelatihan petani, dan pengembangan komoditas unggulan lokal.']);
        $bidangBudaya     = BidangCsr::create(['nama' => 'Sosial Budaya & Olahraga',     'icon' => 'groups',            'deskripsi' => 'Pelestarian budaya, fasilitas olahraga, dan kegiatan kemasyarakatan.']);
        $bidangEnergi     = BidangCsr::create(['nama' => 'Energi Mandiri',               'icon' => 'bolt',              'deskripsi' => 'Akses listrik dan energi terbarukan untuk desa-desa terpencil.']);

        // ===== PERUSAHAAN (Entitas Bayan Group) =====
        $ptBayan    = Perusahaan::create(['nama' => 'PT Bayan Resources Tbk',         'bidang_csr_id' => $bidangPendidikan->id]);
        $ptWahana   = Perusahaan::create(['nama' => 'PT Wahana Baratama Mining',      'bidang_csr_id' => $bidangKesehatan->id]);
        $ptGuntur   = Perusahaan::create(['nama' => 'PT Guntur Geni',                 'bidang_csr_id' => $bidangInfra->id]);
        $ptBerau    = Perusahaan::create(['nama' => 'PT Berau Usaha Mulya',           'bidang_csr_id' => $bidangLingkungan->id]);
        $ptTabang   = Perusahaan::create(['nama' => 'PT Bara Tabang',                 'bidang_csr_id' => $bidangEkonomi->id]);

        // ===== PROGRAM CSR — DATA AKTUAL BAYAN GROUP =====
        // Pendidikan: Total Rp 19,7 M (beasiswa Uniba, Unikarta, dst.)
        $pBeasiswa22 = ProgramCsr::create(['nama' => 'Beasiswa Bayan Peduli 2022',       'bidang_id' => $bidangPendidikan->id, 'perusahaan_id' => $ptBayan->id,  'anggaran' => 6200000000,  'lokasi' => 'Kutai Kartanegara & Kutai Timur', 'tahun' => 2022]);
        $pBeasiswa23 = ProgramCsr::create(['nama' => 'Beasiswa Bayan Peduli 2023',       'bidang_id' => $bidangPendidikan->id, 'perusahaan_id' => $ptBayan->id,  'anggaran' => 6800000000,  'lokasi' => 'Kutai Kartanegara & Kutai Timur', 'tahun' => 2023]);
        $pBeasiswa24 = ProgramCsr::create(['nama' => 'Beasiswa Bayan Peduli 2024-2025',  'bidang_id' => $bidangPendidikan->id, 'perusahaan_id' => $ptBayan->id,  'anggaran' => 6700000000,  'lokasi' => 'Uniba, Unikarta, Politeknik',     'tahun' => 2024]);

        // Infrastruktur: Rp 16,6 M melalui Pemkab Kutai Kartanegara
        $pInfra22 = ProgramCsr::create(['nama' => 'Kontribusi Infrastruktur Pemkab Kukar 2022', 'bidang_id' => $bidangInfra->id, 'perusahaan_id' => $ptBayan->id, 'anggaran' => 5400000000,  'lokasi' => 'Kutai Kartanegara', 'tahun' => 2022]);
        $pInfra23 = ProgramCsr::create(['nama' => 'Kontribusi Infrastruktur Pemkab Kukar 2023', 'bidang_id' => $bidangInfra->id, 'perusahaan_id' => $ptBayan->id, 'anggaran' => 5700000000,  'lokasi' => 'Kutai Kartanegara', 'tahun' => 2023]);
        $pInfra24 = ProgramCsr::create(['nama' => 'Kontribusi Infrastruktur Pemkab Kukar 2024', 'bidang_id' => $bidangInfra->id, 'perusahaan_id' => $ptBayan->id, 'anggaran' => 5500000000,  'lokasi' => 'Kutai Kartanegara', 'tahun' => 2024]);

        // PT Bara Tabang: Rp 21,9 M (2022)
        $pTabang22 = ProgramCsr::create(['nama' => 'PPM PT Bara Tabang 2022',             'bidang_id' => $bidangEkonomi->id,    'perusahaan_id' => $ptTabang->id, 'anggaran' => 21900000000, 'lokasi' => 'Tabang, Kutai Kartanegara', 'tahun' => 2022]);
        $pTabang23 = ProgramCsr::create(['nama' => 'PPM PT Bara Tabang 2023',             'bidang_id' => $bidangEkonomi->id,    'perusahaan_id' => $ptTabang->id, 'anggaran' => 19500000000, 'lokasi' => 'Tabang, Kutai Kartanegara', 'tahun' => 2023]);
        $pTabang24 = ProgramCsr::create(['nama' => 'PPM PT Bara Tabang 2024',             'bidang_id' => $bidangEkonomi->id,    'perusahaan_id' => $ptTabang->id, 'anggaran' => 18200000000, 'lokasi' => 'Tabang, Kutai Kartanegara', 'tahun' => 2024]);

        // Kesehatan — Klinik Keliling
        $pKlinik22 = ProgramCsr::create(['nama' => 'Klinik Keliling & Posyandu 2022',     'bidang_id' => $bidangKesehatan->id,  'perusahaan_id' => $ptWahana->id, 'anggaran' => 4800000000,  'lokasi' => 'Kutai Barat & Kutai Timur', 'tahun' => 2022]);
        $pKlinik23 = ProgramCsr::create(['nama' => 'Klinik Keliling & Posyandu 2023',     'bidang_id' => $bidangKesehatan->id,  'perusahaan_id' => $ptWahana->id, 'anggaran' => 5200000000,  'lokasi' => 'Kutai Barat & Kutai Timur', 'tahun' => 2023]);
        $pKlinik24 = ProgramCsr::create(['nama' => 'Klinik Keliling & Posyandu 2024',     'bidang_id' => $bidangKesehatan->id,  'perusahaan_id' => $ptWahana->id, 'anggaran' => 5700000000,  'lokasi' => 'Kutai Barat & Kutai Timur', 'tahun' => 2024]);

        // Bantuan Kemanusiaan: Rp 1,8 M
        $pBencana = ProgramCsr::create(['nama' => 'Bantuan Bencana Nasional',              'bidang_id' => $bidangBudaya->id,     'perusahaan_id' => $ptBayan->id,  'anggaran' => 1800000000,  'lokasi' => 'Nasional (Kalimantan & Sulawesi)', 'tahun' => 2023]);

        // Lingkungan & Reklamasi
        $pReklam22 = ProgramCsr::create(['nama' => 'Reklamasi & Revegetasi Pascatambang 2022', 'bidang_id' => $bidangLingkungan->id, 'perusahaan_id' => $ptBerau->id,  'anggaran' => 7800000000,  'lokasi' => 'Tabang & Paser', 'tahun' => 2022]);
        $pReklam23 = ProgramCsr::create(['nama' => 'Reklamasi & Revegetasi Pascatambang 2023', 'bidang_id' => $bidangLingkungan->id, 'perusahaan_id' => $ptBerau->id,  'anggaran' => 8400000000,  'lokasi' => 'Tabang & Paser', 'tahun' => 2023]);
        $pReklam24 = ProgramCsr::create(['nama' => 'Reklamasi & Revegetasi Pascatambang 2024', 'bidang_id' => $bidangLingkungan->id, 'perusahaan_id' => $ptBerau->id,  'anggaran' => 9100000000,  'lokasi' => 'Tabang & Paser', 'tahun' => 2024]);

        // Pertanian & Ekonomi Lokal
        $pTani22 = ProgramCsr::create(['nama' => 'Pertanian & Ketahanan Pangan 2022',     'bidang_id' => $bidangPertanian->id,  'perusahaan_id' => $ptGuntur->id, 'anggaran' => 3500000000,  'lokasi' => 'Kabupaten Paser', 'tahun' => 2022]);
        $pTani23 = ProgramCsr::create(['nama' => 'Pertanian & Ketahanan Pangan 2023',     'bidang_id' => $bidangPertanian->id,  'perusahaan_id' => $ptGuntur->id, 'anggaran' => 4200000000,  'lokasi' => 'Kabupaten Paser', 'tahun' => 2023]);
        $pTani24 = ProgramCsr::create(['nama' => 'Pertanian & Ketahanan Pangan 2024',     'bidang_id' => $bidangPertanian->id,  'perusahaan_id' => $ptGuntur->id, 'anggaran' => 4800000000,  'lokasi' => 'Kabupaten Paser', 'tahun' => 2024]);

        // ===== LAPORAN CSR — REALISASI AKTUAL =====
        LaporanCsr::create(['bidang_id' => $bidangPendidikan->id,  'program_id' => $pBeasiswa22->id, 'perusahaan_id' => $ptBayan->id,  'lokasi' => 'Kutai Kartanegara & Kutai Timur', 'nominal' => 6200000000,  'tahun' => 2022]);
        LaporanCsr::create(['bidang_id' => $bidangPendidikan->id,  'program_id' => $pBeasiswa23->id, 'perusahaan_id' => $ptBayan->id,  'lokasi' => 'Kutai Kartanegara & Kutai Timur', 'nominal' => 6800000000,  'tahun' => 2023]);
        LaporanCsr::create(['bidang_id' => $bidangPendidikan->id,  'program_id' => $pBeasiswa24->id, 'perusahaan_id' => $ptBayan->id,  'lokasi' => 'Uniba, Unikarta, Politeknik',     'nominal' => 6700000000,  'tahun' => 2024]);
        LaporanCsr::create(['bidang_id' => $bidangInfra->id,       'program_id' => $pInfra22->id,    'perusahaan_id' => $ptBayan->id,  'lokasi' => 'Kutai Kartanegara',              'nominal' => 5400000000,  'tahun' => 2022]);
        LaporanCsr::create(['bidang_id' => $bidangInfra->id,       'program_id' => $pInfra23->id,    'perusahaan_id' => $ptBayan->id,  'lokasi' => 'Kutai Kartanegara',              'nominal' => 5700000000,  'tahun' => 2023]);
        LaporanCsr::create(['bidang_id' => $bidangInfra->id,       'program_id' => $pInfra24->id,    'perusahaan_id' => $ptBayan->id,  'lokasi' => 'Kutai Kartanegara',              'nominal' => 5500000000,  'tahun' => 2024]);
        LaporanCsr::create(['bidang_id' => $bidangEkonomi->id,     'program_id' => $pTabang22->id,   'perusahaan_id' => $ptTabang->id, 'lokasi' => 'Tabang, Kutai Kartanegara',      'nominal' => 21900000000, 'tahun' => 2022]);
        LaporanCsr::create(['bidang_id' => $bidangEkonomi->id,     'program_id' => $pTabang23->id,   'perusahaan_id' => $ptTabang->id, 'lokasi' => 'Tabang, Kutai Kartanegara',      'nominal' => 19500000000, 'tahun' => 2023]);
        LaporanCsr::create(['bidang_id' => $bidangEkonomi->id,     'program_id' => $pTabang24->id,   'perusahaan_id' => $ptTabang->id, 'lokasi' => 'Tabang, Kutai Kartanegara',      'nominal' => 18200000000, 'tahun' => 2024]);
        LaporanCsr::create(['bidang_id' => $bidangKesehatan->id,   'program_id' => $pKlinik22->id,   'perusahaan_id' => $ptWahana->id, 'lokasi' => 'Kutai Barat & Kutai Timur',      'nominal' => 4800000000,  'tahun' => 2022]);
        LaporanCsr::create(['bidang_id' => $bidangKesehatan->id,   'program_id' => $pKlinik23->id,   'perusahaan_id' => $ptWahana->id, 'lokasi' => 'Kutai Barat & Kutai Timur',      'nominal' => 5200000000,  'tahun' => 2023]);
        LaporanCsr::create(['bidang_id' => $bidangKesehatan->id,   'program_id' => $pKlinik24->id,   'perusahaan_id' => $ptWahana->id, 'lokasi' => 'Kutai Barat & Kutai Timur',      'nominal' => 5700000000,  'tahun' => 2024]);
        LaporanCsr::create(['bidang_id' => $bidangBudaya->id,      'program_id' => $pBencana->id,    'perusahaan_id' => $ptBayan->id,  'lokasi' => 'Nasional (Kalimantan & Sulawesi)','nominal' => 1800000000,  'tahun' => 2023]);
        LaporanCsr::create(['bidang_id' => $bidangLingkungan->id,  'program_id' => $pReklam22->id,   'perusahaan_id' => $ptBerau->id,  'lokasi' => 'Tabang & Paser',                 'nominal' => 7800000000,  'tahun' => 2022]);
        LaporanCsr::create(['bidang_id' => $bidangLingkungan->id,  'program_id' => $pReklam23->id,   'perusahaan_id' => $ptBerau->id,  'lokasi' => 'Tabang & Paser',                 'nominal' => 8400000000,  'tahun' => 2023]);
        LaporanCsr::create(['bidang_id' => $bidangLingkungan->id,  'program_id' => $pReklam24->id,   'perusahaan_id' => $ptBerau->id,  'lokasi' => 'Tabang & Paser',                 'nominal' => 9100000000,  'tahun' => 2024]);
        LaporanCsr::create(['bidang_id' => $bidangPertanian->id,   'program_id' => $pTani22->id,     'perusahaan_id' => $ptGuntur->id, 'lokasi' => 'Kabupaten Paser',                'nominal' => 3500000000,  'tahun' => 2022]);
        LaporanCsr::create(['bidang_id' => $bidangPertanian->id,   'program_id' => $pTani23->id,     'perusahaan_id' => $ptGuntur->id, 'lokasi' => 'Kabupaten Paser',                'nominal' => 4200000000,  'tahun' => 2023]);
        LaporanCsr::create(['bidang_id' => $bidangPertanian->id,   'program_id' => $pTani24->id,     'perusahaan_id' => $ptGuntur->id, 'lokasi' => 'Kabupaten Paser',                'nominal' => 4800000000,  'tahun' => 2024]);
    }
}
