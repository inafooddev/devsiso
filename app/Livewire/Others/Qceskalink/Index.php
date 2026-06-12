<?php

namespace App\Livewire\Others\Qceskalink;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\NominalQcDistImport;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $monthFilter;
    public $regionFilter = '';

    // Import Properties
    public $isImportModalOpen = false;
    public $importMonth;
    public $importExcel;

    // Edit Properties
    public $isEditModalOpen = false;
    public $editDistCode = '';
    public $editDistName = '';
    public $editQty = 0;
    public $editDisc4 = 0;
    public $editDisc8 = 0;
    public $editNeto = 0;
    public $editNominalSurat = 0;
    public $editFileSurat;
    public $existingFileSurat;

    public function mount()
    {
        $this->monthFilter = date('Y-m'); // Default ke bulan saat ini
    }

    public function openImportModal()
    {
        $this->reset(['importMonth', 'importExcel']);
        $this->importMonth = date('Y-m'); // Default ke bulan saat ini
        $this->isImportModalOpen = true;
    }

    public function closeImportModal()
    {
        $this->isImportModalOpen = false;
        $this->reset(['importMonth', 'importExcel']);
    }

    public function processImport()
    {
        $this->validate([
            'importMonth' => 'required',
            'importExcel' => 'required|file|mimes:xlsx,xls,csv|max:10240', // max 10MB
        ]);

        $tanggal = \Carbon\Carbon::parse($this->importMonth)->startOfMonth()->format('Y-m-d');
        
        try {
            Excel::import(new NominalQcDistImport($tanggal, null), $this->importExcel);
            $this->closeImportModal();
            session()->flash('message', 'Data berhasil diimport.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $fileName = 'QC_Eskalink_' . date('Ymd_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\QcEskalinkExport($this->monthFilter, $this->search, $this->regionFilter), 
            $fileName
        );
    }

    public function openEditModal($distCode, $distName)
    {
        $this->editDistCode = $distCode;
        $this->editDistName = $distName;

        $tanggal = \Carbon\Carbon::parse($this->monthFilter)->startOfMonth()->format('Y-m-d');
        
        $data = \App\Models\NominalQcDist::where('distributor_code', $distCode)
                    ->where('tanggal', $tanggal)
                    ->first();

        if ($data) {
            $this->editQty = $data->qty;
            $this->editDisc4 = $data->discount_4;
            $this->editDisc8 = $data->discount_8;
            $this->editNeto = $data->neto;
            $this->editNominalSurat = $data->nominal_surat;
            $this->existingFileSurat = $data->file_surat;
        } else {
            $this->editQty = 0;
            $this->editDisc4 = 0;
            $this->editDisc8 = 0;
            $this->editNeto = 0;
            $this->editNominalSurat = 0;
            $this->existingFileSurat = null;
        }

        $this->isEditModalOpen = true;
    }

    public function closeEditModal()
    {
        $this->isEditModalOpen = false;
        $this->reset(['editDistCode', 'editDistName', 'editQty', 'editDisc4', 'editDisc8', 'editNeto', 'editNominalSurat', 'editFileSurat', 'existingFileSurat']);
    }

    public function deleteData($distCode)
    {
        $tanggal = \Carbon\Carbon::parse($this->monthFilter)->startOfMonth()->format('Y-m-d');
        
        $data = \App\Models\NominalQcDist::where('distributor_code', $distCode)
                    ->where('tanggal', $tanggal)
                    ->first();

        if ($data) {
            if ($data->file_surat) {
                \Illuminate\Support\Facades\Storage::delete($data->file_surat);
            }
            $data->delete();
            session()->flash('message', 'Data nominal berhasil dihapus.');
        }
    }

    public function deleteFileSurat()
    {
        $tanggal = \Carbon\Carbon::parse($this->monthFilter)->startOfMonth()->format('Y-m-d');
        
        $data = \App\Models\NominalQcDist::where('distributor_code', $this->editDistCode)
                    ->where('tanggal', $tanggal)
                    ->first();

        if ($data && $data->file_surat) {
            \Illuminate\Support\Facades\Storage::delete($data->file_surat);
            $data->update(['file_surat' => null]);
            $this->existingFileSurat = null;
            session()->flash('message', 'File surat berhasil dihapus.');
        }
    }

    public function saveEdit()
    {
        $this->validate([
            'editQty' => 'required|numeric',
            'editDisc4' => 'required|numeric',
            'editDisc8' => 'required|numeric',
            'editNeto' => 'required|numeric',
            'editNominalSurat' => 'required|numeric',
            'editFileSurat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $tanggal = \Carbon\Carbon::parse($this->monthFilter)->startOfMonth()->format('Y-m-d');

        $suratPath = $this->existingFileSurat;
        if ($this->editFileSurat) {
            $suratPath = $this->editFileSurat->store('public/surat_qc');
        }

        \App\Models\NominalQcDist::updateOrCreate(
            [
                'tanggal' => $tanggal,
                'distributor_code' => $this->editDistCode,
            ],
            [
                'qty' => $this->editQty,
                'discount_4' => $this->editDisc4,
                'discount_8' => $this->editDisc8,
                'neto' => $this->editNeto,
                'nominal_surat' => $this->editNominalSurat,
                'file_surat' => $suratPath,
            ]
        );

        $this->closeEditModal();
        session()->flash('message', 'Data nominal berhasil disimpan.');
    }

    public function render()
    {
        $startOfMonth = \Carbon\Carbon::parse($this->monthFilter)->startOfMonth()->format('Y-m-d');
        $endOfMonth = \Carbon\Carbon::parse($this->monthFilter)->endOfMonth()->format('Y-m-d');

        // Subquery for CORE
        $coreQuery = DB::table('nominal_qc_dist')
            ->select('distributor_code', 'qty', 'discount_4', 'discount_8', 'neto', 'nominal_surat', 'file_surat')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);

        // Subquery for ESKA
        $eskaQuery = DB::table('selling_out_eskalink')
            ->select(
                'branch_code as distributor_code',
                DB::raw('COUNT(*) as total_row'),
                DB::raw('SUM(qty3_pcs) as qty'),
                DB::raw('SUM(gross_amount) as gross_amount'),
                DB::raw('SUM(line_discount_4) as discount_4'),
                DB::raw('SUM(line_discount_8) as discount_8'),
                DB::raw('SUM(dpp) as dpp'),
                DB::raw('SUM(tax) as tax'),
                DB::raw('SUM(nett_amount) as neto')
            )
            ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
            ->groupBy('branch_code');

        // Main Query
        $query = DB::table('distributor_implementasi_eskalink as d')
            ->leftJoin('master_distributors as md', 'd.distributor_code', '=', 'md.distributor_code')
            ->leftJoinSub($coreQuery, 'core', 'd.eskalink_code', '=', 'core.distributor_code')
            ->leftJoinSub($eskaQuery, 'eska', 'd.eskalink_code', '=', 'eska.distributor_code')
            ->where('d.implementasi', 'Y')
            ->where('md.is_active', true)
            ->where('md.region_code', '<>', 'HOINA')
            ->select(
                'md.region_code',
                'md.region_name',
                'md.area_code',
                'md.area_name',
                'd.eskalink_code as distributor_code',
                'md.distributor_name',
                
                // ROW (isian core = eska)
                DB::raw('COALESCE(eska.total_row, 0) as row_core'),
                DB::raw('COALESCE(eska.total_row, 0) as row_eska'),
                DB::raw('0 as row_selisih'),
                
                // QTY
                DB::raw('COALESCE(core.qty, 0) as qty_core'),
                DB::raw('COALESCE(eska.qty, 0) as qty_eska'),
                DB::raw('COALESCE(core.qty, 0) - COALESCE(eska.qty, 0) as qty_selisih'),
                
                // GROSS AMOUNT (isian core = eska)
                DB::raw('COALESCE(eska.gross_amount, 0) as gross_core'),
                DB::raw('COALESCE(eska.gross_amount, 0) as gross_eska'),
                DB::raw('0 as gross_selisih'),
                
                // DISCOUNT 4
                DB::raw('COALESCE(core.discount_4, 0) as disc4_core'),
                DB::raw('COALESCE(eska.discount_4, 0) as disc4_eska'),
                DB::raw('COALESCE(core.discount_4, 0) - COALESCE(eska.discount_4, 0) as disc4_selisih'),
                
                // DISCOUNT 8
                DB::raw('COALESCE(core.discount_8, 0) as disc8_core'),
                DB::raw('COALESCE(eska.discount_8, 0) as disc8_eska'),
                DB::raw('COALESCE(core.discount_8, 0) - COALESCE(eska.discount_8, 0) as disc8_selisih'),
                
                // DPP (isian core = eska)
                DB::raw('COALESCE(eska.dpp, 0) as dpp_core'),
                DB::raw('COALESCE(eska.dpp, 0) as dpp_eska'),
                DB::raw('0 as dpp_selisih'),
                
                // TAX (isian core = eska)
                DB::raw('COALESCE(eska.tax, 0) as tax_core'),
                DB::raw('COALESCE(eska.tax, 0) as tax_eska'),
                DB::raw('0 as tax_selisih'),
                
                // NETO
                DB::raw('COALESCE(core.neto, 0) as neto_core'),
                DB::raw('COALESCE(eska.neto, 0) as neto_eska'),
                DB::raw('COALESCE(core.neto, 0) - COALESCE(eska.neto, 0) as neto_selisih'),
                
                // SURAT
                DB::raw('COALESCE(core.nominal_surat, 0) as surat_nominal'),
                DB::raw('COALESCE(core.nominal_surat, 0) - COALESCE(core.neto, 0) as surat_selisih'),
                
                // FILE SURAT
                'core.file_surat'
            );

        if ($this->search) {
            $query->where(function($q) {
                $q->where('md.distributor_name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('d.eskalink_code', 'ilike', '%' . $this->search . '%');
            });
        }

        if ($this->regionFilter) {
            $query->where('md.region_code', $this->regionFilter);
        }

        $query->orderBy('md.region_name')
              ->orderBy('md.area_name')
              ->orderBy('md.distributor_name');

        $data = $query->get();

        $regions = DB::table('master_distributors')
            ->select('region_code', 'region_name')
            ->where('is_active', true)
            ->where('region_code', '<>', 'HOINA')
            ->groupBy('region_code', 'region_name')
            ->orderBy('region_name')
            ->get();

        return view('livewire.others.qceskalink.index', [
            'data' => $data,
            'regions' => $regions
        ])->layout('layouts.app');
    }
}
