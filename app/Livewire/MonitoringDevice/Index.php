<?php

namespace App\Livewire\MonitoringDevice;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $start_month;
    public $end_month;

    public $filter_region = '';
    public $filter_area = '';
    public $filter_distributor = '';

    public function exportExcel()
    {
        $filters = [
            'search' => $this->search,
            'filter_region' => $this->filter_region,
            'filter_area' => $this->filter_area,
            'filter_distributor' => $this->filter_distributor,
            'start_month' => $this->start_month,
            'end_month' => $this->end_month,
        ];
        
        $filename = 'monitoring_device_se_' . Carbon::now()->format('Ymd_His') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MonitoringDeviceExport($filters), $filename);
    }

    // Form Properties
    public $isFormModalOpen = false;
    public $tanggal;
    public $form_distributor_search;
    public $form_distributor_code;
    public $form_sales_code;
    public $foto_tampak_depan;
    public $foto_tampak_belakang;
    public $kondisi_hp;
    public $kondisi_kartu;

    public $editId = null;
    public $existing_foto_tampak_depan = null;
    public $existing_foto_tampak_belakang = null;

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isFormModalOpen = true;
    }

    public function closeCreateModal()
    {
        $this->isFormModalOpen = false;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $data = \DB::table('monitoring_device_se')->where('id', $id)->first();
        if ($data) {
            $this->editId = $id;
            $this->tanggal = Carbon::parse($data->tanggal)->format('Y-m');
            $this->form_distributor_code = $data->distributor_code;
            
            // Fetch distributor name for search input
            $distName = \DB::table('master_distributors')->where('distributor_code', $data->distributor_code)->value('distributor_name');
            $this->form_distributor_search = $data->distributor_code . ' - ' . $distName;
            
            $this->form_sales_code = $data->sales_code;
            $this->existing_foto_tampak_depan = $data->foto_tampak_depan;
            $this->existing_foto_tampak_belakang = $data->foto_tampak_belakang;
            $this->kondisi_hp = $data->kondisi_hp;
            $this->kondisi_kartu = $data->kondisi_kartu;
            $this->isFormModalOpen = true;
        }
    }

    public function hapusFotoDepan()
    {
        if ($this->editId) {
            \DB::table('monitoring_device_se')->where('id', $this->editId)->update(['foto_tampak_depan' => null]);
            if ($this->existing_foto_tampak_depan) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($this->existing_foto_tampak_depan);
            }
            $this->existing_foto_tampak_depan = null;
        }
    }

    public function hapusFotoBelakang()
    {
        if ($this->editId) {
            \DB::table('monitoring_device_se')->where('id', $this->editId)->update(['foto_tampak_belakang' => null]);
            if ($this->existing_foto_tampak_belakang) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($this->existing_foto_tampak_belakang);
            }
            $this->existing_foto_tampak_belakang = null;
        }
    }

    public function delete($id)
    {
        \DB::table('monitoring_device_se')->where('id', $id)->delete();
        session()->flash('message', 'Data monitoring berhasil dihapus.');
    }

    // Image Preview Properties
    public $isPreviewModalOpen = false;
    public $previewImageUrl = '';

    public function openPreviewModal($url)
    {
        $this->previewImageUrl = $url;
        $this->isPreviewModalOpen = true;
    }

    public function closePreviewModal()
    {
        $this->isPreviewModalOpen = false;
        $this->previewImageUrl = '';
    }

    public function updatedFormDistributorSearch($value)
    {
        $parts = explode(' - ', $value);
        $this->form_distributor_code = $parts[0] ?? '';
        $this->form_sales_code = '';
    }

    public function updatedFormDistributorCode($value)
    {
        $this->form_sales_code = '';
    }

    public function getFormDistributors()
    {
        return \DB::table('fsalesman as f')
            ->select('f.KD as distributor_code', 'md.distributor_name')
            ->leftJoin('distributor_implementasi_eskalink as die', 'die.eskalink_code', '=', 'f.KD')
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->where('f.TEAM', 'SEI')
            ->where('f.FLAG_ACTIVE', 'Y')
            ->where('f.FLAG_OFFICE', 'N')
            ->where('md.is_active', true)
            ->distinct()
            ->orderBy('md.distributor_name')
            ->get();
    }

    public function getFormSales()
    {
        if (empty($this->form_distributor_code)) {
            return collect();
        }

        return \DB::table('fsalesman as f')
            ->select('f.SLSNO as sales_code', 'f.SLSNAME as sales_name')
            ->where('f.TEAM', 'SEI')
            ->where('f.FLAG_ACTIVE', 'Y')
            ->where('f.FLAG_OFFICE', 'N')
            ->where('f.KD', $this->form_distributor_code)
            ->orderBy('f.SLSNAME')
            ->get();
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->tanggal = '';
        $this->form_distributor_search = '';
        $this->form_distributor_code = '';
        $this->form_sales_code = '';
        $this->foto_tampak_depan = null;
        $this->foto_tampak_belakang = null;
        $this->existing_foto_tampak_depan = null;
        $this->existing_foto_tampak_belakang = null;
        $this->kondisi_hp = '';
        $this->kondisi_kartu = '';
    }

    public function save()
    {
        $this->validate([
            'tanggal' => 'required',
            'form_distributor_code' => 'required|string',
            'form_sales_code' => 'required|string',
            'foto_tampak_depan' => 'nullable|image|max:2048',
            'foto_tampak_belakang' => 'nullable|image|max:2048',
            'kondisi_hp' => 'nullable|string',
            'kondisi_kartu' => 'nullable|string',
        ]);

        $data = [
            'tanggal' => $this->tanggal . '-01', // Format Y-m to Y-m-01
            'distributor_code' => $this->form_distributor_code,
            'sales_code' => $this->form_sales_code,
            'kondisi_hp' => $this->kondisi_hp,
            'kondisi_kartu' => $this->kondisi_kartu,
            'updated_at' => now(),
        ];

        if ($this->foto_tampak_depan) {
            $data['foto_tampak_depan'] = $this->foto_tampak_depan->store('monitoring_device', 'public');
        }
        if ($this->foto_tampak_belakang) {
            $data['foto_tampak_belakang'] = $this->foto_tampak_belakang->store('monitoring_device', 'public');
        }

        if ($this->editId) {
            \DB::table('monitoring_device_se')->where('id', $this->editId)->update($data);
            session()->flash('message', 'Data monitoring berhasil diubah.');
        } else {
            $data['created_at'] = now();
            \DB::table('monitoring_device_se')->insert($data);
            session()->flash('message', 'Data monitoring berhasil ditambahkan.');
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function mount()
    {
        // Default: Dari Januari tahun ini sampai bulan ini
        $this->start_month = \Carbon\Carbon::now()->startOfYear()->format('Y-m');
        $this->end_month = \Carbon\Carbon::now()->format('Y-m');
    }

    public function updatedFilterRegion($value)
    {
        $this->filter_area = '';
        $this->filter_distributor = '';
        $this->resetPage();
    }

    public function updatedFilterArea($value)
    {
        $this->filter_distributor = '';
        $this->resetPage();
    }

    public function updatedFilterDistributor($value)
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filter_region = '';
        $this->filter_area = '';
        $this->filter_distributor = '';
        $this->start_month = \Carbon\Carbon::now()->startOfYear()->format('Y-m');
        $this->end_month = \Carbon\Carbon::now()->format('Y-m');
        $this->resetPage();
    }

    public function getFilterRegions()
    {
        return \DB::table('master_distributors')
            ->select('region_code', 'region_name')
            ->where('is_active', true)
            ->whereNotNull('region_code')
            ->distinct()
            ->orderBy('region_name')
            ->get();
    }

    public function getFilterAreas()
    {
        $query = \DB::table('master_distributors')
            ->select('area_code', 'area_name')
            ->where('is_active', true)
            ->whereNotNull('area_code')
            ->distinct();

        if (!empty($this->filter_region)) {
            $query->where('region_code', $this->filter_region);
        }
        return $query->orderBy('area_name')->get();
    }

    public function getFilterDistributors()
    {
        $query = \DB::table('master_distributors')
            ->select('distributor_code', 'distributor_name')
            ->where('is_active', true)
            ->whereNotNull('distributor_code')
            ->distinct();

        if (!empty($this->filter_region)) {
            $query->where('region_code', $this->filter_region);
        }
        if (!empty($this->filter_area)) {
            $query->where('area_code', $this->filter_area);
        }
        return $query->orderBy('distributor_name')->get();
    }

    public function render()
    {
        // 1. Ambil data master sales dan distributor
        $masterQuery = DB::table('fsalesman as f')
            ->leftJoin('distributor_implementasi_eskalink as die', 'die.eskalink_code', '=', 'f.KD')
            ->leftJoin('master_distributors as md', 'die.distributor_code', '=', 'md.distributor_code')
            ->select([
                'md.region_code',
                'md.region_name',
                'md.area_code',
                'md.area_name',
                'f.KD as distributor_code',
                'md.distributor_name',
                'md.branch_name',
                'f.SLSNO as sales_code',
                'f.SLSNAME as sales_name'
            ])
            ->where('f.TEAM', 'SEI')
            ->where('f.FLAG_ACTIVE', 'Y')
            ->where('f.FLAG_OFFICE', 'N')
            ->where('md.is_active', true);

        if (!empty($this->search)) {
            $masterQuery->where(function ($q) {
                $q->where('f.SLSNAME', 'ILIKE', '%' . $this->search . '%')
                  ->orWhere('f.SLSNO', 'ILIKE', '%' . $this->search . '%')
                  ->orWhere('md.distributor_name', 'ILIKE', '%' . $this->search . '%');
            });
        }

        if (!empty($this->filter_region)) {
            $masterQuery->where('md.region_code', $this->filter_region);
        }

        if (!empty($this->filter_area)) {
            $masterQuery->where('md.area_code', $this->filter_area);
        }

        if (!empty($this->filter_distributor)) {
            $masterQuery->where('md.distributor_code', $this->filter_distributor);
        }

        $masterQuery->orderBy('md.region_name')
                    ->orderBy('md.area_name')
                    ->orderBy('md.distributor_name')
                    ->orderBy('f.SLSNAME');

        $salesData = $masterQuery->paginate(15);

        // 2. Tentukan bulan-bulan yang akan ditampilkan dari filter start_month sampai end_month
        $months = [];
        if ($this->start_month && $this->end_month) {
            $start = Carbon::createFromFormat('Y-m', $this->start_month)->startOfMonth();
            $end = Carbon::createFromFormat('Y-m', $this->end_month)->startOfMonth();
            
            // Pastikan start tidak lebih dari end
            if ($start->gt($end)) {
                $temp = $start;
                $start = $end;
                $end = $temp;
            }

            // Batasi maksimal 12 bulan agar tabel tidak terlalu lebar
            if ($start->diffInMonths($end) > 12) {
                $start = $end->copy()->subMonths(11);
                $this->start_month = $start->format('Y-m');
            }

            $current = $start->copy();
            while ($current->lte($end)) {
                $months[] = $current->format('Y-m');
                $current->addMonth();
            }
        }

        // Default jika kosong (fallback)
        if (empty($months)) {
            $months = [Carbon::now()->format('Y-m')];
        }

        // 3. Ambil data dari monitoring_device_se untuk dicocokkan
        $salesCodes = collect($salesData->items())->pluck('sales_code')->toArray();
        $rawMonitoring = DB::table('monitoring_device_se')
            ->whereIn('sales_code', $salesCodes)
            ->get();

        // Susun data agar mudah diakses di blade: $monitoring[distributor_code_sales_code][YYYY-MM]
        $monitoringData = [];
        foreach ($rawMonitoring as $row) {
            if ($row->tanggal) {
                $monthKey = Carbon::parse($row->tanggal)->format('Y-m');
                // Simpan row terbaru atau pertama
                $monitoringData[$row->distributor_code . '_' . $row->sales_code][$monthKey] = (array) $row;
            }
        }

        // Format nama bulan untuk header
        $monthHeaders = [];
        foreach ($months as $m) {
            $monthHeaders[$m] = Carbon::createFromFormat('Y-m', $m)->translatedFormat('F Y');
        }

        return view('livewire.monitoring-device.index', [
            'salesData' => $salesData,
            'months' => $months,
            'monthHeaders' => $monthHeaders,
            'monitoringData' => $monitoringData,
        ])->title('Monitoring Device SE')->layout('layouts.app');
    }
}
