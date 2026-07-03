# Handover Progres: Modul Master Customer RWO

Dokumen ini berisi rangkuman dari semua hal yang telah kita kerjakan untuk mempermudah Anda melanjutkan pengembangan di PC lain.

## 📱 Ringkasan Proyek
- **Aplikasi**: Mobile-first Web App
- **Tech Stack**: Laravel, Inertia.js, React, Tailwind CSS, Heroicons
- **Fokus Saat Ini**: Membangun UI/UX Frontend untuk modul **Reward Outlet (RWO)** yang responsif (Mobile & Tablet).

---

## ✅ Apa Saja yang Sudah Diselesaikan?

### 1. Sistem Layout Mobile & Tablet
- **`MobileLayout.tsx`**: Layout utama yang membatasi lebar layar maksimal (`max-w-2xl`) agar terlihat proporsional di Tablet/Desktop.
- **Navbar & Bottom Menu**: Dibuat responsif dan melayang (*fixed*). Bottom Menu disesuaikan menggunakan ikon yang tepat (Home, Explore, Call Plan, Profile).
- **Animasi**: Menambahkan animasi transisi (*fade-in*, *slide-up*) untuk interaksi yang lebih mulus layaknya aplikasi *native*.

### 2. Halaman Login
- Halaman login bergaya modern dengan tata letak vertikal di mobile dan form yang *centered* pada layar tablet.

### 3. Halaman Master Customer (`MasterCustomer/Index.tsx`)
Halaman ini sudah didesain 100% menggunakan data *dummy* dan mencakup fitur-fitur UI yang sangat kompleks:
- **Card Outlet (Toko)**: Menampilkan nama toko, alamat, *badge* status kelengkapan (Complete/Not Complete), *badge* verifikasi, dan jarak (KM). Terdapat indikator kelengkapan foto (Tampak Depan, Tampak Dalam).
- **Header Pencarian (Search & Filter)**: 
  - Terletak tepat di bawah Navbar dan di-*setting* menggunakan posisi `fixed` (menggunakan *React Portal*).
  - Merentang penuh (*full width*) dengan tampilan *background* putih sehingga menyatu dengan Navbar.
  - Sudah diberikan spasi matematis (`h-12 md:h-8`) agar kartu di bawahnya tidak terpotong.
- **Bottom Sheets (Panel Bawah)**: Terdapat 3 panel yang otomatis muncul dari bawah (*slide-up*) saat tombol ditekan. Semua panel dibungkus dengan **React Portal** agar posisinya absolut di layar bawah dan tidak rusak akibat *stacking context* dari animasi CSS.
  - **Panel Detail**: Menampilkan seluruh data Outlet (Informasi Dasar, Pemilik, Rekening Bank, Data Server, dan Lampiran Foto).
  - **Panel Edit**: Form lengkap untuk mengubah data outlet (Identitas Pemilik, Rekening Bank, dll), dilengkapi *dropdown* pintar (*mock*) untuk memilih Nama Bank, serta area khusus untuk unggah foto KTP.
  - **Panel Upload**: Layar khusus berisi slot-slot untuk mengambil dan mengunggah Foto Tampak Depan dan Foto Tampak Dalam.

---

## 🚀 Langkah Selanjutnya (Next Steps)

Sekarang desain UI untuk Frontend sudah sangat matang dan siap. Langkah selanjutnya yang perlu Anda lakukan ketika melanjutkan di PC lain adalah mengintegrasikan sistem **Backend** (Laravel):

1. **Database & Migrations**:
   - Buat tabel (misal: `rwo_customers`) yang mencakup kolom-kolom: `customer_code`, `name`, `alamat`, `status`, `is_valid`, `distance`, `nama_pemilik`, `nama_ktp`, `nik_ktp`, `no_hp`, `nama_bank`, `no_rekening`, `nama_pemilik_norek`, `foto_depan`, `foto_dalam`, `foto_ktp`, `foto_toko`.
2. **Model & Controller**:
   - Buat Model Eloquent.
   - Buat Controller (`MasterCustomerController@index`) untuk menarik data dari tabel dan mem-*passing*-nya ke komponen React menggunakan Inertia (`Inertia::render('MasterCustomer/Index', ['customers' => $data])`).
3. **Data Fetching (Integrasi)**:
   - Ganti *array* `customers` statis (data *dummy*) yang ada di dalam `Index.tsx` dengan _props_ bawaan dari Controller Laravel.
4. **Form Handling**:
   - Integrasikan fungsi submit pada form **Edit** dan **Upload** menggunakan `@inertiajs/react` (`useForm`) untuk mengirim POST/PUT request agar data bisa tersimpan ke dalam database di server.

> **Tips:** Jangan lupa untuk melakukan `git add .`, `git commit`, dan `git push` sekarang, agar Anda tinggal melakukan `git pull` saat berpindah ke PC tujuan. Happy coding! 💻
