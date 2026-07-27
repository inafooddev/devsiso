<?php

namespace App\Livewire\Others\MonitoringBankGaransi;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\BankGaransi;
use App\Models\MasterDistributor;
use Illuminate\Validation\Rule;
use App\Traits\EnforcesMenuPermissions;
use Carbon\Carbon;
use Livewire\Attributes\Computed;

class Index extends Component
{
    use WithPagination, WithFileUploads;
    // use EnforcesMenuPermissions; // Dimatikan sementara untuk testing

    protected $paginationTheme = 'tailwind';
    protected string $menuRoute = 'monitoringbankgaransi.index';

    public $search = '';
    public $statusFilter = '';
    public $masaBerlakuFilter = '';
    public $statusDistributorFilter = '';
    public $statusPerpanjanganFilter = '';
    public $regionFilter = '';

    // Modal & Form States
    public $isFormModalOpen = false;
    public $isDeleteModalOpen = false;
    public $isImportModalOpen = false;
    public $showReminderModal = false;
    public $isEditing = false;
    public $garansiIdToDelete = null;

    // Follow Up States
    public $isFollowUpModalOpen = false;
    public $selectedBgForFollowUp = null;
    public $followUpCatatan = '';
    public $followUpStatus = 'Belum';
    public $followUpAttachment = null;

    // Form Fields
    public $garansi_id;
    public $distributor_code = '';
    public $nama_bank = '';
    public $nomor_jaminan = '';
    public $nomor_seri = '';
    public $nilai_jaminan = '';
    public $tanggal_terbit = '';
    public $tanggal_jatuh_tempo = '';
    public $status_perpanjangan = 'Tidak';
    public $keterangan = '';
    public $dokumen_lampiran;
    public $dokumen_lampiran_lama;
    public $importFile;
    
    // UI Helpers
    public $distributorSearch = '';
    public $selectedDistributorName = '';

    protected $queryString = [
        'statusFilter' => ['except' => ''],
        'statusDistributorFilter' => ['except' => ''],
        'statusPerpanjanganFilter' => ['except' => ''],
        'masaBerlakuFilter' => ['except' => ''],
        'regionFilter' => ['except' => ''],
    ];

    public function mount()
    {
        if ($this->expiringBgs->count() > 0) {
            $this->showReminderModal = true;
        }
    }

    protected function rules()
    {
        return [
            'distributor_code' => 'required',
            'nama_bank' => 'required|string',
            'nomor_jaminan' => 'required|string',
            'nomor_seri' => 'nullable|string',
            'nilai_jaminan' => 'required|numeric|min:0',
            'tanggal_terbit' => 'required|date',
            'tanggal_jatuh_tempo' => 'required|date|after_or_equal:tanggal_terbit',
            'status_perpanjangan' => 'required|in:Ya,Tidak',
            'keterangan' => 'nullable|string',
            'dokumen_lampiran' => 'nullable|image|max:5120', // Maksimal 5MB, format gambar
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[Computed]
    public function activeDistributors()
    {
        if (strlen($this->distributorSearch) < 2) {
            return collect();
        }

        $query = MasterDistributor::where('is_active', true);
        
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('region_code', $user->region_code);
        }

        $query->where(function($q) {
            $q->where('distributor_name', 'ilike', '%' . $this->distributorSearch . '%')
              ->orWhere('distributor_code', 'ilike', '%' . $this->distributorSearch . '%');
        });

        return $query->orderBy('distributor_name', 'asc')->take(50)->get();
    }

    #[Computed]
    public function availableRegions()
    {
        $query = \App\Models\MasterRegion::query();
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('region_code', $user->region_code);
        }
        return $query->orderBy('region_name', 'asc')->get();
    }

    #[Computed]
    public function expiringBgs()
    {
        $query = BankGaransi::with('distributor');

        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereHas('distributor', function($q) use ($user) {
                $q->whereIn('region_code', $user->region_code);
            });
        }

        $targetDate = Carbon::now()->startOfDay()->addMonths(3);

