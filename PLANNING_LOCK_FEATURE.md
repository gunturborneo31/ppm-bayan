# Fitur Pengaturan Kunci Perencanaan

## Ringkasan
Sistem pengaturan superadmin untuk mengunci/membuka akses input perencanaan (kegiatan) bagi user non-superadmin.

## Fitur Utama
✅ Toggle lock/unlock perencanaan dari panel superadmin  
✅ Kontrol akses berbasis role (superadmin vs pengguna divisi)  
✅ Pencatatan audit lengkap untuk setiap perubahan setting  
✅ Tampilan status visual yang jelas  
✅ Pesan error informatif untuk user ketika perencanaan dikunci  

## File-File yang Ditambahkan

### 1. Model: `app/Models/Setting.php`
Models untuk mengelola pengaturan sistem. Menyimpan/mengambil setting dengan structure JSON.

**Methods Penting:**
```php
Setting::isPlanningLocked()        // Cek apakah perencanaan dikunci
Setting::lockPlanning(true/false)  // Kunci/buka perencanaan
Setting::getValue($key, $default)  // Ambil nilai setting
Setting::setValue($key, $value)    // Set nilai setting
```

### 2. Migration: `database/migrations/2026_04_05_000010_create_settings_table.php`
Membuat tabel `settings` untuk menyimpan pengaturan sistem.

**Schema:**
- `id` - Primary key
- `key` - Unique identifier (e.g., 'planning_locked')
- `value` - JSON data
- `label` - Label display
- `description` - Deskripsi
- `timestamps`

### 3. Controller: `app/Http/Controllers/SettingsController.php`
Mengelola logika pengaturan.

**Routes Available:**
- `GET /settings` → Tampilkan halaman pengaturan
- `PUT /settings/{id}` → Update setting
- `POST /settings/toggle-planning-lock` → Toggle lock perencanaan

### 4. Component: `resources/js/Pages/Settings/Index.vue`
Halaman UI untuk superadmin mengelola pengaturan.

**Features:**
- Tombol toggle dengan status visual
- Indikator lock/unlock jelas
- Kartu status informatif
- Catatan untuk admin

### 5. Seeder: `database/seeders/SettingsSeeder.php`
Inisialisasi setting default.

## File-File yang Dimodifikasi

### 1. `routes/web.php`
Menambahkan import dan 3 route untuk Settings di middleware superadmin.

### 2. `app/Http/Controllers/KegiatanController.php`
- Import Setting model
- Tambah check di `store()` method - cegah input jika dikunci
- Tambah check di `update()` method - cegah edit jika dikunci
- Pass `planningLocked` status ke view perencanaan

### 3. `resources/js/Layouts/AppLayout.vue`
Menambahkan menu "Pengaturan" di sidebar (hanya untuk superadmin).

## Cara Kerja

### Ketika Perencanaan DIKUNCI:
1. User biasa (divisi) tidak dapat membuat kegiatan baru
2. User biasa tidak dapat mengedit kegiatan
3. User biasa melihat pesan error yang informatif
4. **Superadmin TETAP bisa input/edit** (tidak terpengaruh)

### Ketika Perencanaan TERBUKA:
1. Semua user dapat membuat dan mengedit kegiatan (sesuai aturan eksisting)
2. Tidak ada pembatasan tambahan

### Audit Trail:
Setiap perubahan status lock dicatat di tabel `activity_logs`:
- Kapan dikunci/dibuka
- Siapa yang merubah
- Deskripsi aksi

## Instalasi dan Setup

### 1. Jalankan Migration
```bash
php artisan migrate
```

### 2. Seed Pengaturan Awal (Optional)
```bash
php artisan db:seed --class=SettingsSeeder
```

Atau jika sudah punya DatabaseSeeder yang include SettingsSeeder:
```bash
php artisan db:seed
```

### 3. Akses Halaman Pengaturan
- Login sebagai superadmin
- Di sidebar → Sistem → Pengaturan (atau `/settings`)
- Klik tombol "Kunci" atau "Buka Kunci"

## Pengujian

### Test 1: Login sebagai Superadmin
```
1. Kunci perencanaan dari halaman /settings
2. Buat kegiatan baru - HARUS BERHASIL (superadmin tidak terpengaruh)
3. Edit kegiatan - HARUS BERHASIL
```

### Test 2: Login sebagai User Divisi
```
1. Ketika perencanaan dikunci:
   - Coba buat kegiatan baru - HARUS ERROR ("Perencanaan sedang dikunci")
   - Coba edit kegiatan - HARUS ERROR
   - Cek activity log - HARUS ADA RECORD

2. Ketika perencanaan dibuka:
   - Coba buat kegiatan baru - HARUS BERHASIL
   - Edit kegiatan - HARUS BERHASIL (sesuai aturan status)
```

### Test 3: Activity Log
```
1. Kunci perencanaan
2. Buka activity log
3. Cari record dengan:
   - Module: 'settings'
   - Action: 'lock' atau 'unlock'
   - User ID, tanggal, deskripsi
```

## Pesan Error yang Ditampilkan

**Ketika Perencanaan Dikunci:**
```
"Pengaturan perencanaan sedang dikunci. Hubungi administrator untuk informasi lebih lanjut."
```

Pesan ini muncul di field 'status' pada form input kegiatan.

## Struktur Database

```sql
-- Tabel settings
CREATE TABLE settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    key VARCHAR(255) UNIQUE NOT NULL,
    value JSON,
    label VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Contoh data
INSERT INTO settings VALUES 
(1, 'planning_locked', false, 'Kunci Perencanaan', 'Mengunci input perencanaan untuk semua user non-superadmin');
```

## Ekspansi Masa Depan

Sistem ini didesain untuk mudah ditambah dengan setting lainnya:

1. **Kunci Realisasi** - Lock input realisasi
2. **Deadline Perencanaan** - Auto-lock setelah tanggal tertentu
3. **Lock Message** - Pesan custom yang ditampilkan ke user
4. **Scheduled Unlock** - Auto-unlock pada jadwal tertentu
5. **Permission-based Locks** - Lock per divisi/program

Cukup:
1. Tambah method di Controller
2. Tambah route
3. Tambah UI component
4. Tambah logic check di controller yang relevan

## Support

Jika ada pertanyaan atau error:
1. Cek activity_logs untuk history perubahan
2. Cek console browser untuk error Javascript
3. Cek network tab untuk response dari server
4. Cek database settings table dengan query: `SELECT * FROM settings;`

---
**Dibuat: 2026-04-05**  
**Status: Production Ready** ✅
