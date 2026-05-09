# Panduan Instalasi & Penggunaan Menu Management Dinamis

Dokumen ini berisi panduan singkat tentang cara menginstal dan mengelola fitur **Menu Management** dinamis yang telah terintegrasi di dalam aplikasi TALL Stack ini.

---

## 1. Instalasi (Database Setup)

Karena fitur ini bergantung pada struktur *database* baru (tabel `menus` dan pivot tabel `menu_user`), Anda harus menjalankan proses *migration* dan *seeding* terlebih dahulu sebelum fitur ini bisa berfungsi.

Buka terminal di dalam folder proyek Anda, lalu jalankan perintah berikut secara berurutan:

### A. Jika Anda baru pertama kali menginstall fitur ini:
Jalankan *migration* biasa untuk membuat tabelnya, lalu *seeding* untuk memuat data awal (Eskalink, Call Plan, Master Data, dll).

```bash
php artisan migrate
php artisan db:seed --class=MenuSeeder
```

### B. Jika Anda sebelumnya mengalami error / ingin mereset ulang data menu:
Gunakan perintah `migrate:refresh` khusus untuk tabel menu ini agar tabel dihapus dan dibuat ulang dalam kondisi bersih, lalu lakukan seeding kembali:

```bash
php artisan migrate:refresh --path=database/migrations/2026_05_09_073000_create_menus_and_menu_user_tables.php
php artisan db:seed --class=MenuSeeder
```

---

## 2. Cara Penggunaan (Sebagai Admin)

Setelah instalasi selesai, seluruh sistem *Sidebar* Anda secara otomatis menjadi **Dinamis**. Artinya, *user* biasa tidak akan melihat menu apa-apa sampai Anda (sebagai Admin) memberikannya akses.

Berikut adalah 2 fitur utama yang bisa Anda gunakan:

### A. Manajemen Master Menu (CRUD Menu)
Ini adalah halaman khusus tempat Anda bisa membuat menu navigasi baru, mengatur URL, mengganti *icon*, atau mengubah hierarki (*parent-child*).
- **Akses:** Klik tulisan `Settings` di pojok kanan atas Navbar, lalu klik dropdown **Menus**.
- **Fungsi Utama:** Tambah Menu, Edit URL, Ganti Ikon SVG, dan Tentukan Letak Menu.

### B. Penugasan Akses Menu per User (Assigning)
Setelah menu induknya dibuat, Anda harus menentukan pengguna (user) mana saja yang berhak melihat dan mengakses menu tersebut.
- **Akses:** Klik tulisan `Settings` di pojok kanan atas Navbar, lalu klik **Users**.
- **Fungsi Utama:** Di dalam tabel daftar *User*, perhatikan ada tombol baru berbentuk kunci bernama **"Akses Menu"**.
- Klik tombol tersebut, lalu centang kotak (*checkbox*) pada daftar menu yang Anda inginkan.
- Klik **Simpan Akses**. Saat itu juga, menu di sebelah kiri (*sidebar*) user tersebut akan otomatis berubah sesuai centangan Anda!

---

## 3. Keamanan Tambahan (Middleware)

Selain disembunyikan dari *Sidebar*, halaman juga dikunci di level "sistem" (Backend). Jika ada *user* nakal yang mencoba menebak dan mengetikkan URL menu secara manual di *browser*, sistem akan tetap memblokirnya (Muncul peringatan: **403 Forbidden**).

**Catatan Untuk Developer:**
Jika Anda menambahkan menu dan rute baru di `routes/web.php`, pastikan untuk menambahkan tulisan `->middleware('menu.access')` pada rute tersebut agar terlindungi secara otomatis.

Contoh penggunaannya di file `routes/web.php`:
```php
// Rute yang terlindungi (Hanya user yang dicentang akses menunya yang bisa masuk)
Route::get('/master-regions', MasterRegionsIndex::class)
    ->name('master-regions.index')
    ->middleware('menu.access'); 
```
