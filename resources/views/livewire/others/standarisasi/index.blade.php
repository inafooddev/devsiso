<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full">
    <x-slot name="title">Standarisasi & AI Prompts</x-slot>

    {{-- Header --}}
    <div class="bg-base-100 rounded-xl shadow-sm border border-base-300 p-4 shrink-0">
        <h2 class="text-base md:text-lg lg:text-xl font-bold">Panduan Prompt AI (Devsiso Design System)</h2>
        <p class="text-xs md:text-sm text-base-content/70 mt-1">
            Gunakan template teks di bawah ini dan berikan kepada AI (seperti GitHub Copilot, ChatGPT, atau Gemini) setiap kali Anda ingin membuat atau memperbaiki halaman di aplikasi ini. Prompt ini memaksa AI untuk mengikuti standar komponen, layout flexbox, dan tipografi Devsiso.
        </p>
    </div>

    {{-- Main Content - Scrollable --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-auto">
        <div class="p-4 md:p-6 lg:p-8 flex flex-col gap-6">
            
            {{-- Section 1: Buat Modul Baru --}}
            <div class="border border-base-300 rounded-xl overflow-hidden">
                <div class="bg-primary/10 px-4 py-3 border-b border-base-300 flex items-center gap-2">
                    <x-heroicon-s-document-plus class="w-5 h-5 text-primary" />
                    <h3 class="font-bold text-primary">1. Membuat Modul / Halaman Baru</h3>
                </div>
                <div class="p-4 bg-base-200/30">
                    <p class="text-xs text-base-content/70 mb-2">Gunakan prompt ini ketika Anda menyuruh AI membuatkan halaman Livewire dari nol.</p>
                    <div class="bg-base-300 p-4 rounded-lg relative group">
                        <pre class="text-xs md:text-sm whitespace-pre-wrap font-mono text-base-content">Tolong buatkan halaman Livewire baru untuk modul "[MASUKKAN NAMA MODUL]". 

PENTING: Anda WAJIB mengikuti standarisasi UI "Devsiso Design System" secara ketat. Berikut aturannya:
1. Baca referensi aturan desain di artefak `ui_guidelines.md` (jika ada di history) atau ikuti poin-poin ini.
2. Layout Utama: Harus 100% fluid. Jangan gunakan pembatas lebar seperti `max-w-screen-2xl`. Gunakan `w-full flex-1 flex flex-col min-h-0 min-w-0` untuk mencegah overflow flexbox.
3. Spacing Responsif: Gunakan padding dan gap berundak. Contoh: `gap-3 md:gap-4 lg:gap-6` dan `p-3 md:p-4 lg:p-5`.
4. Komponen UI Baku: Dilarang keras membuat tag &lt;button&gt; manual untuk aksi standar. Wajib gunakan komponen Blade: `&lt;x-ui.search-input /&gt;` dan `&lt;x-ui.action-button type="..." /&gt;` (Pilihan type: add, edit, delete, import, export, filter, save).
5. Baris Aksi (Header): Harus dibungkus dengan `flex flex-wrap` agar tombol tidak tumpang tindih saat layar di-zoom.
6. Tabel: Wajib tambahkan class `whitespace-nowrap` pada tag `&lt;table&gt;` agar kolom tabel tidak hancur menjadi menara teks vertikal saat dibuka di HP atau di-zoom 150%.</pre>
                    </div>
                </div>
            </div>

            {{-- Section 2: Refactor --}}
            <div class="border border-base-300 rounded-xl overflow-hidden">
                <div class="bg-warning/10 px-4 py-3 border-b border-base-300 flex items-center gap-2">
                    <x-heroicon-s-wrench-screwdriver class="w-5 h-5 text-warning" />
                    <h3 class="font-bold text-warning">2. Memperbaiki (Refactor) Halaman Lama</h3>
                </div>
                <div class="p-4 bg-base-200/30">
                    <p class="text-xs text-base-content/70 mb-2">Gunakan prompt ini jika halaman yang ada saat ini berantakan saat dizoom atau komponennya belum baku.</p>
                    <div class="bg-base-300 p-4 rounded-lg relative group">
                        <pre class="text-xs md:text-sm whitespace-pre-wrap font-mono text-base-content">Tolong refactor dan rapikan halaman/view di "[MASUKKAN LOKASI FILE]". Halaman ini belum mengikuti standarisasi UI Devsiso.

Lakukan perbaikan berikut:
1. Ganti semua form pencarian manual menjadi komponen `&lt;x-ui.search-input /&gt;`.
2. Ganti semua tombol (Tambah, Simpan, Edit, Hapus, Export, Import) menjadi komponen `&lt;x-ui.action-button type="..." /&gt;`.
3. Terapkan adaptive padding pada card (`p-3 md:p-4 lg:p-5`) dan adaptive gap (`gap-3 md:gap-4 lg:gap-6`).
4. Hapus class `max-w-*` atau `table-pin-cols` jika ada, karena dapat merusak responsivitas.
5. Suntikkan class `min-w-0` pada setiap container flex column untuk mencegah flexbox anak menembus batas layar.
6. Tambahkan `whitespace-nowrap` pada tabel agar memunculkan scroll horizontal yang rapi.</pre>
                    </div>
                </div>
            </div>

            {{-- Section 3: Revisi Standar --}}
            <div class="border border-base-300 rounded-xl overflow-hidden">
                <div class="bg-info/10 px-4 py-3 border-b border-base-300 flex items-center gap-2">
                    <x-heroicon-s-adjustments-horizontal class="w-5 h-5 text-info" />
                    <h3 class="font-bold text-info">3. Merevisi Standar & Menambah Komponen</h3>
                </div>
                <div class="p-4 bg-base-200/30">
                    <p class="text-xs text-base-content/70 mb-2">Gunakan prompt ini jika Anda ingin menambahkan jenis komponen baru ke dalam buku standar Devsiso.</p>
                    <div class="bg-base-300 p-4 rounded-lg relative group">
                        <pre class="text-xs md:text-sm whitespace-pre-wrap font-mono text-base-content">Saya ingin merevisi/menambah aturan standarisasi UI Devsiso kita. 
Saat ini saya melihat bahwa [JELASKAN MASALAHNYA, misal: Tombol 'Print' belum ada komponen bakunya, atau Jarak tabel terlalu renggang].

Tolong lakukan hal berikut:
1. Update komponen terkait (misalnya di `resources/views/components/ui/action-button.blade.php`).
2. Terapkan perubahannya ke template standar kita (`resources/views/livewire/others/page-template-standard/index.blade.php`).
3. Update dokumen panduan `ui_guidelines.md` di dalam artifacts agar mencerminkan standar terbaru ini.</pre>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
