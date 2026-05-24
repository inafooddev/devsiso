<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AccessGroup;
use App\Models\Menu;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')] 
class AccessGroupManagement extends Component
{
    use WithPagination;

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
        return view('livewire.access-group-management', [
            'groups' => AccessGroup::latest()->paginate(10),
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
            AccessGroup::findOrFail($this->groupId)->update($data);
            session()->flash('message', 'Access Group berhasil diperbarui.');
        } else {
            AccessGroup::create($data);
            session()->flash('message', 'Access Group berhasil ditambahkan.');
        }

        $this->isModalOpen = false;
        $this->resetFields();
    }

    public function delete($id)
    {
        AccessGroup::find($id)->delete();
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
            
            $this->isMenuModalOpen = false;
            session()->flash('message', 'Akses View untuk Group ' . $group->name . ' berhasil diperbarui.');
        }
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
