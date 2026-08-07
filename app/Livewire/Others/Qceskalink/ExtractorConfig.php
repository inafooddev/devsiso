<?php

namespace App\Livewire\Others\Qceskalink;

use Livewire\Component;
use App\Models\ExtractorConfig as ConfigModel;
use App\Traits\EnforcesMenuPermissions;
use Illuminate\Support\Str;

class ExtractorConfig extends Component
{
    use EnforcesMenuPermissions;

    protected string $menuRoute = 'dashboard.extractor-config';

    public $configs = [];
    public $search = '';
    
    // Modal state
    public $isModalOpen = false;
    public $isEditing = false;
    public $editingId = null;

    // Form state
    public $name = '';
    public $header_row = 1;
    public $keywords = [];
    public $newKeyword = '';
    
    // Columns
    public $columns = [];
    
    // Temporary state for adding a new column
    public $newColSource = '';
    public $newColLabel = '';
    public $newColType = 'text';
    public $newColFilterCol = '';
    public $newColFilterOp = '=';
    public $newColFilterVal = '';

    public function mount()
    {
        $this->loadConfigs();
    }

    public function updatedSearch()
    {
        $this->loadConfigs();
    }

    public function loadConfigs()
    {
        $query = ConfigModel::query();
        
        if (!empty($this->search)) {
            $query->where('name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('keywords', 'ilike', '%' . $this->search . '%');
        }
        
        $this->configs = $query->orderBy('name')->get();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $config = ConfigModel::findOrFail($id);
        
        $this->editingId = $config->id;
        $this->name = $config->name;
        $this->header_row = $config->header_row;
        $this->keywords = $config->keywords ?? [];
        $this->columns = $config->columns ?? [];
        
        $this->isEditing = true;
        $this->isModalOpen = true;
    }

    public function resetForm()
    {
        $this->reset(['editingId', 'name', 'header_row', 'keywords', 'newKeyword', 'columns']);
        $this->header_row = 1;
    }

    public function addKeyword()
    {
        $kw = trim($this->newKeyword);
        if ($kw && !in_array($kw, $this->keywords)) {
            $this->keywords[] = $kw;
        }
        $this->newKeyword = '';
    }

    public function removeKeyword($index)
    {
        unset($this->keywords[$index]);
        $this->keywords = array_values($this->keywords);
    }

    public function addColumn()
    {
        $this->validate([
            'newColSource' => 'required|string',
            'newColLabel' => 'required|string',
            'newColType' => 'required|in:text,sum,filtered_sum',
        ]);

        if ($this->newColType === 'filtered_sum') {
            $this->validate([
                'newColFilterCol' => 'required|string',
                'newColFilterOp' => 'required|string',
                'newColFilterVal' => 'required|string',
            ]);
        }

        $this->columns[] = [
            '_id' => Str::random(8),
            'source' => strtoupper(trim($this->newColSource)),
            'label' => trim($this->newColLabel),
            'type' => $this->newColType,
            'filterCol' => $this->newColType === 'filtered_sum' ? strtoupper(trim($this->newColFilterCol)) : '',
            'filterOp' => $this->newColType === 'filtered_sum' ? $this->newColFilterOp : '',
            'filterVal' => $this->newColType === 'filtered_sum' ? $this->newColFilterVal : '',
        ];

        $this->reset(['newColSource', 'newColLabel', 'newColType', 'newColFilterCol', 'newColFilterOp', 'newColFilterVal']);
        $this->newColType = 'text';
        $this->newColFilterOp = '=';
    }

    public function removeColumn($index)
    {
        unset($this->columns[$index]);
        $this->columns = array_values($this->columns);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'header_row' => 'required|integer|min:1',
            'keywords' => 'array',
            'columns' => 'array',
        ]);

        if ($this->isEditing) {
            $config = ConfigModel::findOrFail($this->editingId);
            $config->update([
                'name' => $this->name,
                'header_row' => $this->header_row,
                'keywords' => $this->keywords,
                'columns' => $this->columns,
            ]);
            session()->flash('message', 'Konfigurasi berhasil diperbarui.');
        } else {
            ConfigModel::create([
                'name' => $this->name,
                'header_row' => $this->header_row,
                'keywords' => $this->keywords,
                'columns' => $this->columns,
            ]);
            session()->flash('message', 'Konfigurasi berhasil ditambahkan.');
        }

        $this->isModalOpen = false;
        $this->loadConfigs();
    }

    public function delete($id)
    {
        $config = ConfigModel::findOrFail($id);
        $config->delete();
        session()->flash('message', 'Konfigurasi berhasil dihapus.');
        $this->loadConfigs();
    }

    public function render()
    {
        return view('livewire.others.qceskalink.extractor-config')->layout('layouts.app');
    }
}
