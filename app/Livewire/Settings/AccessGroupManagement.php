<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\AccessGroup;
use App\Models\Menu;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')] 
class AccessGroupManagement extends Component
{
    use WithPagination;

    public $search = '';

    // Properti Form Group
    public $groupId, $name, $description;

    // State untuk Modal Alpine
    public $isModalOpen = false;
    public $isMenuModalOpen = false;

    // Properti untuk Akses Menu View
    public $selectedGroupId = null;
    public $groupNameForMenu = '';
    public $selectedMenus = [];
    public $allMenus = [];

    public function render()
    {
        return view('livewire.settings.access-group-management', [
            'groups' => AccessGroup::when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })->latest()->paginate(10),
        ]);
    }

    public function create()
    {
        $this->resetFields();
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $this->resetFields();
        $group = AccessGroup::findOrFail($id);
        
        $this->groupId = $group->id;
        $this->name = $group->name;
        $this->description = $group->description;
        
        $this->isModalOpen = true;
    }

    public function store()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('access_groups', 'name')->ignore($this->groupId)],
            'description' => 'nullable|string',
        ]);

        $data = [
            'name' => $this->name,
            'description' => $this->description,
        ];

        if ($this->groupId) {
            $group = AccessGroup::findOrFail($this->groupId);
            $group->update($data);
            \App\Helpers\ActivityLogger::log('Update Access Group', "Memperbarui grup akses: {$group->name}");
            session()->flash('message', 'Access Group berhasil diperbarui.');
        } else {
            $group = AccessGroup::create($data);
            \App\Helpers\ActivityLogger::log('Create Access Group', "Membuat grup akses baru: {$group->name}");
            session()->flash('message', 'Access Group berhasil ditambahkan.');
        }

        $this->isModalOpen = false;
        $this->resetFields();
    }

    public function delete($id)
    {
        $group = AccessGroup::find($id);
        if ($group) {
            \App\Helpers\ActivityLogger::log('Delete Access Group', "Menghapus grup akses: {$group->name}");
            $group->delete();
        }
        session()->flash('message', 'Access Group berhasil dihapus.');
    }

    public function openMenuModal($id)
    {
        $group = AccessGroup::findOrFail($id);
        $this->selectedGroupId = $group->id;
        $this->groupNameForMenu = strtoupper($group->name);
        
        $this->allMenus = Menu::whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->orderBy('order_number')->with(['children' => function($q2) {
                    $q2->orderBy('order_number')->with(['children' => function($q3) {
                        $q3->orderBy('order_number');
                    }]);
                }]);
            }])->orderBy('order_number')->get()->toArray();
            
        // Init state checkboxes
        $this->selectedMenus = $group->menus()->pluck('menus.id')->toArray();
        
        $this->isMenuModalOpen = true;
    }

    public function storeMenuAccess()
    {
        if ($this->selectedGroupId) {
            $group = AccessGroup::findOrFail($this->selectedGroupId);
            $group->menus()->sync($this->selectedMenus);
            
            \App\Helpers\ActivityLogger::log('Update Access Group Menu', "Memperbarui akses menu sidebar untuk grup: {$group->name}");
            
            $this->isMenuModalOpen = false;
            session()->flash('message', 'Akses View untuk Group ' . $group->name . ' berhasil diperbarui.');
        }
    }

    public function selectAllMenus()
    {
        $this->selectedMenus = Menu::pluck('id')->toArray();
    }

    public function unselectAllMenus()
    {
        $this->selectedMenus = [];
    }

    private function resetFields()
    {
        $this->groupId = null;
        $this->name = '';
        $this->description = '';
        $this->selectedGroupId = null;
        $this->selectedMenus = [];
    }
}
