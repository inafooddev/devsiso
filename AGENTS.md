# Devsiso Architecture & UI/UX Guidelines

Dokumen ini berisi aturan permanen pengembangan sistem Devsiso yang wajib dipatuhi oleh AI assistant pada setiap tugas pembuatan, refactoring, maupun audit fitur.

---

## 1. Livewire 3 & Alpine.js DOM Morphing Defense

Setiap interaksi di Livewire 3 memicu DOM diffing melalui `Alpine.morph`. Untuk mencegah *listener decoupling*, state yang tertinggal (*stale state*), atau input yang tidak merespons:

### A. Modal Architecture (Anti-Stale DOM)
1. **Wajib Guard `@if($showModal)`**:
   - Semua modal WAJIB dibungkus blok `@if($showModal)`.
   - Jangan pernah membiarkan modal pasif mengendap di DOM menggunakan trik checkbox toggle (`modal-toggle`) atau CSS `hidden`/`display:none`.
   - Modal harus **di-unmount sepenuhnya dari DOM saat ditutup**, dan **di-mount segar saat dibuka** agar Alpine bindings selalu bersih.
2. **Standardisasi Komponen `<x-ui.modal>`**:
   - Selalu gunakan komponen standar `<x-ui.modal id="..." title="..." icon="..." size="..." open="true" wire:close="closeModal">`.
   - Pasang `wire:key` yang stabil pada modal, misalnya: `wire:key="modal-export-{{ $appliedDistributor ?: 'all' }}"`.

### B. Binding Reaktif vs Deferred
1. **Gunakan `wire:model.live` untuk Kontrol Aksi Modal & Filter Kritis**:
   - Input/select yang menentukan parameter eksekusi (seperti Flag Delete, Metode Hapus, Pilihan Salesman, Filter Cabang) WAJIB menggunakan `wire:model.live`.
   - Hindari `wire:model` deferred untuk kontrol modal aksi, karena rentan tidak tersinkronisasi jika aksi berikutnya langsung mengembalikan file download atau respons biner (`Excel::download`, `BinaryFileResponse`).
2. **Pola Pengambilan Parameter Sebelum Download/Close**:
   - Pada method controller Livewire, selalu tangkap nilai parameter ke dalam variabel lokal sebelum menutup modal:
     ```php
     $flag = strtoupper(trim($this->flagDelete)) === 'Y' ? 'Y' : 'N';
     $salesman = $this->selectedSalesman;
     $this->closeModal();
     return Excel::download(new ExportClass($flag, $salesman), $fileName);
     ```

### C. Kestabilan `wire:key`
1. Setiap elemen dinamis, perulangan tabel (`@foreach`), dropdown `<select>`, dan form-control kritis WAJIB memiliki `wire:key` yang unik dan stabil berdasarkan ID / kode entitas.
2. DILARANG menggunakan key dinamis yang berubah setiap render (seperti `uniqid()` atau timestamp) pada container komponen yang memegang Alpine state, karena akan menghancurkan instance Alpine secara terus-menerus.

---

## 2. Alpine.js Component Reactivity

1. Komponen Alpine (`x-data`) yang menampilkan opsi dinamis dari server (misalnya kapsul filter atau dropdown bertingkat) WAJIB dibuat reaktif terhadap pembaruan Livewire:
   - Gunakan `data-*` attributes dan `MutationObserver` untuk mendeteksi perubahan opsi dari backend.
   - Jangan mengunci daftar opsi hanya pada saat inisialisasi awal (`init()`).
2. Komponen kapsul filter dropdown harus memiliki label fallback yang jelas agar tidak pernah kosong/blank saat dilakukan reset filter atau pemilihan region.

---

## 3. UI Layering & Sticky Scroller Standards

1. **Sticky Header vs Scroll Body**:
   - Sticky header pada tabel data WAJIB memiliki `z-index` yang lebih tinggi (minimal `z-20` atau `z-30`) dan latar belakang solid (misal: `bg-base-100` atau `bg-base-200`).
   - Tombol aksi baris dan badge pada baris tabel tidak boleh tembus (*overlap/bleed-through*) ke atas sticky header saat tabel di-scroll secara vertikal.
2. **Aksi Tombol / Dropdown**:
   - Pastikan dropdown aksi di dalam tabel atau header memiliki `z-50` agar tidak terpotong oleh `overflow-x-auto` container tabel.

---

## 4. Filter Pipeline & Data Integrity

1. **Siklus Reset Filter**:
   - Tombol reset filter harus membersihkan state secara menyeluruh tanpa menyebabkan dropdown distributor/salesman terkunci (*disabled*) atau kehilangan event klik.
   - Pengecekan kode entitas (seperti kode toko atau distributor) harus bersih dari spasi ganda (*trim & clean*) dan bersifat *case-insensitive*.
