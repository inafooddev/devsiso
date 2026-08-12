<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full">
    <x-slot name="title">Proses Ekstraksi Excel</x-slot>

    {{-- Tabs Navigation --}}
    @include('livewire.others.qceskalink._tabs')

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden" x-data="extractorProcess($wire)">

        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                        <x-heroicon-s-cpu-chip class="w-5 h-5" />
                    </div>
                    Proses Ekstraksi File
                </h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">Upload file Excel, proses, review hasil, lalu simpan ke database</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                {{-- Badge hasil --}}
                <span x-show="results.length > 0" class="badge badge-primary badge-sm font-semibold" x-text="results.length + ' baris hasil'" style="display:none"></span>

                {{-- Tombol Hapus Preview --}}
                <button x-show="results.length > 0" @click="results = []; resultColumns = []"
                    class="btn btn-sm btn-ghost text-error rounded-xl normal-case gap-1" style="display:none">
                    <x-heroicon-s-trash class="w-4 h-4" /> Hapus Preview
                </button>

                {{-- Tombol Simpan --}}
                <button x-show="results.length > 0 && !isSaving" @click="showSaveModal = true"
                    class="btn btn-sm btn-success rounded-xl normal-case gap-2 shadow-sm shadow-success/20" style="display:none"
                    :disabled="isSaving">
                    <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                    Simpan <span x-text="results.length"></span> Hasil
                </button>
                <button x-show="isSaving" class="btn btn-sm btn-success rounded-xl normal-case" style="display:none" disabled>
                    <span class="loading loading-spinner loading-xs"></span> Menyimpan...
                </button>
            </div>
        </div>

        {{-- Body Card: 2 panel --}}
        <div class="flex-1 min-h-0 flex overflow-hidden">

            {{-- ======================================================== --}}
            {{-- PANEL KIRI: Upload + Eksekusi + List File --}}
            {{-- ======================================================== --}}
            <div class="w-72 xl:w-80 shrink-0 border-r border-base-300 flex flex-col overflow-hidden">

                {{-- Upload + Eksekusi --}}
                <div class="p-4 border-b border-base-300 bg-base-50/50 space-y-3 shrink-0">

                    {{-- Toast --}}
                    <div
                        x-show="toastVisible"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="rounded-lg border p-2.5 flex items-start gap-2 text-xs"
                        :class="{
                            'bg-success/10 border-success/30 text-success': toastType === 'success',
                            'bg-warning/10 border-warning/30 text-warning': toastType === 'warning',
                            'bg-error/10 border-error/30 text-error':   toastType === 'error',
                            'bg-info/10 border-info/30 text-info':      toastType === 'info',
                        }"
                        style="display:none"
                    >
                        <svg x-show="toastType==='success'" class="w-3.5 h-3.5 shrink-0 mt-px" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <svg x-show="toastType==='warning'" class="w-3.5 h-3.5 shrink-0 mt-px" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <svg x-show="toastType==='error'" class="w-3.5 h-3.5 shrink-0 mt-px" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <svg x-show="toastType==='info'" class="w-3.5 h-3.5 shrink-0 mt-px" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-text="toastMessage" class="flex-1 leading-snug"></span>
                        <button @click="toastVisible=false" class="opacity-50 hover:opacity-100 leading-none shrink-0">&times;</button>
                    </div>

                    {{-- Dropzone --}}
                    <div
                        x-ref="dropzone"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop($event)"
                        :class="isDragging ? 'border-primary bg-primary/5 scale-[1.01]' : 'border-base-300 hover:border-primary/50 hover:bg-base-200/30'"
                        class="border-2 border-dashed rounded-xl p-4 flex flex-col items-center justify-center text-center cursor-pointer transition-all"
                        @click="$refs.fileInput.click()"
                    >
                        <input type="file" x-ref="fileInput" class="hidden" multiple accept=".xlsx,.xls,.csv" @change="handleFileSelect($event)">
                        <div class="w-9 h-9 bg-primary/10 text-primary rounded-full flex items-center justify-center mb-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <p class="text-xs font-bold text-base-content/80">Klik atau Drag & Drop</p>
                        <p class="text-[10px] text-base-content/40 mt-0.5">.xlsx · .xls · .csv</p>
                    </div>

                    {{-- Progress --}}
                    <div x-show="isProcessing" style="display:none">
                        <div class="flex justify-between text-[10px] mb-1">
                            <span class="text-primary font-semibold truncate" x-text="progressText"></span>
                            <span class="text-base-content/40 shrink-0 ml-1" x-text="Math.round(progressPercent)+'%'"></span>
                        </div>
                        <progress class="progress progress-primary w-full h-1.5" :value="progressPercent" max="100"></progress>
                    </div>

                    {{-- Tombol Proses --}}
                    <button @click="startProcessing()"
                        class="btn btn-sm btn-primary w-full rounded-xl normal-case gap-2 shadow-sm shadow-primary/20"
                        :disabled="files.length === 0 || isProcessing">
                        <template x-if="!isProcessing">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Proses <span x-text="files.filter(f=>f.matchedGroupId).length"></span> File
                            </span>
                        </template>
                        <template x-if="isProcessing">
                            <span class="flex items-center gap-1.5">
                                <span class="loading loading-spinner loading-xs"></span> Memproses...
                            </span>
                        </template>
                    </button>

                    {{-- Sukses info --}}
                    <div x-show="lastBatchId" class="p-2.5 rounded-lg bg-success/10 border border-success/20" style="display:none">
                        <p class="text-xs font-bold text-success flex items-center gap-1">
                            <x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Tersimpan ke database!
                        </p>
                        <p class="text-[10px] text-base-content/40 mt-1 break-all font-mono" x-text="lastBatchId"></p>
                    </div>

                    {{-- Error log --}}
                    <div x-show="errors.length > 0" class="p-2.5 rounded-lg bg-error/10 border border-error/20" style="display:none">
                        <p class="text-xs font-bold text-error mb-1">
                            <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5 inline -mt-px" />
                            Gagal (<span x-text="errors.length"></span> file):
                        </p>
                        <ul class="text-[10px] text-error/70 space-y-0.5">
                            <template x-for="err in errors" :key="err">
                                <li class="truncate" :title="err" x-text="err"></li>
                            </template>
                        </ul>
                    </div>
                </div>

                {{-- List File (scrollable) --}}
                <div class="flex-1 overflow-y-auto">
                    {{-- Header list --}}
                    <div x-show="files.length > 0" class="px-4 py-2.5 border-b border-base-200 flex items-center justify-between sticky top-0 bg-base-100 z-10" style="display:none">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-base-content/50">
                            File (<span x-text="files.length"></span>)
                        </span>
                        <div class="flex items-center gap-2">
                            <span x-show="unmatchedCount > 0" class="badge badge-warning badge-xs font-semibold" x-text="unmatchedCount+' belum'"></span>
                            <span x-show="unmatchedCount === 0 && files.length > 0" class="badge badge-success badge-xs">Semua ✓</span>
                            <button @click="clearFiles()" class="text-[10px] text-error hover:underline">Hapus</button>
                        </div>
                    </div>

                    {{-- Empty state --}}
                    <div x-show="files.length === 0" class="flex flex-col items-center justify-center h-full p-8 text-center" style="display:none">
                        <x-heroicon-o-document-arrow-up class="w-10 h-10 text-base-content/15 mb-2" />
                        <p class="text-xs text-base-content/30">Belum ada file</p>
                    </div>
                    <div x-show="files.length === 0" class="flex flex-col items-center justify-center h-full p-8 text-center">
                        <svg class="w-10 h-10 text-base-content/15 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-xs text-base-content/30">Belum ada file diupload</p>
                    </div>

                    {{-- File items --}}
                    <div class="p-3 space-y-2">
                        <template x-for="(file, index) in files" :key="file.id">
                            <div class="rounded-lg border p-2.5 transition-colors"
                                :class="file.matchedGroupId ? 'border-success/30 bg-success/5' : 'border-error/30 bg-error/5'">
                                <div class="flex items-start justify-between gap-1 mb-1.5">
                                    <span class="text-[11px] font-semibold text-base-content/80 leading-snug break-all" x-text="file.name" :title="file.name"></span>
                                    <button @click="removeFile(index)" class="btn btn-xs btn-ghost btn-square text-base-content/30 hover:text-error shrink-0 -mt-0.5">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <select x-model="file.matchedGroupId"
                                    class="select select-xs w-full font-semibold"
                                    :class="file.matchedGroupId ? 'select-success text-success' : 'select-error text-error'">
                                    <option value="">-- Pilih Grup --</option>
                                    <template x-for="g in groups" :key="g.id">
                                        <option :value="String(g.id)" x-text="g.name"></option>
                                    </template>
                                </select>
                                <div class="text-[10px] text-base-content/30 mt-1" x-text="formatBytes(file.size)"></div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>{{-- end panel kiri --}}

            {{-- ======================================================== --}}
            {{-- PANEL KANAN: Preview Tabel Hasil --}}
            {{-- ======================================================== --}}
            <div class="flex-1 min-w-0 flex flex-col overflow-hidden">

                {{-- Empty state --}}
                <div x-show="results.length === 0 && !isProcessing"
                    class="flex-1 flex flex-col items-center justify-center p-12 text-center bg-base-50/30">
                    <svg class="w-16 h-16 text-base-content/10 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="font-semibold text-base-content/25 text-sm">Preview hasil akan muncul di sini</p>
                    <p class="text-xs text-base-content/20 mt-1">Upload file → Klik Proses → Review → Simpan</p>
                </div>

                {{-- Loading state --}}
                <div x-show="isProcessing" class="flex-1 flex flex-col items-center justify-center p-12" style="display:none">
                    <span class="loading loading-dots loading-lg text-primary mb-3"></span>
                    <p class="text-sm text-base-content/50 font-medium" x-text="progressText"></p>
                    <p class="text-xs text-base-content/30 mt-1" x-text="Math.round(progressPercent) + '% selesai'"></p>
                </div>

                {{-- Tabel Preview --}}
                <div x-show="results.length > 0" class="flex-1 overflow-auto" style="display:none">
                    <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                        <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                            <tr>
                                <th class="w-10 text-center">No</th>
                                <th>Nama File</th>
                                <th>Grup</th>
                                <th>Kode Dist</th>
                                <template x-for="col in resultColumns" :key="col">
                                    <th class="text-right" x-text="col"></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <template x-for="(row, idx) in results" :key="idx">
                                <tr class="hover:bg-base-200/50 transition-colors">
                                    <td class="text-center text-base-content/40 font-mono text-xs" x-text="idx+1"></td>
                                    <td class="font-medium max-w-[220px] truncate" x-text="row.nama_file" :title="row.nama_file"></td>
                                    <td>
                                        <span class="badge badge-sm badge-ghost font-semibold" x-text="row.group_name"></span>
                                    </td>
                                    <td class="font-mono text-xs" x-text="row.kode_dist || '-'"></td>
                                    <template x-for="col in resultColumns" :key="col">
                                        <td class="text-right font-mono text-xs"
                                            x-text="(row.extracted_data||{})[col] !== undefined
                                                ? (typeof (row.extracted_data||{})[col]==='number'
                                                    ? Number((row.extracted_data||{})[col]).toLocaleString('id-ID')
                                                    : (row.extracted_data||{})[col])
                                                : '-'">
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                        {{-- Footer Total (mode rekap) --}}
                        <tfoot class="text-xs font-bold bg-base-300 shadow-[inset_0_1px_0_rgba(0,0,0,0.1)]">
                            <tr>
                                <th colspan="4" class="text-right text-base-content/60 uppercase tracking-wider text-[10px]">TOTAL</th>
                                <template x-for="col in resultColumns" :key="col">
                                    <th class="text-right font-mono"
                                        x-text="Number(results.reduce((s,r)=> s + (typeof((r.extracted_data||{})[col])==='number' ? (r.extracted_data||{})[col] : 0), 0)).toLocaleString('id-ID')">
                                    </th>
                                </template>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>{{-- end panel kanan --}}

        </div>{{-- end body 2-panel --}}

        {{-- Footer Card --}}
        <div class="p-3 md:p-4 border-t border-base-300 shrink-0 bg-base-200/30 flex items-center justify-between gap-3 text-xs text-base-content/50">
            <div class="flex items-center gap-3">
                <span x-show="files.length > 0" x-text="files.length + ' file diupload'"></span>
                <span x-show="results.length > 0" class="text-primary font-semibold" x-text="results.length + ' baris hasil'"></span>
            </div>
            <div>
                <span x-show="lastBatchId" class="text-success font-semibold">
                    ✓ Batch disimpan: <span class="font-mono" x-text="lastBatchId.substring(0,8)+'...'"></span>
                </span>
                <span x-show="!lastBatchId && results.length === 0" class="text-base-content/30">Belum ada proses berjalan</span>
            </div>
        </div>

        {{-- Modal Simpan (Pilih Bulan) --}}
        <div class="modal" :class="{'modal-open': showSaveModal}">
            <div class="modal-box relative p-0 overflow-hidden rounded-2xl max-w-sm">
                <div class="p-4 border-b border-base-200 bg-base-200/50 flex items-center justify-between">
                    <h3 class="font-bold text-lg flex items-center gap-2 text-base-content/80">
                        <x-heroicon-o-calendar-days class="w-5 h-5 text-primary" />
                        Pilih Bulan
                    </h3>
                    <button @click="showSaveModal = false" class="btn btn-sm btn-circle btn-ghost">✕</button>
                </div>
                <div class="p-5 space-y-4">
                    <p class="text-sm text-base-content/70">
                        Pilih bulan untuk data yang akan disimpan. Data pada bulan yang sama akan <b>ditimpa (upsert)</b>.
                    </p>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Bulan Data (YYYY-MM)</span>
                        </label>
                        <input type="month" x-model="selectedMonth" class="input input-bordered w-full input-primary focus:outline-none focus:border-primary/50" />
                    </div>
                </div>
                <div class="p-4 border-t border-base-200 bg-base-50/50 flex justify-end gap-2">
                    <button @click="showSaveModal = false" class="btn btn-ghost normal-case rounded-xl">Batal</button>
                    <button @click="confirmSave()" :disabled="!selectedMonth || isSaving" class="btn btn-primary normal-case rounded-xl">
                        <span x-show="!isSaving">Konfirmasi Simpan</span>
                        <span x-show="isSaving" class="loading loading-spinner loading-xs"></span>
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" @click="showSaveModal = false"></div>
        </div>

    </div>{{-- end main card --}}
