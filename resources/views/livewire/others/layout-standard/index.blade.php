<div>
    <x-slot name="title">Standarisasi Layout & Spacing</x-slot>

    <div class="mx-auto px-4 sm:px-6 py-8 text-base-content space-y-8">
        {{-- Header --}}
        <div>
            <h1 class="text-3xl font-bold mb-2">Standarisasi Layout & Spacing</h1>
            <p class="text-base-content/70">Panduan penggunaan jarak (margin, padding) serta ukuran (tinggi, lebar) elemen pada antarmuka aplikasi DevSiso.</p>
        </div>

        {{-- 1. Card & Container Padding --}}
        <x-card flush title="Standar Padding dalam Komponen" icon="square-2-stack" subtitle="Jarak ideal di dalam Card atau Kontainer">
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase text-base-content/50 border-b pb-2">Padding Standar (p-6)</h3>
                    <div class="bg-base-200 border-2 border-dashed border-primary/50 relative overflow-hidden rounded-2xl group">
                        <div class="bg-primary/20 absolute inset-0 m-6 rounded-lg flex items-center justify-center text-primary-content">
                            <span class="bg-base-100 text-base-content text-xs font-bold py-1 px-3 rounded-full shadow-sm">Konten Utama</span>
                        </div>
                        <div class="h-32 p-6 flex flex-col justify-between">
                            <span class="text-[10px] font-mono text-primary rotate-90 origin-left absolute left-2 top-10">p-6 (24px)</span>
                            <span class="text-[10px] font-mono text-primary absolute top-2 left-10">p-6 (24px)</span>
                        </div>
                    </div>
                    <p class="text-xs text-base-content/60">Gunakan class <code>p-6</code> (24px) sebagai standar padding dalam pada kebanyakan komponen Card atau blok konten berukuran sedang-besar.</p>
                </div>

                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase text-base-content/50 border-b pb-2">Padding Rapat (p-4)</h3>
                    <div class="bg-base-200 border-2 border-dashed border-secondary/50 relative overflow-hidden rounded-2xl">
                        <div class="bg-secondary/20 absolute inset-0 m-4 rounded-lg flex items-center justify-center text-secondary-content">
                            <span class="bg-base-100 text-base-content text-xs font-bold py-1 px-3 rounded-full shadow-sm">Konten Utama</span>
                        </div>
                        <div class="h-32 p-4 flex flex-col justify-between">
                            <span class="text-[10px] font-mono text-secondary rotate-90 origin-left absolute left-1 top-10">p-4 (16px)</span>
                            <span class="text-[10px] font-mono text-secondary absolute top-1 left-10">p-4 (16px)</span>
                        </div>
                    </div>
                    <p class="text-xs text-base-content/60">Gunakan class <code>p-4</code> (16px) untuk komponen kecil seperti list item, dropdown item, atau panel samping.</p>
                </div>

            </div>
        </x-card>

        {{-- 2. Spacing / Margin --}}
        <x-card flush title="Standar Jarak Antar Elemen (Margin)" icon="arrows-pointing-out" subtitle="Gunakan space-y-* untuk grup vertikal dan gap-* untuk flex/grid">
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                
                {{-- Space Vertikal --}}
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase text-base-content/50 border-b pb-2">Grup Vertikal (space-y-*)</h3>
                    
                    <div class="bg-base-200 p-4 rounded-2xl flex">
                        <div class="w-full space-y-4">
                            <div class="bg-primary/20 p-3 rounded-xl text-center text-sm font-semibold">Elemen 1</div>
                            <div class="bg-error/10 border-y border-dashed border-error relative flex items-center justify-center py-1">
                                <span class="text-[10px] font-mono text-error absolute">space-y-4 (16px)</span>
                            </div>
                            <div class="bg-primary/20 p-3 rounded-xl text-center text-sm font-semibold">Elemen 2</div>
                            <div class="bg-error/10 border-y border-dashed border-error relative flex items-center justify-center py-1">
                                <span class="text-[10px] font-mono text-error absolute">space-y-4 (16px)</span>
                            </div>
                            <div class="bg-primary/20 p-3 rounded-xl text-center text-sm font-semibold">Elemen 3</div>
                        </div>
                    </div>
                    <p class="text-xs text-base-content/60">Bungkus sekumpulan blok input atau card dengan div dan class <code>space-y-4</code> atau <code>space-y-6</code> untuk memberikan margin bawah seragam.</p>
                </div>

                {{-- Gap Grid / Flex --}}
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase text-base-content/50 border-b pb-2">Grid / Flex Gap (gap-*)</h3>
                    
                    <div class="bg-base-200 p-4 rounded-2xl">
                        <div class="flex items-center gap-3 relative">
                            <div class="bg-accent/20 flex-1 p-3 rounded-xl text-center text-sm font-semibold">Tombol 1</div>
                            <div class="w-3 bg-error/10 border-x border-dashed border-error h-full flex items-center justify-center overflow-visible">
                                <span class="text-[10px] font-mono text-error absolute -top-4">gap-3 (12px)</span>
                            </div>
                            <div class="bg-accent/20 flex-1 p-3 rounded-xl text-center text-sm font-semibold">Tombol 2</div>
                        </div>
                        
                        <div class="mt-6 grid grid-cols-2 gap-4 relative">
                            <div class="bg-info/20 p-6 rounded-xl text-center text-sm font-semibold">Grid Item 1</div>
                            <div class="bg-info/20 p-6 rounded-xl text-center text-sm font-semibold">Grid Item 2</div>
                            <div class="absolute inset-y-0 left-1/2 -ml-2 w-4 bg-error/10 border-x border-dashed border-error flex items-center justify-center">
                                <span class="text-[10px] font-mono text-error absolute -top-4 whitespace-nowrap">gap-4 (16px)</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-base-content/60">Gunakan class <code>gap-3</code> antar elemen inline seperti kumpulan tombol. Gunakan <code>gap-4</code>, <code>gap-6</code>, atau <code>gap-8</code> untuk tata letak kolom berbasis grid.</p>
                </div>

            </div>
        </x-card>

        {{-- 3. Width (Lebar) --}}
        <x-card flush title="Standar Lebar Konten (Width)" icon="arrows-right-left" subtitle="Mengatur batasan maksimal lebar elemen">
            <div class="p-6 space-y-6">
                
                <div class="space-y-2">
                    <h3 class="text-sm font-bold">Max Width Kustom Form Modal</h3>
                    <div class="w-full bg-base-200 rounded-xl h-12 flex relative border-r-2 border-error border-dashed">
                        <div class="bg-primary/20 w-full max-w-2xl h-full rounded-l-xl flex items-center px-4 justify-between border-r-2 border-primary border-dashed">
                            <span class="text-sm font-semibold">Modal Besar / Form Lebar</span>
                            <span class="text-[10px] font-mono bg-base-100 px-2 py-0.5 rounded">max-w-2xl (42rem/672px)</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <h3 class="text-sm font-bold">Max Width Dialog Konfirmasi</h3>
                    <div class="w-full bg-base-200 rounded-xl h-12 flex relative border-r-2 border-error border-dashed">
                        <div class="bg-secondary/20 w-full max-w-lg h-full rounded-l-xl flex items-center px-4 justify-between border-r-2 border-secondary border-dashed">
                            <span class="text-sm font-semibold">Modal Dialog Kecil</span>
                            <span class="text-[10px] font-mono bg-base-100 px-2 py-0.5 rounded">max-w-lg (32rem/512px)</span>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <h3 class="text-sm font-bold">Lebar Input Field</h3>
                    <div class="flex gap-4 items-center">
                        <div class="w-full">
                            <div class="bg-base-200 p-2 rounded-xl text-center text-xs font-mono text-base-content/60 border border-base-300">w-full (Tergantung grid pembungkusnya)</div>
                        </div>
                        <div class="w-64 shrink-0">
                            <div class="bg-base-200 p-2 rounded-xl text-center text-xs font-mono text-base-content/60 border border-base-300">w-64 (256px - Search bar)</div>
                        </div>
                    </div>
                </div>

            </div>
        </x-card>

        {{-- 4. Height (Tinggi) --}}
        <x-card flush title="Standar Tinggi Konten (Height)" icon="arrows-up-down" subtitle="Aturan tinggi untuk card dan area scroll">
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase text-base-content/50 border-b pb-2">Tinggi Tetap dengan Scroll</h3>
                    
                    <div class="bg-base-200 p-4 rounded-2xl relative h-64 overflow-y-auto border border-base-300">
                        <div class="absolute right-0 top-0 bottom-0 w-8 bg-error/10 border-l border-dashed border-error flex flex-col items-center justify-center">
                            <span class="text-[10px] font-mono text-error -rotate-90 whitespace-nowrap">h-64 (256px)</span>
                        </div>
                        
                        <div class="space-y-2 pr-10">
                            @for($i=1; $i<=10; $i++)
                                <div class="bg-base-100 p-3 rounded-xl text-sm shadow-sm">Item List {{ $i }}</div>
                            @endfor
                        </div>
                    </div>
                    <p class="text-xs text-base-content/60">Gunakan class <code>h-64</code>, <code>h-72</code>, atau <code>h-96</code> dikombinasikan dengan <code>overflow-y-auto</code> untuk membuat area scroll terbatas (seperti dropdown hasil list atau tabel fix-header berukuran sedang).</p>
                </div>

                <div class="space-y-4">
                    <h3 class="text-sm font-bold uppercase text-base-content/50 border-b pb-2">Min-Height & Auto Height</h3>
                    
                    <div class="bg-base-200 p-4 rounded-2xl relative min-h-[16rem] border border-base-300 flex flex-col items-center justify-center text-center">
                        <div class="absolute left-0 top-0 bottom-0 w-8 bg-success/10 border-r border-dashed border-success flex flex-col items-center justify-center">
                            <span class="text-[10px] font-mono text-success -rotate-90 whitespace-nowrap">min-h-64</span>
                        </div>
                        
                        <x-heroicon-o-document-magnifying-glass class="w-12 h-12 text-base-content/30 mb-2" />
                        <span class="text-sm font-bold text-base-content/60">Area Kosong Tabel / Widget</span>
                        <p class="text-xs text-base-content/50 mt-1 max-w-[200px]">Gunakan class <code>min-h-64</code> atau <code>min-h-[xxx]</code> agar area kosong tidak menciut saat tidak ada data.</p>
                    </div>
                </div>

            </div>
        </x-card>

        {{-- 5. Helper Classes Summary --}}
        <x-card flush title="Ringkasan Cheat Sheet" icon="academic-cap" subtitle="Daftar class yang sering digunakan di project">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="table table-zebra table-sm">
                        <thead class="bg-base-200 text-base-content">
                            <tr>
                                <th>Kategori</th>
                                <th>Class Utility Tailwind</th>
                                <th>Deskripsi Penggunaan DevSiso</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-bold">Padding Dalam (Card)</td>
                                <td><code class="text-primary bg-primary/10 px-1 rounded">p-6</code>, <code class="text-primary bg-primary/10 px-1 rounded">px-6 py-5</code></td>
                                <td>Standar ruang nafas (whitespace) dalam `&lt;x-card&gt;` atau section putih.</td>
                            </tr>
                            <tr>
                                <td class="font-bold">Padding Header Modal</td>
                                <td><code class="text-primary bg-primary/10 px-1 rounded">px-6 py-5</code></td>
                                <td>Standar ruang untuk bagian *header* dan *footer* (aksi tombol) pada modal.</td>
                            </tr>
                            <tr>
                                <td class="font-bold">Margin Vertikal (Grup)</td>
                                <td><code class="text-primary bg-primary/10 px-1 rounded">space-y-4</code>, <code class="text-primary bg-primary/10 px-1 rounded">space-y-6</code></td>
                                <td>Jarak baris antar field di dalam formulir, atau kumpulan notifikasi bertumpuk.</td>
                            </tr>
                            <tr>
                                <td class="font-bold">Spacing Antar Input</td>
                                <td><code class="text-primary bg-primary/10 px-1 rounded">gap-4</code>, <code class="text-primary bg-primary/10 px-1 rounded">gap-6</code></td>
                                <td>Jarak antara 2 kolom input di dalam grid form (contoh `&lt;div class="grid grid-cols-2 gap-4"&gt;`).</td>
                            </tr>
                            <tr>
                                <td class="font-bold">Max Lebar Modal</td>
                                <td><code class="text-primary bg-primary/10 px-1 rounded">max-w-2xl</code>, <code class="text-primary bg-primary/10 px-1 rounded">max-w-lg</code></td>
                                <td>`max-w-2xl` untuk formulir panjang, `max-w-lg` untuk modal kecil atau dialog konfirmasi.</td>
                            </tr>
                            <tr>
                                <td class="font-bold">Tinggi Area Scroll</td>
                                <td><code class="text-primary bg-primary/10 px-1 rounded">h-64</code>, <code class="text-primary bg-primary/10 px-1 rounded">h-96</code></td>
                                <td>Untuk container data list atau tabel fix header agar tidak menutupi seluruh layar.</td>
                            </tr>
                            <tr>
                                <td class="font-bold">Border Radius</td>
                                <td><code class="text-primary bg-primary/10 px-1 rounded">rounded-2xl</code>, <code class="text-primary bg-primary/10 px-1 rounded">rounded-xl</code></td>
                                <td>`rounded-2xl` untuk card/modal luar, `rounded-xl` untuk elemen dalam (input, tombol).</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </x-card>

    </div>
</div>
