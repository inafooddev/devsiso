<div class="flex-1 flex flex-col w-full h-full min-h-0" x-data="{ sampleOpen: false }">
    <x-slot name="title">Tambah Config Sales Invoice Distributor</x-slot>

    <div class="bg-base-100 rounded-xl shadow-xl border border-base-300 flex-1 min-h-0 min-w-0 flex flex-col overflow-hidden">
        <form wire:submit.prevent="save" class="flex-1 flex flex-col min-h-0">

            {{-- HEADER ACTIONS & TITLE --}}
            <div class="bg-base-200/50 p-3 md:p-4 lg:p-5 border-b border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 shadow-sm z-10">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base md:text-lg font-bold text-base-content">Tambah Konfigurasi Pemetaan</h2>
                        <span class="badge badge-primary badge-sm font-semibold">Baru</span>
                    </div>
                    <p class="text-xs text-base-content/60 mt-0.5">Petakan format file Excel invoice distributor ke standar database sistem</p>
                </div>

                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="button" 
                            wire:click="openCopyModal" 
                            class="btn btn-sm btn-outline border-base-300 hover:bg-base-300 gap-1.5 normal-case">
                        <x-heroicon-o-document-duplicate class="w-4 h-4 text-primary" />
                        <span>Salin dari Config Lain</span>
                    </button>

                    <x-ui.action-button
                        type="default"
                        label="Kembali"
                        icon="arrow-left"
                        href="{{ route('sales-configs.index') }}"
                        class="btn-sm btn-ghost border border-base-300 hover:bg-base-300/50"
                    />

                    <button type="submit" 
                            class="btn btn-sm btn-primary shadow-sm shadow-primary/20 gap-1.5 normal-case"
                            wire:loading.attr="disabled">
                        <x-heroicon-o-check class="w-4 h-4" />
                        <span wire:loading.remove wire:target="save">Simpan Konfigurasi</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                    </button>
                </div>
            </div>

            {{-- SCROLLABLE FORM BODY --}}
            <div class="flex-1 overflow-y-auto custom-scrollbar p-3 md:p-5 lg:p-6 space-y-4 md:space-y-6">
                <div class="max-w-6xl mx-auto space-y-4 md:space-y-6">

                    {{-- FLASH NOTIFICATIONS --}}
                    @if (session()->has('message'))
                        <x-ui.notif type="success" dismissible>
                            {{ session('message') }}
                        </x-ui.notif>
                    @endif

                    @if (session()->has('copy_success'))
                        <div class="alert alert-success shadow-sm rounded-xl py-2.5 px-4 text-xs md:text-sm">
                            <x-heroicon-o-check-circle class="w-5 h-5 flex-shrink-0" />
                            <span>{{ session('copy_success') }}</span>
                        </div>
                    @endif

                    @if (session()->has('sample_success'))
                        <div class="alert alert-info shadow-sm rounded-xl py-2.5 px-4 text-xs md:text-sm">
                            <x-heroicon-o-sparkles class="w-5 h-5 flex-shrink-0" />
                            <span>{{ session('sample_success') }}</span>
                        </div>
                    @endif

                    @if (session()->has('sample_error'))
                        <div class="alert alert-error shadow-sm rounded-xl py-2.5 px-4 text-xs md:text-sm">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 flex-shrink-0" />
                            <span>{{ session('sample_error') }}</span>
                        </div>
                    @endif

                    @if (session()->has('info_message'))
                        <div class="alert alert-warning shadow-sm rounded-xl py-2.5 px-4 text-xs md:text-sm">
                            <x-heroicon-o-information-circle class="w-5 h-5 flex-shrink-0" />
                            <span>{{ session('info_message') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="p-4 bg-error/10 border border-error/20 text-error rounded-xl flex items-start space-x-3 text-xs md:text-sm">
                            <x-heroicon-s-exclamation-circle class="w-5 h-5 mt-0.5 flex-shrink-0" />
                            <div class="flex-1">
                                <strong class="font-bold block mb-1">Periksa kembali data konfigurasi Anda:</strong>
                                <ul class="list-disc list-inside space-y-0.5 opacity-90">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{-- SECTION 1: PILIH DISTRIBUTOR --}}
                    <div class="bg-base-200/40 border border-base-300 rounded-xl p-4 md:p-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                            <label class="text-sm font-bold text-base-content flex items-center gap-1.5">
                                <x-heroicon-o-building-storefront class="w-4 h-4 text-primary" />
                                1. Pilih Distributor <span class="text-error">*</span>
                            </label>
                            <span class="text-xs text-base-content/50">Hanya menampilkan distributor yang belum memiliki konfigurasi</span>
                        </div>

                        <div x-data="{ open: false }" @click.away="open = false" class="relative max-w-xl">
                            <div class="relative">
                                <input type="text"
                                       wire:model.live.debounce.300ms="distributorSearch"
                                       @focus="open = true"
                                       id="distributor_search"
                                       placeholder="Ketik nama atau kode distributor..."
                                       class="input input-sm md:input-md w-full bg-base-100 border border-base-300 text-base-content text-xs md:text-sm rounded-xl focus:ring-2 focus:ring-primary/50 focus:border-transparent transition-all placeholder:text-base-content/40"
                                       x-show="!$wire.distributor_code">
                                
                                <div x-show="$wire.distributor_code" class="w-full flex items-center justify-between px-4 py-2.5 bg-primary/10 border border-primary/20 rounded-xl text-xs md:text-sm font-semibold text-primary">
                                    <div class="flex items-center gap-2 truncate">
                                        <x-heroicon-o-check-badge class="w-5 h-5 flex-shrink-0" />
                                        <span class="truncate" x-text="$wire.selectedDistributorName"></span>
                                        <span class="font-mono text-xs opacity-70">([Kode: <span x-text="$wire.distributor_code"></span>])</span>
                                    </div>
                                    <button type="button" 
                                            @click="$wire.set('distributor_code', ''); $wire.set('selectedDistributorName', '')" 
                                            class="btn btn-ghost btn-xs btn-circle text-error hover:bg-error/10" 
                                            title="Ganti distributor">
                                        <x-heroicon-s-x-mark class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                            
                            {{-- Autocomplete dropdown --}}
                            <div x-show="open && $wire.distributorSearch.length >= 2 && !$wire.distributor_code"
                                 x-transition
                                 class="absolute z-30 w-full mt-1.5 bg-base-100 border border-base-300 rounded-xl shadow-2xl max-h-60 overflow-y-auto custom-scrollbar">
                                @if($distributors->isNotEmpty())
                                    <div class="py-1 divide-y divide-base-200">
                                        @foreach($distributors as $distributor)
                                            <div wire:click="selectDistributor('{{ $distributor->distributor_code }}', '{{ addslashes($distributor->distributor_name) }}')" 
                                                 @click="open = false"
                                                 class="cursor-pointer px-4 py-2 text-xs md:text-sm text-base-content/80 hover:text-primary hover:bg-primary/10 transition-colors flex justify-between items-center">
                                                <span class="font-medium truncate">{{ $distributor->distributor_name }}</span> 
                                                <span class="font-mono text-xs opacity-60 ml-2 bg-base-200 px-2 py-0.5 rounded">{{ $distributor->distributor_code }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-4 text-xs md:text-sm text-base-content/50 text-center">Tidak ada distributor ditemukan.</div>
                                @endif
                            </div>
                        </div>
                        @error('distributor_code') <p class="mt-2 text-xs text-error font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- SECTION 2: SMART HELPER (UPLOAD SAMPLE EXCEL & SALIN DARI LAIN) --}}
                    <div class="bg-base-200/30 border border-base-300 rounded-xl overflow-hidden">
                        <div class="p-3 md:p-4 bg-base-200/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-base-300">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-sparkles class="w-5 h-5 text-warning" />
                                <div>
                                    <h3 class="text-xs md:text-sm font-bold text-base-content">Bantuan Pemetaan Cepat (Opsional)</h3>
                                    <p class="text-[11px] md:text-xs text-base-content/60">Upload file contoh Excel invoice distributor untuk deteksi header & contoh isian data otomatis</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                                @if(!empty($detectedHeaders))
                                    <button type="button" 
                                            wire:click="autoMatchFromSample"
                                            class="btn btn-xs md:btn-sm btn-primary gap-1 normal-case"
                                            title="Cocokkan header secara otomatis">
                                        <x-heroicon-o-sparkles class="w-3.5 h-3.5" />
                                        <span>Auto-Match Kolom</span>
                                    </button>
                                    <button type="button" 
                                            wire:click="removeSampleFile"
                                            class="btn btn-xs md:btn-sm btn-ghost text-error gap-1 normal-case"
                                            title="Hapus file sample">
                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                        <span>Reset Sample</span>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="p-4">
                            @if(empty($detectedHeaders))
                                <div class="flex flex-col sm:flex-row items-center gap-3">
                                    <div class="flex-1 w-full">
                                        <input type="file" 
                                               wire:model="sampleFile" 
                                               accept=".xlsx,.xls,.csv"
                                               id="sample_file_input"
                                               class="file-input file-input-bordered file-input-sm w-full bg-base-100 text-xs text-base-content" />
                                    </div>
                                    <div wire:loading wire:target="sampleFile" class="flex items-center gap-2 text-xs text-primary font-medium shrink-0">
                                        <span class="loading loading-spinner loading-xs"></span>
                                        <span>Membaca header dan contoh data...</span>
                                    </div>
                                </div>
                                <p class="text-[11px] text-base-content/50 mt-1.5">
                                    Format: <code>.xlsx</code>, <code>.xls</code>, atau <code>.csv</code> (Baris 1 dibaca sebagai header, Baris 2 dibaca sebagai contoh data isian).
                                </p>
                            @else
                                <div class="flex flex-wrap items-center justify-between gap-2 p-3 bg-base-100 rounded-lg border border-base-300">
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-document-chart-bar class="w-5 h-5 text-success" />
                                        <div>
                                            <div class="text-xs md:text-sm font-bold text-base-content">{{ $sampleFileName }}</div>
                                            <div class="text-[11px] text-base-content/60">
                                                Terdeteksi <span class="font-bold text-success">{{ count($detectedHeaders) }}</span> kolom header dan contoh data isian
                                            </div>
                                        </div>
                                    </div>
                                    <div class="badge badge-success badge-sm font-semibold gap-1">
                                        <x-heroicon-o-check class="w-3 h-3" /> Siap Dipetakan
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- SECTION 3: STATUS KELENGKAPAN & CHECKLIST (REAL-TIME FEEDBACK) --}}
                    @php
                        $progress = $this->progressStats;
                    @endphp
                    <div class="bg-base-200/40 border border-base-300 rounded-xl p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
                            <div>
                                <span class="text-xs font-bold text-base-content uppercase tracking-wider">Status Validasi Konfigurasi</span>
                                <div class="text-xs text-base-content/60 mt-0.5">
                                    Kolom wajib terisi: <span class="font-bold text-base-content">{{ $progress['required_completed'] }} / {{ $progress['required_total'] }}</span>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                {{-- Badge Status Qty --}}
                                @if($progress['is_qty_valid'])
                                    <span class="badge badge-success badge-sm text-white gap-1 font-medium">
                                        <x-heroicon-o-check class="w-3.5 h-3.5" /> Qty Terpenuhi
                                    </span>
                                @else
                                    <span class="badge badge-error badge-sm text-white gap-1 font-medium">
                                        <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5" /> Minimal 1 Qty Diisi
                                    </span>
                                @endif

                                {{-- Badge Status Satuan --}}
                                @if(!$progress['is_unit_valid'])
                                    <span class="badge badge-error badge-sm text-white gap-1 font-medium">
                                        <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5" /> Satuan (Unit) Wajib
                                    </span>
                                @endif

                                {{-- Status Persentase --}}
                                <span class="badge {{ $progress['is_all_valid'] ? 'badge-success text-white' : 'badge-ghost text-base-content/70' }} badge-sm font-mono font-bold">
                                    {{ $progress['percent'] }}% Lengkap
                                </span>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="w-full bg-base-300 h-2 rounded-full overflow-hidden">
                            <div class="h-full transition-all duration-300 {{ $progress['is_all_valid'] ? 'bg-success' : 'bg-primary' }}"
                                 style="width: {{ $progress['percent'] }}%"></div>
                        </div>

                        @if(!empty($progress['missing_labels']))
                            <div class="mt-2.5 text-[11px] text-base-content/60 flex flex-wrap items-center gap-1.5">
                                <span class="font-semibold text-warning">Belum terpetakan:</span>
                                @foreach($progress['missing_labels'] as $missing)
                                    <span class="badge badge-xs bg-base-300 text-base-content/80">{{ $missing }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- SECTION 4: AREA TABEL PEMETAAN KOLOM --}}
                    <div class="bg-base-100 rounded-xl border border-base-300 shadow-sm overflow-hidden">
                        
                        {{-- TAB NAVIGATION & FILTER TOOLBAR --}}
                        <div class="p-3 md:p-4 bg-base-200/50 border-b border-base-300 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-3">
                            {{-- Tab Kategori --}}
                            <div class="flex flex-wrap items-center gap-1 w-full lg:w-auto">
                                <button type="button" 
                                        wire:click="$set('activeTab', 'all')"
                                        class="btn btn-xs md:btn-sm rounded-lg normal-case {{ $activeTab === 'all' ? 'btn-primary font-bold shadow-sm' : 'btn-ghost text-base-content/70' }}">
                                    Semua (31)
                                </button>
                                
                                @php $catStats = $this->categoryStats; @endphp
                                @foreach($categories as $catKey => $cat)
                                    <button type="button" 
                                            wire:click="$set('activeTab', '{{ $catKey }}')"
                                            class="btn btn-xs md:btn-sm rounded-lg normal-case flex items-center gap-1 {{ $activeTab === $catKey ? 'btn-primary font-bold shadow-sm' : 'btn-ghost text-base-content/70' }}">
                                        <span>{{ $cat['title'] }}</span>
                                        @if(isset($catStats[$catKey]))
                                            <span class="badge badge-xs {{ $catStats[$catKey]['is_complete'] ? 'badge-success text-white' : 'badge-ghost text-base-content/60' }} font-mono">
                                                {{ $catStats[$catKey]['filled'] }}/{{ $catStats[$catKey]['total'] }}
                                            </span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>

                            {{-- Filter Tools: Search & Only Filled Toggle --}}
                            <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto justify-start lg:justify-end">
                                <div class="relative w-full sm:w-56">
                                    <input type="text" 
                                           wire:model.live.debounce.200ms="searchColumn"
                                           placeholder="Cari nama kolom..."
                                           class="input input-xs md:input-sm w-full bg-base-100 border border-base-300 rounded-lg pl-8 text-xs text-base-content" />
                                    <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-base-content/40" />
                                    @if($searchColumn)
                                        <button type="button" wire:click="$set('searchColumn', '')" class="absolute right-2 top-1/2 -translate-y-1/2 text-base-content/40 hover:text-base-content">
                                            <x-heroicon-s-x-mark class="w-3.5 h-3.5" />
                                        </button>
                                    @endif
                                </div>

                                <label class="label cursor-pointer gap-2 py-0">
                                    <input type="checkbox" wire:model.live="showOnlyFilled" class="checkbox checkbox-xs checkbox-primary rounded" />
                                    <span class="label-text text-xs text-base-content/70 font-medium">Hanya Kolom Terisi</span>
                                </label>

                                <button type="button" 
                                        wire:click="clearAllFields"
                                        wire:confirm="Yakin ingin mengosongkan seluruh pemetaan kolom?"
                                        class="btn btn-xs btn-ghost text-error/80 hover:text-error hover:bg-error/10 normal-case"
                                        title="Kosongkan seluruh isian kolom">
                                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                                    <span>Reset Semua</span>
                                </button>
                            </div>
                        </div>

                        {{-- TABEL PEMETAAN --}}
                        <div class="overflow-x-auto">
                            <table class="table table-sm table-zebra w-full whitespace-nowrap text-xs md:text-sm">
                                <thead class="bg-base-200/80 text-base-content/70 uppercase text-[11px] font-bold border-b border-base-300">
                                    <tr>
                                        <th class="w-10 text-center">#</th>
                                        <th class="w-1/4">Kolom Database (Standar)</th>
                                        <th class="w-2/5">Nama Header di File Distributor (Excel)</th>
                                        <th class="w-1/4">Posisi Kolom</th>
                                        <th class="w-12 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $filteredFields = $this->filteredFields;
                                    @endphp

                                    @forelse($filteredFields as $field => $alias)
                                        @php
                                            $isRequired = in_array($field, $requiredMappingFields);
                                            $isQtyField = in_array($field, $qtyFields);
                                            $headerVal = $config[$field]['header_inv_dist'] ?? '';
                                            $indexVal = $config[$field]['index'] ?? '';
                                            $isRowFilled = !empty($headerVal) || ($indexVal !== '' && $indexVal !== null);
                                            $letter = $this->getColumnLetter($indexVal);
                                        @endphp
                                        <tr class="hover:bg-base-200/40 transition-colors {{ $isRowFilled ? 'bg-base-100' : 'opacity-85' }}">
                                            {{-- No / Indicator --}}
                                            <td class="text-center font-mono text-xs text-base-content/40">
                                                {{ $loop->iteration }}
                                            </td>

                                            {{-- Kolom Database --}}
                                            <td>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-base-content">{{ $alias }}</span>
                                                    @if($isRequired)
                                                        <span class="badge badge-error badge-xs text-white font-bold" title="Wajib diisi">* Wajib</span>
                                                    @elseif($field === 'distributor_code')
                                                        <span class="badge badge-ghost badge-xs text-primary font-semibold" title="Opsional: Jika kolom tidak ada di file Excel, kode cabang otomatis diambil saat import">Otomatis dari Import</span>
                                                    @elseif($isQtyField)
                                                        <span class="badge badge-warning badge-xs font-semibold" title="Minimal salah satu Qty harus diisi">Qty</span>
                                                    @endif
                                                </div>
                                                <span class="font-mono text-[10px] text-base-content/40">{{ $field }}</span>
                                            </td>

                                            {{-- Nama Kolom di File Distributor --}}
                                            <td>
                                                @if(!empty($detectedHeaders))
                                                    {{-- Jika ada sample file, sediakan dropdown quick select & opsi manual --}}
                                                    <div class="space-y-1.5">
                                                        <div class="flex items-center gap-1.5">
                                                            <select wire:change="selectDetectedHeader('{{ $field }}', $event.target.value)"
                                                                    class="select select-bordered select-xs w-48 bg-base-200 text-xs text-base-content truncate">
                                                                <option value="">-- Pilih dari File --</option>
                                                                @foreach($detectedHeaders as $colIdx => $colName)
                                                                    @php
                                                                        $sampleSnippet = !empty($sampleRowData[$colIdx]) ? ' [Contoh: ' . \Illuminate\Support\Str::limit($sampleRowData[$colIdx], 20) . ']' : '';
                                                                    @endphp
                                                                    <option value="{{ $colIdx }}" {{ ($indexVal == $colIdx && $headerVal == $colName) ? 'selected' : '' }}>
                                                                        {{ $this->getColumnLetter($colIdx) }}: {{ $colName }}{{ $sampleSnippet }}
                                                                    </option>
                                                                @endforeach
                                                            </select>

                                                            <input type="text"
                                                                   wire:model.live.debounce.300ms="config.{{ $field }}.header_inv_dist"
                                                                   placeholder="Header manual..."
                                                                   class="input input-xs w-full bg-base-100 border border-base-300 rounded text-xs text-base-content font-mono @error('config.'.$field.'.header_inv_dist') border-error @enderror" />
                                                        </div>

                                                        {{-- Badge Contoh Isian Data Kolom Ini --}}
                                                        @if(!empty($indexVal) && isset($sampleRowData[$indexVal]) && $sampleRowData[$indexVal] !== '')
                                                            <div class="flex items-center gap-1.5 text-[11px] text-base-content/70">
                                                                <span class="badge badge-xs badge-info font-medium text-[10px]">Contoh Data:</span>
                                                                <code class="px-1.5 py-0.5 rounded bg-base-200 border border-base-300 font-mono text-base-content/90 text-[10px] truncate max-w-sm" title="{{ $sampleRowData[$indexVal] }}">
                                                                    {{ \Illuminate\Support\Str::limit($sampleRowData[$indexVal], 40) }}
                                                                </code>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <input type="text"
                                                           wire:model.live.debounce.300ms="config.{{ $field }}.header_inv_dist"
                                                           placeholder="Contoh: {{ $alias }}"
                                                           class="input input-xs md:input-sm w-full bg-base-100 border border-base-300 rounded-lg text-xs text-base-content font-mono @error('config.'.$field.'.header_inv_dist') border-error @enderror" />
                                                @endif
                                                @error('config.'.$field.'.header_inv_dist')
                                                    <p class="text-[10px] text-error mt-0.5">{{ $message }}</p>
                                                @enderror
                                            </td>

                                            {{-- Posisi Kolom (Mendukung Huruf A, B, C... atau Angka) --}}
                                            <td>
                                                <div class="flex items-center gap-2">
                                                    <input type="text"
                                                           wire:model.live.debounce.300ms="config.{{ $field }}.index"
                                                           placeholder="Angka / Huruf (misal: C atau 3)"
                                                           class="input input-xs md:input-sm w-32 bg-base-100 border border-base-300 rounded-lg text-xs font-mono text-base-content @error('config.'.$field.'.index') border-error @enderror" />

                                                    @if(!empty($letter))
                                                        <span class="badge badge-primary badge-sm font-mono font-bold" title="Posisi di Excel: Kolom {{ $letter }} (Urutan ke-{{ $indexVal }})">
                                                            Kolom {{ $letter }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @error('config.'.$field.'.index')
                                                    <p class="text-[10px] text-error mt-0.5">{{ $message }}</p>
                                                @enderror
                                            </td>

                                            {{-- Tombol Reset Baris --}}
                                            <td class="text-center">
                                                @if($isRowFilled)
                                                    <button type="button" 
                                                            wire:click="clearField('{{ $field }}')"
                                                            class="btn btn-ghost btn-xs btn-circle text-base-content/40 hover:text-error hover:bg-error/10"
                                                            title="Kosongkan baris ini">
                                                        <x-heroicon-s-x-mark class="w-4 h-4" />
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-12 text-center text-base-content/40">
                                                <x-heroicon-o-magnifying-glass class="w-8 h-8 mx-auto mb-2 opacity-40" />
                                                <p class="text-xs md:text-sm">Tidak ada kolom yang cocok dengan pencarian / filter ini.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            {{-- BOTTOM STICKY BAR (SAVE / BACK) --}}
            <div class="bg-base-200/80 p-3 md:p-4 border-t border-base-300 shrink-0 flex flex-col sm:flex-row justify-between items-center gap-3">
                <div class="text-xs text-base-content/60 text-center sm:text-left">
                    <span class="font-bold text-base-content">{{ count($filteredFields) }}</span> kolom ditampilkan. Pastikan semua kolom bertanda <span class="text-error font-bold">*</span> terisi lengkap.
                </div>
                <div class="flex items-center gap-2">
                    <x-ui.action-button
                        type="default"
                        label="Kembali"
                        icon="arrow-left"
                        href="{{ route('sales-configs.index') }}"
                        class="btn-sm btn-ghost border border-base-300 hover:bg-base-300/50"
                    />
                    <button type="submit" 
                            class="btn btn-sm btn-primary shadow-sm shadow-primary/20 gap-1.5 normal-case"
                            wire:loading.attr="disabled">
                        <x-heroicon-o-check class="w-4 h-4" />
                        <span wire:loading.remove wire:target="save">Simpan Konfigurasi</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                    </button>
                </div>
            </div>

        </form>
    </div>

    {{-- MODAL SALIN CONFIG DARI DISTRIBUTOR LAIN --}}
    @if($isCopyModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-base-100/80 backdrop-blur-sm" wire:click="closeCopyModal"></div>

        <div class="relative bg-base-100 rounded-2xl shadow-2xl border border-base-300 p-5 md:p-6 w-full max-w-xl z-10 flex flex-col max-h-[90vh]">
            <div class="flex items-start justify-between gap-3 pb-3 border-b border-base-300">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-primary/10 text-primary">
                        <x-heroicon-o-document-duplicate class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-base-content">Salin dari Config Distributor Lain</h3>
                        <p class="text-xs text-base-content/60">Pilih cabang yang format file Excel-nya sama dengan cabang ini</p>
                    </div>
                </div>
                <button type="button" wire:click="closeCopyModal" class="btn btn-ghost btn-xs btn-circle text-base-content/60 hover:text-base-content">
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>

            <div class="py-4 space-y-4 overflow-y-auto custom-scrollbar flex-1">
                {{-- Search Box --}}
                <div class="relative">
                    <input type="text" 
                           wire:model.live.debounce.250ms="copySearch"
                           placeholder="Cari kode atau nama distributor sumber..."
                           class="input input-sm w-full bg-base-200 border border-base-300 pl-8 text-xs text-base-content rounded-xl" />
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-base-content/40" />
                </div>

                {{-- List Pilihan Distributor Sumber --}}
                <div class="border border-base-300 rounded-xl divide-y divide-base-200 max-h-64 overflow-y-auto custom-scrollbar bg-base-200/30">
                    @forelse($this->sourceConfigs as $source)
                        <label class="flex items-center justify-between p-3 cursor-pointer hover:bg-primary/10 transition-colors {{ $selectedSourceConfigId == $source->id ? 'bg-primary/15 font-bold' : '' }}">
                            <div class="flex items-center gap-3">
                                <input type="radio" 
                                       wire:model.live="selectedSourceConfigId" 
                                       value="{{ $source->id }}" 
                                       class="radio radio-primary radio-sm" />
                                <div>
                                    <div class="text-xs md:text-sm text-base-content font-medium">{{ $source->config_name }}</div>
                                    <div class="text-[11px] font-mono text-base-content/60">{{ $source->distributor_code }}</div>
                                </div>
                            </div>
                            <span class="badge badge-ghost badge-sm font-mono text-[10px]">
                                {{ $source->updated_at ? $source->updated_at->format('d M Y') : '-' }}
                            </span>
                        </label>
                    @empty
                        <div class="p-6 text-center text-xs text-base-content/50">
                            Tidak ditemukan konfigurasi distributor yang cocok.
                        </div>
                    @endforelse
                </div>
                @error('selectedSourceConfigId')
                    <p class="text-xs text-error font-medium">{{ $message }}</p>
                @enderror

                <div class="p-3 bg-warning/10 border border-warning/20 rounded-xl text-xs text-warning flex items-start gap-2">
                    <x-heroicon-o-information-circle class="w-4 h-4 mt-0.5 flex-shrink-0" />
                    <span>Perhatian: Menyalin konfigurasi akan menimpa seluruh pemetaan kolom yang saat ini telah terisi. Data distributor yang Anda pilih tidak akan berubah.</span>
                </div>
            </div>

            <div class="pt-3 border-t border-base-300 flex justify-end gap-2">
                <button type="button" wire:click="closeCopyModal" class="btn btn-sm btn-ghost border border-base-300 normal-case">
                    Batal
                </button>
                <button type="button" 
                        wire:click="applyCopyConfig"
                        @if(empty($selectedSourceConfigId)) disabled @endif
                        class="btn btn-sm btn-primary shadow-sm shadow-primary/20 gap-1.5 normal-case">
                    <x-heroicon-o-check class="w-4 h-4" />
                    Terapkan Salinan
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
