# Alur Sistem Monitoring PPM (Pengembangan dan Pemberdayaan Masyarakat) – PT Bayan Resources

> **Catatan untuk AI pembuat laporan**
> - Pembaca laporan: **pimpinan**. Laporan dipakai untuk **presentasi alur sistem**.
> - Gunakan bahasa Indonesia yang sederhana, hindari istilah teknis (jangan sebut database, controller, route, dsb.).
> - Output: file **.docx** + **screenshot aplikasi** sesuai daftar di bagian 8.
> - Tiap bagian cukup 1 halaman: penjelasan singkat, diagram/poin, lalu screenshot.
> - Jangan cantumkan email/password login di dalam laporan.

---

## 1. Ringkasan Singkat

Sistem ini adalah aplikasi untuk **merencanakan, menyetujui, dan memantau** program PPM secara terpusat, mulai dari penyusunan anggaran program sampai bukti pelaksanaan di lapangan.

Alur utamanya hanya **4 langkah**:

```
1. RENCANA        2. PENGAJUAN        3. PERSETUJUAN       4. REALISASI & PANTAU
Admin menyiapkan → Divisi mengajukan → Admin menyetujui  → Divisi melapor hasil,
program & anggaran  kegiatan            atau minta revisi    pimpinan memantau
```

---

## 2. Siapa Saja Penggunanya

| Pengguna | Peran |
|---|---|
| **Admin (CDO/CPO)** | Menyiapkan program & anggaran, memeriksa dan menyetujui kegiatan, mengatur sistem |
| **Divisi** | Mengajukan kegiatan dan melaporkan realisasi (hasil + biaya + bukti) |
| **Pimpinan** | Melihat rekap (Resume) dan memberi arahan/komentar pada kegiatan |
| **Masyarakat/Publik** | Melihat informasi PPM, berita, dan dashboard publik tanpa login |

**Kelebihan:** setiap orang hanya melihat menu yang sesuai tugasnya, sehingga tampilan sederhana dan data lebih aman.

---

## 3. Susunan Program

Semua kegiatan tersusun berjenjang sehingga mudah ditelusuri:

```
Pilar (contoh: Pendidikan, Kesehatan, Ekonomi, Lingkungan)
  └─ Program  → memiliki anggaran
       └─ Kegiatan  → diajukan oleh divisi
            └─ Lokasi  → target, satuan, dan rencana biaya per lokasi
                 └─ Realisasi  → hasil, biaya, dan bukti di lapangan
```

**Kelebihan:** pimpinan dapat melihat penyerapan anggaran dari tingkat pilar sampai ke lokasi desa.

---

## 4. Alur Pengajuan & Persetujuan Kegiatan

```
Divisi mengisi kegiatan
        │
        ▼
   [ DIAJUKAN ] ──────────────► Admin memeriksa
                                   │
            ┌──────────────────────┼──────────────────────┐
            ▼                      ▼                      ▼
     [ DISETUJUI ]           [ REVISI ]              [ DITOLAK ]
   boleh dilaksanakan     ada catatan perbaikan    ada catatan alasan
                                   │                      │
                                   └──── Divisi perbaiki ─┘
                                              │
                                              ▼
                                     [ DIAJUKAN ULANG ] → kembali ke Admin
```

Aturan sederhana:
- Total biaya kegiatan **tidak bisa melebihi anggaran program** (sistem menolak otomatis).
- Kegiatan yang sudah diajukan/disetujui **tidak bisa diubah sembarangan**.
- Admin dapat **mengunci masa perencanaan**, sehingga setelah batas waktu divisi tidak bisa menambah/mengubah kegiatan.

**Kelebihan:**
- Anggaran terkendali sejak awal, tidak ada pengajuan yang melebihi pagu.
- Setiap revisi/penolakan disertai catatan, sehingga perbaikan jelas.
- Proses persetujuan tidak lagi lewat kertas atau pesan terpisah.

---

## 5. Alur Pelaporan Realisasi

1. Hanya kegiatan yang **sudah disetujui** yang bisa dilaporkan.
2. Divisi mengisi per lokasi: **tanggal, hasil (output), biaya**, dan keterangan.
3. Divisi melampirkan **foto/dokumen bukti** dan **laporan** (PDF/JPG/PNG).
4. Sistem menghitung **progres** dan **sisa anggaran** secara otomatis.
5. Total biaya realisasi **tidak bisa melebihi rencana biaya lokasi**.

