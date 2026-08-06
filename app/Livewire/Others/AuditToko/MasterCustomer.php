<?php

namespace App\Livewire\Others\AuditToko;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use App\Models\ListOutletAudit;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;

class MasterCustomer extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    public $perPage = 100;
    
    // Form fields
    public $selectedId;
    public $distributor_code = '';
    public $customer_code = '';
    public $customer_name = '';
    public $customer_address = '';
    public $latitude = '';
    public $longitude = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getFilteredQueryProperty()
    {
        $query = ListOutletAudit::query();

        if (!empty($this->search)) {
            $q = '%' . trim($this->search) . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('customer_name', 'like', $q)
                    ->orWhere('customer_code', 'like', $q)
                    ->orWhere('distributor_code', 'like', $q);
            });
        }

        return $query;
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('open-create-modal');
    }

    public function save()
    {
        if ($this->selectedId) {
            $this->update();
        } else {
            $this->store();
        }
    }

    public function store()
    {
        $this->validate([
            'distributor_code' => 'required|string|max:50',
            'customer_code' => 'required|string|max:50|unique:list_outlet_audit,customer_code',
            'customer_name' => 'required|string|max:255',
            'customer_address' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
        ], [
            'customer_code.unique' => 'Kode Toko sudah terdaftar.',
            'required' => ':attribute wajib diisi.'
        ]);

        ListOutletAudit::create([
            'distributor_code' => $this->distributor_code,
            'customer_code' => $this->customer_code,
            'customer_name' => $this->customer_name,
            'customer_address' => $this->customer_address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);

        ActivityLogger::log('Create Master Customer Audit', "Menambahkan data master customer audit baru: {$this->customer_name} ({$this->customer_code})");

        $this->dispatch('close-create-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Data Master Customer berhasil ditambahkan.');
        $this->resetForm();
    }

    public function edit($id)
    {
        $this->resetForm();
        $data = ListOutletAudit::find($id);
        if ($data) {
            $this->selectedId = $data->id;
            $this->distributor_code = $data->distributor_code;
            $this->customer_code = $data->customer_code;
            $this->customer_name = $data->customer_name;
            $this->customer_address = $data->customer_address;
            $this->latitude = $data->latitude;
            $this->longitude = $data->longitude;
            $this->dispatch('open-edit-modal');
        }
    }

    public function update()
    {
        $this->validate([
            'distributor_code' => 'required|string|max:50',
            'customer_code' => 'required|string|max:50|unique:list_outlet_audit,customer_code,' . $this->selectedId,
            'customer_name' => 'required|string|max:255',
            'customer_address' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
        ]);

        $data = ListOutletAudit::find($this->selectedId);
        if ($data) {
            $data->update([
                'distributor_code' => $this->distributor_code,
                'customer_code' => $this->customer_code,
                'customer_name' => $this->customer_name,
                'customer_address' => $this->customer_address,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ]);

            ActivityLogger::log('Update Master Customer Audit', "Memperbarui data master customer audit ID: {$this->selectedId} - {$this->customer_name}");

            $this->dispatch('close-edit-modal');
            $this->dispatch('show-toast', type: 'success', message: 'Data Master Customer berhasil diperbarui.');
            $this->resetForm();
        }
    }

    public function deleteConfirm($id)
    {
        $this->selectedId = $id;
        $this->dispatch('open-delete-modal');
    }

    public function destroy()
    {
        $data = ListOutletAudit::find($this->selectedId);
        if ($data) {
            $customerName = $data->customer_name;
            $data->delete();

            ActivityLogger::log('Delete Master Customer Audit', "Menghapus data master customer audit: {$customerName}");

            $this->dispatch('close-delete-modal');
            $this->dispatch('show-toast', type: 'success', message: "Data Master Customer {$customerName} berhasil dihapus.");
        }
        $this->resetForm();
    }

    public function exportExcel()
    {
        ActivityLogger::log('Export Master Customer Audit', 'Mengekspor data Master Customer Audit (Excel).');

        return Excel::download(
            new \App\Exports\ListOutletAuditExport($this->search),
            'Master_Customer_Audit_' . date('Ymd_His') . '.xlsx'
        );
    }

    private function resetForm()
    {
        $this->selectedId = null;
        $this->distributor_code = '';
        $this->customer_code = '';
        $this->customer_name = '';
        $this->customer_address = '';
        $this->latitude = '';
        $this->longitude = '';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $customers = $this->filteredQuery
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.others.audittoko.master-customer', [
            'customers' => $customers,
        ])->layout('layouts.app');
    }
}