        return $query->where('tanggal_jatuh_tempo', '<=', $targetDate)
                     ->where('progress_status', '!=', 'Close')
                     ->orderByRaw('tanggal_jatuh_tempo < CURRENT_DATE DESC')
                     ->orderBy('tanggal_jatuh_tempo', 'asc')
                     ->get();
    }

    #[Computed]
    public function kpiStats()
    {
        $query = BankGaransi::query();

        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereHas('distributor', function($q) use ($user) {
                $q->whereIn('region_code', $user->region_code);
            });
        }

        $garansis = $query->get();
        $now = Carbon::now()->startOfDay();
        
        $stats = [
            'aktif' => 0,
            'expired' => 0,
            'diperpanjang' => 0,
            'kurang_3_bulan' => 0,
            'kurang_2_bulan' => 0,
            'kurang_1_bulan' => 0,
        ];

        foreach ($garansis as $g) {
            // Hitung total fisik BG yang masih aktif
            if ($g->status === 'Aktif') $stats['aktif']++;

            // Abaikan BG yang urusannya sudah 'Close' untuk indikator alarm
            if ($g->progress_status !== 'Close') {
                if ($g->status === 'Expired') $stats['expired']++;

                if ($g->status === 'Aktif' && $g->tanggal_jatuh_tempo) {
                    $days = $now->diffInDays($g->tanggal_jatuh_tempo->startOfDay(), false);
                    
                    if ($days >= 0 && $days <= 90) {
                        $stats['kurang_3_bulan']++;
                    }
                    if ($days >= 0 && $days <= 60) {
                        $stats['kurang_2_bulan']++;
                    }
                    if ($days >= 0 && $days <= 30) {
                        $stats['kurang_1_bulan']++;
                    }
                }
            }
        }
        
        return $stats;
    }

    public function selectDistributor($code, $name)
    {
        $this->distributor_code = $code;
        $this->selectedDistributorName = $name;
        $this->distributorSearch = '';
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        $this->isFormModalOpen = true;
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $garansi = BankGaransi::with('distributor')->findOrFail($id);
        
        $this->garansi_id = $garansi->id;
        $this->distributor_code = $garansi->distributor_code;
        $this->selectedDistributorName = $garansi->distributor->short_name ?? $garansi->distributor_code;
        $this->nama_bank = $garansi->nama_bank;
        $this->nomor_jaminan = $garansi->nomor_jaminan;
        $this->nomor_seri = $garansi->nomor_seri;
        $this->nilai_jaminan = (float) $garansi->nilai_jaminan;
        $this->tanggal_terbit = $garansi->tanggal_terbit->format('Y-m-d');
        $this->tanggal_jatuh_tempo = $garansi->tanggal_jatuh_tempo->format('Y-m-d');
        $this->status_perpanjangan = $garansi->status_perpanjangan;
        $this->keterangan = $garansi->keterangan;
        $this->dokumen_lampiran = null;
        $this->dokumen_lampiran_lama = $garansi->dokumen_lampiran;
        
        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    private function resetForm()
    {
        $this->garansi_id = null;
        $this->distributor_code = '';
        $this->distributorSearch = '';
        $this->selectedDistributorName = '';
        $this->nama_bank = '';
        $this->nomor_jaminan = '';
        $this->nomor_seri = '';
        $this->nilai_jaminan = '';
        $this->tanggal_terbit = '';
        $this->tanggal_jatuh_tempo = '';
        $this->status_perpanjangan = 'Tidak';
        $this->keterangan = '';
        $this->dokumen_lampiran = null;
        $this->dokumen_lampiran_lama = null;
        $this->resetValidation();
    }

    public function save()
    {
        // $this->authorizeAction('can_edit');
        $this->validate();

        $data = [
            'distributor_code' => $this->distributor_code,
            'nama_bank' => $this->nama_bank,
            'nomor_jaminan' => $this->nomor_jaminan,
            'nomor_seri' => $this->nomor_seri,
            'nilai_jaminan' => $this->nilai_jaminan,
            'tanggal_terbit' => $this->tanggal_terbit,
            'tanggal_jatuh_tempo' => $this->tanggal_jatuh_tempo,
            'status_perpanjangan' => $this->status_perpanjangan,
            'keterangan' => $this->keterangan,
        ];

        if ($this->dokumen_lampiran) {
            $data['dokumen_lampiran'] = $this->dokumen_lampiran->store('bank_garansi', 'public');
            
            // Delete old file if exists and we are editing
            if ($this->garansi_id && $this->dokumen_lampiran_lama) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($this->dokumen_lampiran_lama);
            }
        }

        BankGaransi::updateOrCreate(
            ['id' => $this->garansi_id],
            $data
        );

        session()->flash('message', $this->isEditing ? 'Data Bank Garansi berhasil diperbarui.' : 'Data Bank Garansi baru berhasil ditambahkan.');

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\BankGaransiTemplateExport(), 'Template_Import_Bank_Garansi.xlsx');
    }

    public function importExcel()
    {
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls|max:5120'
        ]);

        try {
            $import = new \App\Imports\BankGaransiImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $this->importFile);
            
            $msg = "Import selesai! {$import->successCount} data berhasil diproses.";
            if ($import->errorCount > 0) {
                $msg .= " Namun ada {$import->errorCount} baris yang gagal, pastikan kode distributor benar.";
            }
            
            session()->flash('message', $msg);
            $this->isImportModalOpen = false;
            $this->importFile = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        $query = BankGaransi::with('distributor');
        
        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereHas('distributor', function($q) use ($user) {
                $q->whereIn('region_code', $user->region_code);
            });
        }
        
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('nama_bank', 'ilike', '%' . $this->search . '%')
                  ->orWhere('nomor_jaminan', 'ilike', '%' . $this->search . '%')
                  ->orWhere('distributor_code', 'ilike', '%' . $this->search . '%')
                  ->orWhereHas('distributor', function($q) {
                      $q->where('distributor_name', 'ilike', '%' . $this->search . '%');
                  });
            });
        }
        if ($this->statusFilter !== '') {
            if ($this->statusFilter === 'Aktif') {
                $query->whereDate('tanggal_jatuh_tempo', '>=', Carbon::today());
            } elseif ($this->statusFilter === 'Expired') {
                $query->whereDate('tanggal_jatuh_tempo', '<', Carbon::today());
            }
        }
        if ($this->masaBerlakuFilter !== '') {
            $targetDate = Carbon::now()->startOfDay();
            if ($this->masaBerlakuFilter === '3_months') $targetDate->addMonths(3);
            elseif ($this->masaBerlakuFilter === '2_months') $targetDate->addMonths(2);
            elseif ($this->masaBerlakuFilter === '1_month') $targetDate->addMonth(1);
            elseif ($this->masaBerlakuFilter === '2_weeks') $targetDate->addWeeks(2);
            $query->where('tanggal_jatuh_tempo', '<=', $targetDate)
                  ->whereDate('tanggal_jatuh_tempo', '>=', Carbon::today());
        }
        if ($this->statusDistributorFilter !== '') {
            $isActive = $this->statusDistributorFilter === 'Aktif';
            $query->whereHas('distributor', function($q) use ($isActive) {
                $q->where('is_active', $isActive);
            });
        }
        if ($this->statusPerpanjanganFilter !== '') {
            $query->where('status_perpanjangan', $this->statusPerpanjanganFilter);
        }
        if ($this->regionFilter !== '') {
            $query->whereHas('distributor', function($q) {
                $q->where('region_code', $this->regionFilter);
            });
        }

        $garansis = $query->orderBy(\App\Models\MasterDistributor::select('is_active')
                                    ->whereColumn('distributor_code', 'bank_garansis.distributor_code')
                                    ->limit(1), 'desc')
                          ->orderBy('tanggal_jatuh_tempo', 'asc')
                          ->get();

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\BankGaransiExport($garansis), 'Monitoring_Bank_Garansi_'.date('YmdHis').'.xlsx');
    }

    public function confirmDelete($id)
    {
        $this->garansiIdToDelete = $id;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        // $this->authorizeAction('can_edit');

        $garansi = BankGaransi::find($this->garansiIdToDelete);
        
        if ($garansi) {
            if ($garansi->dokumen_lampiran) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($garansi->dokumen_lampiran);
            }
            $garansi->delete();
            session()->flash('message', 'Data Bank Garansi berhasil dihapus!');
        } else {
            session()->flash('error', 'Data tidak ditemukan.');
        }

        $this->isDeleteModalOpen = false;
    }

    public function openFollowUpModal($id)
    {
        $this->selectedBgForFollowUp = BankGaransi::with(['followUps.user', 'distributor'])->find($id);
        if ($this->selectedBgForFollowUp) {
            $this->followUpStatus = $this->selectedBgForFollowUp->progress_status ?? 'Belum';
            $this->followUpCatatan = '';
            $this->followUpAttachment = null;
            $this->isFollowUpModalOpen = true;
        }
    }

    public function saveFollowUp()
    {
        $this->validate([
            'followUpStatus' => 'required|in:Belum,Sudah di-Follow Up,Close',
            'followUpCatatan' => 'required|string|max:1000',
            'followUpAttachment' => 'nullable|image|max:5120', // Max 5MB
        ]);

        if ($this->selectedBgForFollowUp) {
            $attachmentPath = null;
            if ($this->followUpAttachment) {
                $attachmentPath = $this->followUpAttachment->store('follow_ups', 'public');
            }

            // Simpan riwayat
            \App\Models\BankGaransiFollowUp::create([
                'bank_garansi_id' => $this->selectedBgForFollowUp->id,
                'user_id' => auth()->id(),
                'status_progress' => $this->followUpStatus,
                'catatan' => $this->followUpCatatan,
                'attachment' => $attachmentPath,
            ]);

            // Update status di BG
            $this->selectedBgForFollowUp->update([
                'progress_status' => $this->followUpStatus
            ]);

            // Reload data
            $this->selectedBgForFollowUp->load(['followUps.user']);
            
            // Reset form (kecuali status biarkan yang baru)
            $this->followUpCatatan = '';
            $this->followUpAttachment = null;

            // Reset file input di browser (menghindari cache state)
            $this->dispatch('reset-file-input');

            session()->flash('message', 'Catatan follow up berhasil ditambahkan.');
        }
    }

    public function render()
    {
        $query = BankGaransi::with('distributor');

        $user = auth()->user();
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereHas('distributor', function($q) use ($user) {
                $q->whereIn('region_code', $user->region_code);
            });
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_jaminan', 'ilike', '%' . $search . '%')
                  ->orWhere('nama_bank', 'ilike', '%' . $search . '%')
                  ->orWhereHas('distributor', function ($qs) use ($search) {
                      $qs->where('distributor_name', 'ilike', "%$search%")
                         ->orWhere('distributor_code', 'ilike', "%$search%");
                  });
            });
        }

        if ($this->statusFilter !== '') {
            if ($this->statusFilter === 'Aktif') {
                $query->whereDate('tanggal_jatuh_tempo', '>=', Carbon::today());
            } elseif ($this->statusFilter === 'Expired') {
                $query->whereDate('tanggal_jatuh_tempo', '<', Carbon::today());
            }
        }

        if ($this->masaBerlakuFilter !== '') {
            $targetDate = Carbon::now()->startOfDay();
            if ($this->masaBerlakuFilter === '3_months') {
                $targetDate->addMonths(3);
            } elseif ($this->masaBerlakuFilter === '2_months') {
                $targetDate->addMonths(2);
            } elseif ($this->masaBerlakuFilter === '1_month') {
                $targetDate->addMonth(1);
            } elseif ($this->masaBerlakuFilter === '2_weeks') {
                $targetDate->addWeeks(2);
            }
            $query->where('tanggal_jatuh_tempo', '<=', $targetDate)
                  ->whereDate('tanggal_jatuh_tempo', '>=', Carbon::today());
        }

        if ($this->statusDistributorFilter !== '') {
            $isActive = $this->statusDistributorFilter === 'Aktif';
            $query->whereHas('distributor', function($q) use ($isActive) {
                $q->where('is_active', $isActive);
            });
        }

        if ($this->statusPerpanjanganFilter !== '') {
            $query->where('status_perpanjangan', $this->statusPerpanjanganFilter);
        }

        if ($this->regionFilter !== '') {
            $query->whereHas('distributor', function($q) {
                $q->where('region_code', $this->regionFilter);
            });
        }

        $garansis = $query->orderByRaw("CASE WHEN progress_status = 'Close' THEN 1 ELSE 0 END ASC")
                          ->orderByRaw('tanggal_jatuh_tempo < CURRENT_DATE DESC')
                          ->orderBy(\App\Models\MasterDistributor::select('is_active')
                                    ->whereColumn('distributor_code', 'bank_garansis.distributor_code')
                                    ->limit(1), 'desc')
                          ->orderBy('tanggal_jatuh_tempo', 'asc')
                          ->paginate(20);

        return view('livewire.others.monitoringbankgaransi.index', [
            'garansis' => $garansis
        ])->layout('layouts.app');
    }
}
