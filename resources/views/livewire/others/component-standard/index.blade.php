<div>
    <x-slot name="title">Standarisasi Komponen (UI Guide)</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8 text-base-content space-y-8">
        {{-- Header --}}
        <div>
            <h1 class="text-3xl font-bold mb-2">Standarisasi Komponen</h1>
            <p class="text-base-content/70">Panduan dan referensi komponen UI yang digunakan dalam aplikasi DevSiso.</p>
        </div>

        {{-- 1. Typography & Colors --}}
        <x-card flush title="Warna & Tipografi" icon="swatch" subtitle="Warna dasar dan format teks" class="pb-6">
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold border-b pb-2">Warna Utama (Tema)</h3>
                    <div class="flex flex-wrap gap-4">
                        <div class="w-20 h-20 bg-primary rounded-xl flex items-center justify-center text-primary-content font-bold shadow-lg shadow-primary/30">Primary</div>
                        <div class="w-20 h-20 bg-secondary rounded-xl flex items-center justify-center text-secondary-content font-bold shadow-lg shadow-secondary/30">Secondary</div>
                        <div class="w-20 h-20 bg-accent rounded-xl flex items-center justify-center text-accent-content font-bold shadow-lg shadow-accent/30">Accent</div>
                        <div class="w-20 h-20 bg-neutral rounded-xl flex items-center justify-center text-neutral-content font-bold shadow-lg shadow-neutral/30">Neutral</div>
                    </div>
                </div>
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold border-b pb-2">Warna Status</h3>
                    <div class="flex flex-wrap gap-4">
                        <div class="w-20 h-20 bg-success rounded-xl flex items-center justify-center text-success-content font-bold shadow-lg shadow-success/30">Success</div>
                        <div class="w-20 h-20 bg-warning rounded-xl flex items-center justify-center text-warning-content font-bold shadow-lg shadow-warning/30">Warning</div>
                        <div class="w-20 h-20 bg-error rounded-xl flex items-center justify-center text-error-content font-bold shadow-lg shadow-error/30">Error</div>
                        <div class="w-20 h-20 bg-info rounded-xl flex items-center justify-center text-info-content font-bold shadow-lg shadow-info/30">Info</div>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- 2. Buttons --}}
        <x-card flush title="Tombol (Buttons)" icon="hand-raised" subtitle="Variasi tombol aksi" class="pb-6">
            <div class="p-6 space-y-8">
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase text-base-content/50">Warna Button (Normal)</h3>
                    <div class="flex flex-wrap gap-3">
                        <button class="btn btn-primary rounded-xl shadow-sm shadow-primary/20">Primary</button>
                        <button class="btn btn-secondary rounded-xl shadow-sm shadow-secondary/20">Secondary</button>
                        <button class="btn btn-accent rounded-xl shadow-sm shadow-accent/20">Accent</button>
                        <button class="btn btn-info rounded-xl shadow-sm shadow-info/20">Info</button>
                        <button class="btn btn-success text-white rounded-xl shadow-sm shadow-success/20">Success</button>
                        <button class="btn btn-warning rounded-xl shadow-sm shadow-warning/20">Warning</button>
                        <button class="btn btn-error text-white rounded-xl shadow-sm shadow-error/20">Error</button>
                        <button class="btn rounded-xl">Default</button>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase text-base-content/50">Ukuran Button</h3>
                    <div class="flex flex-wrap items-center gap-3">
                        <button class="btn btn-primary btn-lg rounded-xl">Large</button>
                        <button class="btn btn-primary rounded-xl">Normal</button>
                        <button class="btn btn-primary btn-sm rounded-xl">Small</button>
                        <button class="btn btn-primary btn-xs rounded-xl">Tiny</button>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase text-base-content/50">Style Button</h3>
                    <div class="flex flex-wrap items-center gap-3">
                        <button class="btn btn-outline btn-primary rounded-xl">Outline Primary</button>
                        <button class="btn btn-outline btn-error rounded-xl">Outline Error</button>
                        <button class="btn btn-ghost rounded-xl">Ghost Button</button>
                        <button class="btn btn-link rounded-xl">Link Button</button>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase text-base-content/50">Button dengan Icon</h3>
                    <div class="flex flex-wrap gap-3">
                        <button class="btn btn-primary rounded-xl gap-2">
                            <x-heroicon-s-plus class="w-4 h-4" />
                            Tambah Data
                        </button>
                        <button class="btn btn-success text-white rounded-xl gap-2">
                            <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                            Export
                        </button>
                        <button class="btn btn-error text-white rounded-xl gap-2">
                            <x-heroicon-s-trash class="w-4 h-4" />
                            Hapus
                        </button>
                        <button class="btn btn-circle btn-primary">
                            <x-heroicon-s-magnifying-glass class="w-5 h-5" />
                        </button>
                        <button class="btn btn-square btn-outline">
                            <x-heroicon-s-pencil class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- 3. Badges --}}
        <x-card flush title="Badge & Label" icon="tag" subtitle="Indikator status atau label" class="pb-6">
            <div class="p-6 space-y-6">
                <div class="flex flex-wrap gap-3">
                    <span class="badge badge-primary">Primary</span>
                    <span class="badge badge-secondary">Secondary</span>
                    <span class="badge badge-accent">Accent</span>
                    <span class="badge badge-success text-white">Success</span>
                    <span class="badge badge-info">Info</span>
                    <span class="badge badge-warning">Warning</span>
                    <span class="badge badge-error text-white">Error</span>
                    <span class="badge">Default</span>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <span class="badge badge-lg badge-primary">Large</span>
                    <span class="badge badge-md badge-primary">Medium</span>
                    <span class="badge badge-sm badge-primary">Small</span>
                    <span class="badge badge-xs badge-primary">Extra Small</span>
                </div>

                <div class="flex flex-wrap gap-3">
                    <span class="badge badge-outline badge-primary">Outline Primary</span>
                    <span class="badge badge-outline badge-success">Outline Success</span>
                    <span class="badge badge-outline badge-error">Outline Error</span>
                    <span class="badge badge-outline">Outline Default</span>
                </div>
            </div>
        </x-card>

        {{-- 4. Alerts --}}
        <x-card flush title="Alerts & Notifikasi" icon="bell" subtitle="Pesan notifikasi untuk pengguna" class="pb-6">
            <div class="p-6 space-y-4">
                <div class="alert alert-success shadow-lg rounded-2xl border-none bg-success/20 text-success">
                    <x-heroicon-s-check-circle class="w-6 h-6 shrink-0" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Sukses</h3>
                        <div class="text-sm">Data berhasil disimpan dengan sukses.</div>
                    </div>
                </div>

                <div class="alert alert-error shadow-lg rounded-2xl border-none bg-error/20 text-error">
                    <x-heroicon-s-x-circle class="w-6 h-6 shrink-0" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Error</h3>
                        <div class="text-sm">Terjadi kesalahan saat menyimpan data.</div>
                    </div>
                </div>

                <div class="alert alert-warning shadow-lg rounded-2xl border-none bg-warning/20 text-warning-content">
                    <x-heroicon-s-exclamation-triangle class="w-6 h-6 shrink-0" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Peringatan</h3>
                        <div class="text-sm">Mohon periksa kembali inputan Anda.</div>
                    </div>
                </div>

                <div class="alert alert-info shadow-lg rounded-2xl border-none bg-info/20 text-info">
                    <x-heroicon-s-information-circle class="w-6 h-6 shrink-0" />
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider">Informasi</h3>
                        <div class="text-sm">Pembaruan sistem akan dilakukan malam ini.</div>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- 5. Form Elements --}}
        <x-card flush title="Elemen Form" icon="document-text" subtitle="Input teks, select, dan form lainnya" class="pb-6">
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Input Text <span class="text-error">*</span></label>
                        <input type="text" placeholder="Ketik disini..." class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Input Error State</label>
                        <input type="text" placeholder="Ketik disini..." class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-error/50 input-error transition-all duration-300" value="Input salah">
                        <span class="text-error text-[10px] font-medium ml-1">Field ini wajib diisi dengan format yang benar.</span>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Input Disabled</label>
                        <input type="text" placeholder="Tidak bisa diisi" class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl" disabled>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Select Dropdown</label>
                        <select class="select select-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            <option value="">-- Pilih Opsi --</option>
                            <option value="1">Opsi Pertama</option>
                            <option value="2">Opsi Kedua</option>
                            <option value="3">Opsi Ketiga</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Textarea</label>
                        <textarea class="textarea textarea-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300" placeholder="Tuliskan keterangan..."></textarea>
                    </div>
                    
                    <div class="flex items-center gap-6">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" checked="checked" class="checkbox checkbox-primary rounded-lg" />
                            <span class="label-text">Checkbox</span>
                        </label>
                        
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="radio" name="radio-1" class="radio radio-primary" checked />
                            <span class="label-text">Radio 1</span>
                        </label>

                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" class="toggle toggle-primary" checked />
                            <span class="label-text">Toggle</span>
                        </label>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- 6. Tables --}}
        <div class="space-y-6">
            <h2 class="text-2xl font-bold border-b pb-2">Varian Tabel</h2>

            {{-- 6.1 Tabel Standar --}}
            <x-card flush title="Tabel Standar" icon="table-cells" subtitle="Format tabel default">
                <x-ui.table empty="Data tidak ditemukan.">
                    <x-slot:head>
                        <tr>
                            <th class="w-12">No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <th class="w-24 text-center">Aksi</th>
                        </tr>
                    </x-slot:head>
                    
                    <tr class="group text-sm">
                        <td><span class="text-xs font-semibold text-base-content/40">1</span></td>
                        <td><span class="font-mono text-base-content/80">REG-001</span></td>
                        <td><span class="font-bold text-base-content/80">Jawa Barat</span></td>
                        <td><span class="badge badge-sm badge-success text-white">Aktif</span></td>
                        <td class="text-center">
                            <button class="btn btn-xs btn-circle btn-ghost text-primary"><x-heroicon-s-pencil class="w-4 h-4" /></button>
                        </td>
                    </tr>
                    <tr class="group text-sm">
                        <td><span class="text-xs font-semibold text-base-content/40">2</span></td>
                        <td><span class="font-mono text-base-content/80">REG-002</span></td>
                        <td><span class="font-bold text-base-content/80">Jawa Tengah</span></td>
                        <td><span class="badge badge-sm badge-error text-white">Nonaktif</span></td>
                        <td class="text-center">
                            <button class="btn btn-xs btn-circle btn-ghost text-primary"><x-heroicon-s-pencil class="w-4 h-4" /></button>
                        </td>
                    </tr>
                </x-ui.table>
            </x-card>

            {{-- 6.2 Tabel dengan Sort --}}
            <x-card flush title="Tabel dengan Sort" icon="arrows-up-down" subtitle="Indikator sorting pada header tabel">
                <x-ui.table empty="Data tidak ditemukan.">
                    <x-slot:head>
                        <tr>
                            <th class="w-12">No</th>
                            <th class="cursor-pointer hover:bg-base-200 transition-colors">
                                <div class="flex items-center gap-1">
                                    Kode
                                    <x-heroicon-s-chevron-up-down class="w-4 h-4 opacity-50" />
                                </div>
                            </th>
                            <th class="cursor-pointer hover:bg-base-200 transition-colors">
                                <div class="flex items-center gap-1">
                                    Nama
                                    <x-heroicon-s-chevron-down class="w-4 h-4 text-primary" />
                                </div>
                            </th>
                            <th>Status</th>
                        </tr>
                    </x-slot:head>
                    <tr class="group text-sm">
                        <td>1</td><td>REG-001</td><td>Jawa Barat</td><td><span class="badge badge-sm badge-success text-white">Aktif</span></td>
                    </tr>
                </x-ui.table>
            </x-card>

            {{-- 6.3 Tabel Fix Header --}}
            <x-card flush title="Tabel Fix Header" icon="bars-arrow-down" subtitle="Header tetap berada di atas saat scroll (tambahkan class h-[tinggi] dan sticky=true)">
                <x-ui.table sticky="true" class="h-48 overflow-y-auto" empty="Data tidak ditemukan.">
                    <x-slot:head>
                        <tr>
                            <th class="w-12">No</th>
                            <th>Nama Regional</th>
                            <th>Status</th>
                        </tr>
                    </x-slot:head>
                    @for($i = 1; $i <= 8; $i++)
                        <tr class="group text-sm">
                            <td>{{ $i }}</td>
                            <td>Regional {{ $i }}</td>
                            <td><span class="badge badge-sm badge-outline">Aktif</span></td>
                        </tr>
                    @endfor
                </x-ui.table>
            </x-card>

            {{-- 6.4 Tabel Fix Header & Kolom Side Freeze --}}
            <x-card flush title="Tabel Fix Header & Side Freeze" icon="view-columns" subtitle="Header dan kolom pertama/terakhir terkunci (tambahkan sticky=true dan pinCols=true)">
                <x-ui.table sticky="true" pinCols="true" class="h-48 overflow-auto" empty="Data tidak ditemukan.">
                    <x-slot:head>
                        <tr>
                            <th>ID</th>
                            <th>Nama Customer</th>
                            <th>Alamat</th>
                            <th>Kota</th>
                            <th>Provinsi</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Limit Kredit</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </x-slot:head>
                    @for($i = 1; $i <= 5; $i++)
                        <tr class="text-sm">
                            <th>{{ 100 + $i }}</th>
                            <td>PT. Contoh Maju {{ $i }}</td>
                            <td>Jl. Sudirman No. {{ $i }}0</td>
                            <td>Jakarta Pusat</td>
                            <td>DKI Jakarta</td>
                            <td>0812345678{{ $i }}</td>
                            <td>contact{{ $i }}@contoh.com</td>
                            <td>Rp {{ number_format(10000000 * $i, 0, ',', '.') }}</td>
                            <td><span class="badge badge-sm badge-success text-white">Verified</span></td>
                            <th>
                                <button class="btn btn-xs btn-primary">Pilih</button>
                            </th>
                        </tr>
                    @endfor
                </x-ui.table>
            </x-card>
        </div>

        {{-- 7. Filter & Search Bar Area --}}
        <x-card flush title="Filter Bar" icon="funnel" subtitle="Standar untuk pencarian dan filter di atas tabel" class="pb-6">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Search --}}
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-base-content/30 group-focus-within:text-primary transition-colors">
                            <x-heroicon-s-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input type="text" placeholder="Cari data..." class="input input-sm input-bordered pl-10 w-full sm:w-64 rounded-xl bg-base-100 border-base-300 focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                    </div>

                    {{-- Select Filter --}}
                    <select class="select select-sm select-bordered rounded-xl bg-base-100 border-base-300">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>

                    <button class="btn btn-sm btn-success text-white rounded-xl normal-case gap-2 shadow-sm shadow-success/20">
                        <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                        Export
                    </button>

                    <button class="btn btn-sm btn-primary rounded-xl normal-case gap-2 shadow-sm shadow-primary/20">
                        <x-heroicon-s-plus class="w-4 h-4" />
                        Tambah Data
                    </button>
                </div>
            </x-slot:actions>
            
            <div class="p-6">
                <p class="text-sm text-base-content/60">Gunakan `&lt;x-slot:actions&gt;` pada komponen `&lt;x-card&gt;` untuk menempatkan search bar dan filter di sudut kanan atas seperti contoh ini.</p>
            </div>
        </x-card>

        {{-- 8. Modal Example --}}
        <x-card flush title="Contoh Modal" icon="window" subtitle="Struktur UI untuk Modal form" class="pb-6">
            <div class="p-6">
                <button onclick="document.getElementById('demo-modal').showModal()" class="btn btn-primary rounded-xl">Buka Demo Modal</button>

                <dialog id="demo-modal" class="modal modal-bottom sm:modal-middle">
                    <div class="modal-box bg-base-100 rounded-3xl shadow-2xl p-0 w-full max-w-lg border border-base-300">
                        <div class="flex items-center justify-between px-6 py-5 border-b border-base-300 bg-base-200/30 rounded-t-3xl">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 rounded-2xl bg-primary/10 text-primary">
                                    <x-heroicon-s-plus-circle class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg leading-none">Judul Modal Form</h3>
                                    <p class="text-[11px] text-base-content/50 mt-1 uppercase tracking-wider font-semibold">Subjudul atau keterangan singkat</p>
                                </div>
                            </div>
                            <form method="dialog">
                                <button class="btn btn-sm btn-circle btn-ghost text-base-content/30 hover:text-base-content hover:bg-base-300">
                                    <x-heroicon-s-x-mark class="w-5 h-5" />
                                </button>
                            </form>
                        </div>
                        
                        <div class="p-6 space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-base-content/50 ml-1">Contoh Input <span class="text-error">*</span></label>
                                <input type="text" placeholder="Input dalam modal" class="input input-bordered w-full bg-base-200 border-base-300 rounded-2xl focus:ring-2 focus:ring-primary/50 transition-all duration-300">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-6 py-5 border-t border-base-300 bg-base-200/30 rounded-b-3xl">
                            <form method="dialog">
                                <button class="btn btn-ghost rounded-xl normal-case hover:bg-base-300">Batal</button>
                            </form>
                            <button class="btn btn-primary rounded-xl px-10 normal-case shadow-sm shadow-primary/20 gap-2">
                                Simpan Data
                                <x-heroicon-s-paper-airplane class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>
            </div>
        </x-card>

    </div>
</div>
