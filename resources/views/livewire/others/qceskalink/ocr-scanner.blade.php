<div class="flex-1 min-h-0 min-w-0 flex flex-col gap-3 md:gap-4 lg:gap-6 w-full h-full"
     x-data="ocrScanner($wire)"
     x-init="distMapping = {{ json_encode($distMapping) }}">
    
    @include('livewire.others.qceskalink._tabs')

    {{-- Main Card --}}
    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        
        {{-- Header Card & Actions --}}
        <div class="p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-base-200/30">
            <div class="shrink-0 w-full sm:w-auto">
                <h2 class="text-base md:text-lg font-bold flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                        <x-heroicon-s-camera class="w-5 h-5" />
                    </div>
                    Scan OCR Surat
                </h2>
                <p class="text-[10px] md:text-xs text-base-content/60 font-semibold uppercase tracking-wider mt-0.5">
                    Upload gambar/PDF surat, proses OCR, lalu simpan ke database
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <span x-show="verifiedCount > 0" class="badge badge-primary badge-sm font-semibold" x-text="verifiedCount + ' hasil'" style="display:none"></span>
                
                <button x-show="files.length > 0" @click="resetAll()" class="btn btn-sm btn-ghost text-error rounded-xl normal-case gap-1" style="display:none">
                    <x-heroicon-s-trash class="w-4 h-4" /> Hapus Semua
                </button>
                
                <button x-show="verifiedCount > 0 && !isSaving" @click="showSaveModal = true" class="btn btn-sm btn-success rounded-xl normal-case gap-2 shadow-sm shadow-success/20 text-white" style="display:none">
                    <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                    Simpan <span x-text="verifiedCount"></span> Data
                </button>
                <button x-show="isSaving" class="btn btn-sm btn-success rounded-xl normal-case text-white" style="display:none" disabled>
                    <span class="loading loading-spinner loading-xs"></span> Menyimpan...
                </button>
            </div>
        </div>

        {{-- Body Card: 2 panel --}}
        <div class="flex-1 min-h-0 flex overflow-hidden">
            
            {{-- PANEL KIRI: Upload + Eksekusi + List File --}}
            <div class="w-72 xl:w-80 shrink-0 border-r border-base-300 flex flex-col overflow-hidden bg-base-50/50">
                <div class="p-4 border-b border-base-300 shrink-0 space-y-3">
                    <div class="border-2 border-dashed rounded-xl p-4 flex flex-col items-center justify-center text-center cursor-pointer transition-all"
                         :class="isDragging ? 'border-primary bg-primary/10 scale-[1.02] border-primary' : 'border-base-300 hover:border-primary/50 hover:bg-base-200/30'"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="handleDrop($event)"
                         @click="$refs.fileInput.click()">
                        <input type="file" multiple accept=".pdf,.png,.jpg,.jpeg" class="hidden" @change="handleFileSelect" x-ref="fileInput">
                        <div class="p-2 bg-base-200 rounded-full text-primary mb-2 pointer-events-none">
                            <x-heroicon-o-document-plus class="w-6 h-6" />
                        </div>
                        <p class="font-bold text-xs text-base-content/80 pointer-events-none">Pilih atau Tarik File</p>
                        <p class="text-[10px] text-base-content/50 pointer-events-none">PDF, JPG, PNG</p>
                    </div>

                    <button @click="startProcessing()" :disabled="isProcessing || files.filter(f => f.status === 'idle' || f.status === 'error').length === 0" class="btn btn-primary btn-sm w-full normal-case rounded-xl shadow-lg shadow-primary/20">
                        <x-heroicon-s-play class="w-4 h-4 mr-1" x-show="!isProcessing" />
                        <span x-show="!isProcessing">Proses OCR</span>
                        <span x-show="isProcessing" class="loading loading-spinner loading-xs"></span>
                        <span x-show="isProcessing" class="ml-1" x-text="progressPercent + '%'"></span>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-3 space-y-2">
                    <template x-for="(file, index) in files" :key="file.id">
                        <div class="flex items-center gap-2 p-2 rounded-lg border transition-all bg-base-100 shadow-sm"
                             :class="file.kode_dist ? 'border-success/30 hover:border-success' : 'border-error/30 hover:border-error'">
                            <div class="w-8 h-8 rounded bg-base-200 flex items-center justify-center overflow-hidden shrink-0">
                                <img x-show="file.thumbnail" :src="file.thumbnail" class="w-full h-full object-cover" />
                                <x-heroicon-o-document x-show="!file.thumbnail" class="w-5 h-5 text-base-content/40" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-semibold truncate text-base-content/80" x-text="file.name" :title="file.name"></p>
                                <p class="text-[9px] mt-0.5 flex justify-between uppercase tracking-wider">
                                    <span x-show="file.kode_dist" class="text-success font-bold" x-text="file.kode_dist"></span>
                                    <span x-show="!file.kode_dist" class="text-error font-bold">Unrecognized</span>
                                    <span x-show="file.status === 'processing'" class="text-primary font-bold animate-pulse">Wait...</span>
                                    <span x-show="file.status === 'done'" class="text-success font-bold">Done</span>
                                    <span x-show="file.status === 'error'" class="text-error font-bold">Err</span>
                                </p>
                            </div>
                            <button @click="removeFile(index)" class="btn btn-ghost btn-xs btn-square text-error shrink-0 h-6 w-6 min-h-0">
                                <x-heroicon-o-x-mark class="w-3 h-3" />
                            </button>
                        </div>
                    </template>
                    <div x-show="files.length === 0" class="text-center p-6 text-xs text-base-content/40 italic">
                        Belum ada file diunggah.
                    </div>
                </div>
            </div>

            {{-- PANEL KANAN: Preview Tabel Hasil --}}
            <div class="flex-1 min-w-0 flex flex-col overflow-hidden bg-base-100 relative">
                
                {{-- Empty state --}}
                <div x-show="verifiedCount === 0" class="absolute inset-0 flex items-center justify-center text-center p-8 bg-base-100/50 z-10">
                    <div class="text-base-content/40 max-w-xs">
                        <x-heroicon-o-table-cells class="w-16 h-16 mx-auto mb-4 opacity-50" />
                        <h3 class="text-sm font-bold">Hasil OCR Kosong</h3>
                        <p class="text-xs mt-1 leading-relaxed">Pilih file di panel kiri dan klik proses untuk memulai ekstraksi.</p>
                    </div>
                </div>

                {{-- Table container --}}
                <div class="flex-1 overflow-auto" x-show="verifiedCount > 0" style="display:none">
                    <table class="table table-sm table-zebra table-pin-rows w-full whitespace-nowrap">
                        <thead class="text-xs uppercase tracking-wider bg-base-300 text-base-content/80 border-b border-base-300 shadow-sm">
                            <tr>
                                <th class="w-10 text-center">No</th>
                                <th>Nama File</th>
                                <th class="w-24">Tipe</th>
                                <th class="w-32">Distributor</th>
                                <th class="w-56 text-right">Nominal OCR</th>
                                <th class="w-20 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <template x-for="(file, index) in files.filter(f => f.status === 'done')" :key="file.id">
                                <tr class="hover:bg-base-200/50 transition-colors">
                                    <td class="text-center text-base-content/40 font-mono text-xs" x-text="index + 1"></td>
                                    <td class="font-medium max-w-[280px] truncate" x-text="file.name" :title="file.name"></td>
                                    <td>
                                        <span class="badge badge-sm badge-ghost font-semibold uppercase" x-text="file.ext"></span>
                                    </td>
                                    <td class="font-mono text-xs" :class="file.kode_dist ? 'text-success font-bold' : 'text-error font-bold'" x-text="file.kode_dist || '-'"></td>
                                    <td class="text-right">
                                        <div class="join w-full justify-end">
                                            <span class="join-item bg-base-200 px-3 flex items-center text-xs font-semibold border border-base-300 text-base-content/70">Rp</span>
                                            <input type="number" x-model="file.nominal" class="input input-sm input-bordered join-item w-full max-w-[160px] text-right font-bold focus:border-primary focus:outline-none" />
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button @click="openPreview(file)" class="btn btn-sm btn-ghost text-primary normal-case font-bold h-8 min-h-0">Lihat</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Save Modal --}}
    <div x-show="showSaveModal" class="relative z-50" style="display:none">
        <div class="fixed inset-0 bg-base-300/60 backdrop-blur-sm z-40 transition-opacity"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="modal-box relative p-0 overflow-hidden rounded-2xl max-w-sm" @click.stop>
                <div class="p-4 border-b border-base-200 bg-base-200/50 flex items-center justify-between">
                    <h3 class="font-bold text-lg flex items-center gap-2 text-base-content/80">
                        <x-heroicon-o-calendar-days class="w-5 h-5 text-primary" />
                        Pilih Bulan Surat
                    </h3>
                    <button @click="showSaveModal = false" class="btn btn-sm btn-circle btn-ghost">✕</button>
                </div>
                <div class="p-5 space-y-4">
                    <p class="text-sm text-base-content/70 leading-relaxed">
                        Data akan disimpan dan dikaitkan ke bulan yang Anda pilih.
                    </p>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Bulan Data (YYYY-MM)</span>
                        </label>
                        <input type="month" x-model="selectedMonth" class="input input-bordered w-full focus:outline-none focus:border-primary" />
                    </div>
                    
                    <div class="form-control bg-base-100 border border-base-200 rounded-lg p-2 mt-4 space-y-1">
                        <label class="label cursor-pointer justify-start gap-3 p-1">
                            <input type="checkbox" class="checkbox checkbox-sm checkbox-primary rounded" x-model="optSaveNominal" />
                            <span class="label-text font-medium">Simpan Data Angka/Nominal</span>
                        </label>
                        <label class="label cursor-pointer justify-start gap-3 p-1">
                            <input type="checkbox" class="checkbox checkbox-sm checkbox-primary rounded" x-model="optSaveFile" />
                            <span class="label-text font-medium">Upload & Simpan File Fisik Asli</span>
                        </label>
                    </div>
                </div>
                <div class="p-4 border-t border-base-200 bg-base-50/50 flex justify-end gap-2">
                    <button @click="showSaveModal = false" class="btn btn-ghost btn-sm normal-case rounded-xl px-4">Batal</button>
                    <button @click="confirmSave()" :disabled="!selectedMonth || (!optSaveNominal && !optSaveFile) || isSaving" class="btn btn-primary btn-sm normal-case rounded-xl px-6">
                        <span x-show="!isSaving">Simpan Data</span>
                        <span x-show="isSaving" class="loading loading-spinner loading-xs"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Preview Modal --}}
    <div x-show="previewFile !== null" class="relative z-50" style="display:none">
        <div class="fixed inset-0 bg-base-300/80 backdrop-blur-sm z-40"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="modal-box w-11/12 max-w-5xl h-[90vh] flex flex-col p-0 rounded-2xl overflow-hidden shadow-2xl" @click.stop>
                <div class="p-3 border-b border-base-200 bg-base-100 flex items-center justify-between shrink-0">
                    <h3 class="font-bold text-sm flex items-center gap-2 truncate pr-4 text-base-content/80">
                        <x-heroicon-o-photo class="w-5 h-5 text-primary" />
                        <span x-text="previewFile ? previewFile.name : ''"></span>
                    </h3>
                    <div class="flex items-center gap-1 shrink-0">
                        <button @click="zoomOut()" class="btn btn-xs btn-square btn-ghost"><x-heroicon-o-minus class="w-4 h-4"/></button>
                        <button @click="zoomIn()" class="btn btn-xs btn-square btn-ghost"><x-heroicon-o-plus class="w-4 h-4"/></button>
                        <button @click="previewFile = null" class="btn btn-xs btn-square btn-ghost text-error ml-2"><x-heroicon-o-x-mark class="w-5 h-5"/></button>
                    </div>
                </div>
                <div class="flex-1 flex flex-col overflow-hidden bg-base-200">
                    <div class="bg-base-100 border-b border-base-200 p-2.5 shrink-0 shadow-sm flex justify-between items-center z-10 relative">
                        <h4 class="font-bold text-xs text-primary flex items-center gap-1">
                            <x-heroicon-s-document-text class="w-3.5 h-3.5" />
                            Teks Hasil Bacaan AI (Raw Text)
                        </h4>
                        <div class="badge badge-primary badge-outline font-bold py-2 text-xs" x-show="previewFile && previewFile.nominal">
                            Nominal Terbaca: Rp <span class="ml-1" x-text="previewFile.nominal.toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                    
                    <div class="flex-1 overflow-auto p-4 flex items-start justify-center relative">
                        <canvas id="previewCanvas" class="max-w-none shadow-md transition-transform duration-200 origin-top bg-white" :style="'transform: scale(' + zoomLevel + ')'"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>