</div>{{-- end wrapper --}}

<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('extractorProcess', (wire) => ({
            isDragging:      false,
            isProcessing:    false,
            isSaving:        false,
            files:           [],
            results:         [],
            resultColumns:   [],
            currentBatchId:  '',
            lastBatchId:     '',
            groups:          @js($configs),
            distMapping:     @js($distMapping),
            mode:            'rekap',
            progressText:    '',
            progressPercent: 0,
            errors:          [],
            wire:            wire,
            toastMessage:    '',
            toastType:       'info',
            toastVisible:    false,
            showSaveModal:   false,
            selectedMonth:   new Date().toISOString().slice(0, 7), // Format YYYY-MM
            _toastTimer:     null,

            get unmatchedCount() {
                return this.files.filter(f => !f.matchedGroupId).length;
            },

            showToast(msg, type = 'info', duration = 4500) {
                this.toastMessage = msg;
                this.toastType    = type;
                this.toastVisible = true;
                if (this._toastTimer) clearTimeout(this._toastTimer);
                this._toastTimer  = setTimeout(() => { this.toastVisible = false; }, duration);
            },

            handleDrop(e) {
                this.isDragging = false;
                if (e.dataTransfer.files) this.addFiles(Array.from(e.dataTransfer.files));
            },

            handleFileSelect(e) {
                if (e.target.files) this.addFiles(Array.from(e.target.files));
                e.target.value = '';
            },

            addFiles(newFiles) {
                const allowed    = ['xlsx', 'xls', 'csv', 'xlsm', 'xlsb'];
                const rejected   = [];
                const unmatched  = [];
                const duplicates = [];
                let added = 0;

                newFiles.forEach(f => {
                    const ext = f.name.split('.').pop().toLowerCase();
                    if (!allowed.includes(ext)) { rejected.push(f.name); return; }
                    if (this.files.some(ex => ex.name === f.name)) { duplicates.push(f.name); return; }
                    const matched = this.matchGroup(f.name);
                    this.files.push({ id: Math.random().toString(36).substr(2,9), name: f.name, size: f.size, file: f, matchedGroupId: matched });
                    added++;
                    if (!matched) unmatched.push(f.name);
                });

                if (added > 0 && unmatched.length === 0 && rejected.length === 0) {
                    this.showToast(`${added} file ditambahkan — semua cocok otomatis ✓`, 'success');
                } else if (unmatched.length > 0) {
                    const ok = added - unmatched.length;
                    this.showToast(
                        `${ok > 0 ? ok+' file cocok, ' : ''}${unmatched.length} file tidak cocok keyword — pilih grup manual (ditandai merah).`,
                        'warning', 7000
                    );
                }
                if (rejected.length > 0)  this.showToast(`${rejected.length} file diabaikan (format tidak didukung).`, 'error');
                if (duplicates.length > 0) this.showToast(`${duplicates.length} file duplikat diabaikan.`, 'info');
            },

            removeFile(index)  { this.files.splice(index, 1); },
            clearFiles()       { this.files = []; this.errors = []; this.results = []; this.resultColumns = []; this.lastBatchId = ''; },

            matchGroup(filename) {
                const fn = filename.toLowerCase();
                for (const g of this.groups) {
                    let kw = [];
                    try { kw = typeof g.keywords === 'string' ? JSON.parse(g.keywords) : (g.keywords || []); } catch(e) {}
                    for (const k of kw) { if (k && fn.includes(k.toLowerCase())) return String(g.id); }
                }
                return '';
            },

            formatBytes(b) {
                if (b >= 1024*1024) return (b/1024/1024).toFixed(1)+' MB';
                return (b/1024).toFixed(1)+' KB';
            },

            toNum(v) {
                if (typeof v === 'number') return v;
                if (!v) return 0;
                let s = String(v).replace(/\s/g, '');
                if (s.includes(',') && s.includes('.')) { s = s.replace(/\./g,'').replace(',','.'); }
                else if (s.includes(',') && !s.includes('.')) {
                    const parts = s.split(',');
                    s = parts[parts.length-1].length <= 2 ? s.replace(',','.') : s.replace(/,/g,'');
                }
                const n = parseFloat(s);
                return isNaN(n) ? 0 : n;
            },

            passFilter(rawRow, col, hmap) {
                if (col.type !== 'filtered_sum') return true;
                const idx = hmap[col.filterCol];
                if (idx === undefined) return true;
                const cell = String(rawRow[idx] ?? '').trim().toLowerCase();
                const fv   = String(col.filterVal ?? '').trim().toLowerCase();
                if (col.filterOp === '=')            return cell === fv;
                if (col.filterOp === '!=')           return cell !== fv;
                if (col.filterOp === 'contains')     return cell.includes(fv);
                if (col.filterOp === 'not_contains') return !cell.includes(fv);
                return true;
            },

            getNominal(filename) {
                const t = filename.toLowerCase().replace(/\.[^/.]+$/,'');
                const m = t.match(/(?:rp|rp\.|rp\s)([\d\.,_]+)/i);
                if (m) { const n = parseInt(m[1].replace(/[\.,_]/g,''),10); if (!isNaN(n)) return n; }
                const nums = t.match(/\b\d{5,}\b/g);
                if (nums) { const c = nums.map(x=>parseInt(x,10)).filter(x=>x>1000); if (c.length) return Math.max(...c); }
                return 0;
            },

            getKodeDist(filename) {
                if (!filename) return null;
                const fn = filename.toLowerCase();
                // Cari apakah nama file mengandung keyword (branch_name) dari database
                for (const branch in this.distMapping) {
                    if (fn.includes(branch.toLowerCase())) {
                        return this.distMapping[branch];
                    }
                }
                return null;
            },

            generateUUID() {
                if (typeof crypto !== 'undefined' && crypto.randomUUID) return crypto.randomUUID();
                return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
                    const r = Math.random()*16|0, v = c==='x' ? r : (r&0x3|0x8);
                    return v.toString(16);
                });
            },

            async parseOneFile(fObj) {
                const g = this.groups.find(x => String(x.id) === String(fObj.matchedGroupId));
                if (!g) return { error: 'Grup tidak ditemukan' };

                let buf;
                try { buf = await fObj.file.arrayBuffer(); } catch(e) { return { error: 'Gagal baca file: '+e.message }; }

                let wb;
                try { wb = XLSX.read(buf, { type:'array', cellDates:false, raw:true }); } catch(e) { return { error: 'Format tidak valid: '+e.message }; }

                if (!wb.SheetNames.length) return { error: 'File tidak punya sheet' };

                const raw  = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], { header:1, defval:'', raw:true });
                const hIdx = Math.max(0, (parseInt(g.header_row)||1) - 1);
                if (hIdx >= raw.length) return { error: `Header row ${g.header_row} melebihi jumlah baris` };

                const hmap = {};
                // TRIM dan UPPERCASE header dari file excel
                raw[hIdx].map(h => String(h??'').trim().toUpperCase()).forEach((h,i) => { if(h) hmap[h]=i; });

                const rawRows = raw.slice(hIdx+1).filter(r => r.some(c => c!==''&&c!==null&&c!==undefined));

                let colDefs = [];
                try { colDefs = typeof g.columns==='string' ? JSON.parse(g.columns) : (g.columns||[]); } catch(e) {}
                if (!colDefs?.length) return { error: 'Kolom kosong pada grup: '+g.name };

                const labels = colDefs.map(c => c.label);
                const rows   = rawRows.map(r => colDefs.map(col => { 
                    const srcKey = String(col.source || '').trim().toUpperCase();
                    const i=hmap[srcKey]; 
                    return i!==undefined?r[i]:''; 
                }));

                return { fileName: fObj.name.replace(/\.[^/.]+$/,''), groupId:g.id, groupName:g.name, colDefs, labels, rows, rawRows, hmap };
            },

            computeRekap(res) {
                const out = {};
                res.colDefs.forEach(col => {
                    const lbl = col.label;
                    // TRIM dan UPPERCASE nama kolom dari database saat mencocokkan
                    const srcKey = String(col.source || '').trim().toUpperCase();
                    const si  = res.hmap[srcKey];
                    if (col.type === 'text') {
                        const fv = si!==undefined ? res.rawRows.find(r=>r[si]!==''&&r[si]!==null) : null;
                        out[lbl] = fv ? String(fv[si]) : '-';
                    } else if (col.type === 'filtered_sum') {
                        out[lbl] = si===undefined ? 0 : Math.round(res.rawRows.reduce((s,rr)=>this.passFilter(rr,col,res.hmap)?s+this.toNum(rr[si]):s, 0));
                    } else {
                        out[lbl] = si===undefined ? 0 : Math.round(res.rawRows.reduce((s,rr)=>s+this.toNum(rr[si]), 0));
                    }
                });
                return out;
            },

            async startProcessing() {
                const toProcess = this.files.filter(f => f.matchedGroupId);
                if (!toProcess.length) { this.showToast('Semua file belum dipilih grupnya!', 'error'); return; }
                if (this.unmatchedCount > 0) {
                    if (!confirm(`${this.unmatchedCount} file tanpa grup akan diabaikan. Lanjutkan?`)) return;
                }

                this.isProcessing    = true;
                this.errors          = [];
                this.results         = [];
                this.resultColumns   = [];
                this.progressPercent = 0;
                this.currentBatchId  = this.generateUUID();

                const colSet = new Set();
                const out    = [];

                for (let i = 0; i < toProcess.length; i++) {
                    const fObj = toProcess[i];
                    this.progressPercent = Math.round(((i+1)/toProcess.length)*100);
                    this.progressText    = `(${i+1}/${toProcess.length}) ${fObj.name}`;

                    try {
                        const res = await this.parseOneFile(fObj);
                        if (res.error) { this.errors.push(`${fObj.name}: ${res.error}`); continue; }

                        const rek = this.computeRekap(res);
                        Object.keys(rek).forEach(k => colSet.add(k));
                        out.push({ nama_file:res.fileName, kode_dist:this.getKodeDist(res.fileName), group_name:res.groupName, nominal_surat:this.getNominal(res.fileName), extracted_data:rek });
                        
                    } catch(e) {
                        this.errors.push(`${fObj.name}: ${e.message}`);
                    }
                    await new Promise(r => setTimeout(r, 10));
                }

                this.isProcessing  = false;
                this.results       = out;
                this.resultColumns = Array.from(colSet);

                if (out.length === 0) {
                    this.showToast('Tidak ada data valid yang berhasil diekstrak.', 'error', 6000);
                } else {
                    this.showToast(`${out.length} baris siap direview. Klik Simpan di kanan atas jika sudah sesuai.`, 'success', 6000);
                }
            },

            async confirmSave() {
                if (!this.results.length || !this.selectedMonth) return;
                this.isSaving = true;
                const tanggal = this.selectedMonth + '-01'; // YYYY-MM-01
                try {
                    await this.wire.saveResults(this.currentBatchId, this.mode, this.results, tanggal);
                    this.showSaveModal = false;
                } catch(e) {
                    this.showToast('Gagal menyimpan: ' + e.message, 'error', 8000);
                    this.isSaving = false;
                }
            }
        }));
    });

    window.addEventListener('results-saved', (event) => {
        const detail  = event.detail || {};
        const batchId = detail.batchId || (Array.isArray(detail) ? detail[0]?.batchId : '') || '';
        document.querySelectorAll('[x-data]').forEach(el => {
            if (el._x_dataStack) {
                const d = el._x_dataStack[0];
                if (d && 'lastBatchId' in d) {
                    d.lastBatchId = batchId;
                    d.isSaving    = false;
                    d.showToast('Data berhasil disimpan ke database! ✓', 'success', 6000);
                }
            }
        });
    });
</script>
