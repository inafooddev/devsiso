<?php

namespace App\Livewire\Mobile\MonitoringDeviceSe;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $filter_year;

    public $filter_region = '';
    public $filter_area = '';
    public $filter_distributor = '';

    // Form Properties
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

    public $detailData = null;

    public function showDetail($id)
    {
        $data = \DB::table('monitoring_device_se')->where('id', $id)->first();
        if ($data) {
            $distName = \DB::table('master_distributors')->where('distributor_code', $data->distributor_code)->value('distributor_name');
            $salesName = \DB::table('fsalesman')->where('SLSNO', $data->sales_code)->where('TEAM', 'SEI')->value('SLSNAME');

            $this->detailData = [
                'tanggal' => Carbon::parse($data->tanggal)->translatedFormat('F Y'),
                'distributor' => $data->distributor_code . ' - ' . $distName,
                'sales' => $data->sales_code . ' - ' . $salesName,
                'kondisi_hp' => $data->kondisi_hp,
                'kondisi_kartu' => $data->kondisi_kartu,
                'foto_tampak_depan' => $data->foto_tampak_depan,
                'foto_tampak_belakang' => $data->foto_tampak_belakang,
            ];
            $this->dispatch('open-detail-sheet');
        }
    }

    public function edit($id)
    {
        $this->resetValidation();
        $data = \DB::table('monitoring_device_se')->where('id', $id)->first();
        if ($data) {
            $this->editId = $id;
            $this->tanggal = Carbon::parse($data->tanggal)->format('Y-m');
            $this->form_distributor_code = $data->distributor_code;
            
            $distName = \DB::table('master_distributors')->where('distributor_code', $data->distributor_code)->value('distributor_name');
            $this->form_distributor_search = $data->distributor_code . ' - ' . $distName;
            
            $this->form_sales_code = $data->sales_code;
            $this->existing_foto_tampak_depan = $data->foto_tampak_depan;
            $this->existing_foto_tampak_belakang = $data->foto_tampak_belakang;
            $this->kondisi_hp = $data->kondisi_hp;
            $this->kondisi_kartu = $data->kondisi_kartu;
            // Alpine JS will handle sheet opening via event
            $this->dispatch('open-form-sheet');
        }
    }

    public function prefillAdd($distributor_code, $sales_code, $month)
    {
        $this->resetValidation();
        $this->resetForm();
        $this->tanggal = $month;
        $this->form_distributor_code = $distributor_code;
        $distName = \DB::table('master_distributors')->where('distributor_code', $distributor_code)->value('distributor_name');
        $this->form_distributor_search = $distributor_code . ' - ' . $distName;
        $this->form_sales_code = $sales_code;
        $this->dispatch('open-form-sheet');
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
        $this->tanggal = Carbon::now()->format('Y-m');
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
            'tanggal' => $this->tanggal . '-01',
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

        $this->dispatch('close-form-sheet');
        $this->resetForm();
    }

    public function mount()
    {
        $this->filter_year = Carbon::now()->format('Y');
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
        $this->reset(['search', 'filter_region', 'filter_area', 'filter_distributor']);
        $this->filter_year = Carbon::now()->format('Y');
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

        $allSales = $masterQuery->orderBy('f.SLSNAME')->get();

        // Define Months
        $year = $this->filter_year ?: date('Y');
        $currentMonth = ($year == date('Y')) ? date('n') : 12;
        $months = [];
        for ($i = $currentMonth; $i >= 1; $i--) {
            $monthVal = $year . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $monthName = Carbon::parse($monthVal . '-01')->translatedFormat('F');
            $months[] = [
                'value' => $monthVal,
                'name' => $monthName . ' ' . $year
            ];
        }

        // Cross join SEs and Months
        $flatList = collect();
        foreach ($allSales as $sales) {
            foreach ($months as $month) {
                $flatList->push((object)[
                    'sales_code' => $sales->sales_code,
                    'sales_name' => $sales->sales_name,
                    'distributor_code' => $sales->distributor_code,
                    'distributor_name' => $sales->distributor_name,
                    'month_value' => $month['value'],
                    'month_name' => $month['name']
                ]);
            }
        }

        $page = $this->getPage();
        $perPage = 12;
        $salesData = new \Illuminate\Pagination\LengthAwarePaginator(
            $flatList->forPage($page, $perPage),
            $flatList->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        $monthValues = array_map(function($m) { return $m['value'] . '-01'; }, $months);

        $monitoringData = \DB::table('monitoring_device_se')
            ->whereIn('tanggal', $monthValues)
            ->get()
            ->keyBy(function ($item) {
                return $item->distributor_code . '_' . $item->sales_code . '_' . substr($item->tanggal, 0, 7);
            })->map(function ($item) {
                return (array) $item;
            })->toArray();

        return view('livewire.mobile.monitoring-device-se.index', [
            'salesData' => $salesData,
            'monitoringData' => $monitoringData,
            'months' => $months,
        ])->title('Mobile Monitoring Device')->layout('layouts.mobile-guest');
    }
}