**Kelebihan:**
- Setiap rupiah yang dilaporkan memiliki bukti pendukung.
- Progres kegiatan langsung terlihat tanpa menunggu rekap manual.
- Mencegah realisasi melebihi anggaran.

---

## 6. Pemantauan oleh Pimpinan

| Fitur | Manfaat bagi Pimpinan |
|---|---|
| **Dashboard** | Gambaran cepat: total anggaran, realisasi, sisa, dan grafik |
| **Resume Pilar / Program / Divisi / User** | Rekap penyerapan anggaran dari berbagai sudut, bisa difilter tahun & bulan |
| **Export Excel & PDF** | Rekap siap dicetak atau dibawa ke rapat |
| **Detail Kegiatan + Bukti** | Bisa membuka foto/dokumen bukti langsung dari Resume |
| **Komentar Pimpinan** | Memberi arahan pada kegiatan; divisi & admin langsung menerima notifikasi |
| **Riwayat Aktivitas** | Semua tindakan (pengajuan, persetujuan, perubahan) tercatat siapa & kapan |

**Kelebihan:** pimpinan bisa memantau kapan saja secara langsung (*real-time*), tanpa menunggu laporan bulanan.

---

## 7. Ringkasan Kelebihan Sistem

1. **Terpusat** – seluruh data PPM ada di satu tempat.
2. **Anggaran terkendali** – sistem otomatis menolak pengajuan/realisasi yang melebihi anggaran.
3. **Transparan** – setiap tindakan tercatat, setiap biaya ada buktinya.
4. **Cepat** – persetujuan dan pelaporan dilakukan online, tidak perlu berkas fisik.
5. **Mudah dipantau** – rekap otomatis per pilar, program, divisi, dan bisa diekspor.
6. **Ada komunikasi langsung** – pimpinan dapat memberi komentar dan arahan di dalam sistem.
7. **Informasi untuk publik** – masyarakat dapat melihat informasi dan capaian PPM.

---

## 8. Panduan Screenshot (untuk AI)

Alamat aplikasi: `http://localhost` (sesuaikan bila berbeda). Login di `/login`.
Gunakan akun demo sesuai peran (akun tersedia di seeder aplikasi; **jangan dicantumkan di laporan**).

| No | Halaman | Alamat | Login sebagai | Dipakai di bagian |
|---|---|---|---|---|
| 1 | Halaman depan (publik) | `/` | tanpa login | 2, 7 |
| 2 | Halaman login | `/login` | tanpa login | 2 |
| 3 | Dashboard | `/dashboard` | Admin | 6 |
| 4 | Program (anggaran) | `/program` | Admin | 3 |
| 5 | Perencanaan Kegiatan | `/perencanaan` | Divisi | 4 |
| 6 | Form tambah kegiatan (klik tombol tambah) | `/perencanaan` | Divisi | 4 |
| 7 | Daftar kegiatan & status persetujuan | `/kegiatan` | Admin | 4 |
| 8 | Realisasi Kegiatan | `/realisasi` | Divisi | 5 |
| 9 | Resume Pilar | `/resume/pilar` | Pimpinan/Admin | 6 |
| 10 | Resume Program / Divisi | `/resume/program`, `/resume/divisi` | Pimpinan/Admin | 6 |
| 11 | Detail kegiatan + komentar pimpinan | `/kegiatan/{id}` (klik dari Resume) | Pimpinan | 6 |
| 12 | Pengaturan (kunci perencanaan) | `/settings` | Admin | 4 |
| 13 | Activity Log | `/activity-log` | Admin | 6 |
| 14 | Dashboard Publik | `/dashboard-publik` | tanpa login | 7 |

Tips screenshot:
- Gunakan layar lebar (±1440 px) dan pastikan ada data contoh agar tabel/grafik tidak kosong.
- Beri judul singkat di bawah setiap gambar, contoh: *"Gambar 4 – Pimpinan melihat rekap per pilar"*.

---

## 9. Usulan Susunan Slide/Laporan

1. Judul & tujuan sistem
2. Siapa penggunanya (bagian 2)
3. Susunan program (bagian 3)
4. Alur pengajuan & persetujuan (bagian 4)
5. Alur pelaporan realisasi (bagian 5)
6. Pemantauan oleh pimpinan (bagian 6)
7. Kelebihan sistem (bagian 7)
8. Penutup