<script>
    document.addEventListener('alpine:init', () => {
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        Alpine.data('ocrScanner', (wire) => ({
            distMapping: {},
            files: [],
            isDragging: false,
            isProcessing: false,
            isSaving: false,
            showSaveModal: false,
            selectedMonth: new Date().toISOString().slice(0, 7),
            progressPercent: 0,
            optSaveNominal: true,
            optSaveFile: true,
            previewFile: null,
            zoomLevel: 1,

            get verifiedCount() {
                return this.files.filter(f => f.status === 'done').length;
            },

            openPreview(file) {
                this.previewFile = file;
                this.zoomLevel = 1;
                this.$nextTick(() => {
                    const canvas = document.getElementById('previewCanvas');
                    if (canvas && file.imageDataUrl) {
                        const ctx = canvas.getContext('2d');
                        const img = new Image();
                        img.onload = () => {
                            canvas.width = img.width;
                            canvas.height = img.height;
                            ctx.drawImage(img, 0, 0);
                        };
                        img.src = file.imageDataUrl;
                    }
                });
            },

            zoomIn() { this.zoomLevel = Math.min(this.zoomLevel + 0.2, 3); },
            zoomOut() { this.zoomLevel = Math.max(this.zoomLevel - 0.2, 0.5); },

            getKodeDist(filename) {
                if (!filename) return null;
                const fn = filename.toLowerCase();
                for (const branch in this.distMapping) {
                    if (fn.includes(branch.toLowerCase())) {
                        return this.distMapping[branch];
                    }
                }
                return null;
            },

            handleDrop(e) {
                this.isDragging = false;
                if (e.dataTransfer.files) this.addFiles(Array.from(e.dataTransfer.files));
            },

            handleFileSelect(e) {
                if (e.target.files) this.addFiles(Array.from(e.target.files));
                e.target.value = '';
            },

            addFiles(fileArray) {
                fileArray.forEach(file => {
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (!['pdf', 'png', 'jpg', 'jpeg'].includes(ext)) return;
                    
                    const fObj = {
                        id: 'f_' + Math.random().toString(36).substr(2,9),
                        file: file,
                        name: file.name,
                        ext: ext,
                        status: 'idle',
                        thumbnail: null,
                        imageDataUrl: null,
                        raw_text: '',
                        nominal: 0,
                        kode_dist: this.getKodeDist(file.name)
                    };
                    
                    if (['png', 'jpg', 'jpeg'].includes(ext)) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            fObj.thumbnail = e.target.result;
                            fObj.imageDataUrl = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    } else if (ext === 'pdf') {
                        this.renderPdfToCanvas(fObj);
                    }
                    
                    this.files.push(fObj);
                });
            },
            
            removeFile(index) {
                this.files.splice(index, 1);
            },
            
            resetAll() {
                if(confirm('Hapus semua file?')) {
                    this.files = [];
                }
            },

            async renderPdfToCanvas(fObj) {
                try {
                    const arrayBuffer = await fObj.file.arrayBuffer();
                    const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
                    const page = await pdf.getPage(1);
                    
                    const viewport = page.getViewport({ scale: 1.5 });
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    
                    await page.render({ canvasContext: ctx, viewport: viewport }).promise;
                    
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
                    fObj.thumbnail = dataUrl;
                    fObj.imageDataUrl = dataUrl;
                } catch (e) {
                    console.error("PDF Render error:", e);
                    fObj.status = 'error';
                    fObj.raw_text = 'Gagal membaca PDF: ' + e.message;
                }
            },

            extractNominalFinal(text) {
                const pattern = /(NOMIN\w*)[^\dRp]{0,20}(Rp|R\.?|RP)?[^\d]{0,10}([\d.,]{6,})/i;
                const match = text.match(pattern);
            
                if (match && match[3]) {
                    const angka = match[3].replace(/[.,]/g, '');
                    return parseInt(angka, 10);
                }
            
                const numbers = text.match(/[\d.,]{6,}/g);
                if (!numbers) return 0;
                
                const cleaned = numbers.map(n => parseInt(n.replace(/[.,]/g, ''), 10));
                return Math.max(...cleaned);
            },

            async startProcessing() {
                const toProcess = this.files.filter(f => f.status === 'idle' || f.status === 'error');
                if (toProcess.length === 0) return;
                
                this.isProcessing = true;
                this.progressPercent = 0;
                
                for (let i = 0; i < toProcess.length; i++) {
                    const fObj = toProcess[i];
                    fObj.status = 'processing';
                    
                    try {
                        if (!fObj.imageDataUrl) {
                            let waitTime = 0;
                            while(!fObj.imageDataUrl && waitTime < 5000) {
                                await new Promise(r => setTimeout(r, 200));
                                waitTime += 200;
                            }
                            if (!fObj.imageDataUrl) throw new Error("Gagal merender gambar.");
                        }

                        const result = await Tesseract.recognize(fObj.imageDataUrl, 'ind+eng', {
                            logger: m => {
                                if (m.status === 'recognizing text') {
                                    this.progressPercent = Math.round(m.progress * 100);
                                }
                            }
                        });
                        
                        fObj.raw_text = result.data.text;
                        fObj.nominal = this.extractNominalFinal(result.data.text);
                        fObj.status = 'done';
                    } catch (e) {
                        console.error("OCR Error:", e);
                        fObj.status = 'error';
                        fObj.raw_text = "ERROR: " + e.message;
                    }
                }
                
                this.isProcessing = false;
                this.progressPercent = 0;
            },

            async confirmSave() {
                const verifiedFiles = this.files.filter(f => f.status === 'done');
                if (verifiedFiles.length === 0 || !this.selectedMonth) return;
                
                this.isSaving = true;
                const tanggal = this.selectedMonth + '-01';
                
                const dataToSave = verifiedFiles.map(f => ({
                    file_name: f.name,
                    distributor_code: f.kode_dist,
                    raw_text: f.raw_text,
                    nominal_extracted: parseFloat(f.nominal) || 0
                }));
                
                const execSave = async () => {
                    try {
                        await wire.saveOcrResults(dataToSave, tanggal, this.optSaveNominal, this.optSaveFile);
                        this.showSaveModal = false;
                        alert('Data berhasil disimpan!');
                    } catch(e) {
                        alert('Gagal menyimpan data database: ' + e.message);
                    } finally {
                        this.isSaving = false;
                    }
                };

                try {
                    if (this.optSaveFile) {
                        const filesToUpload = verifiedFiles.map(f => f.file);
                        @this.uploadMultiple('uploadedFiles', filesToUpload, async (uploadedFilenames) => {
                            await execSave();
                        }, (error) => {
                            alert('Gagal meng-upload file fisik: ' + error);
                            this.isSaving = false;
                        });
                    } else {
                        await execSave();
                    }
                } catch(e) {
                    alert('Gagal memproses upload: ' + e.message);
                    this.isSaving = false;
                }
            }
        }));
    });
</script>
