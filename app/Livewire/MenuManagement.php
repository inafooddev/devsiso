<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Menu;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class MenuManagement extends Component
{
    public $menus = [];
    public $flatMenus = []; // Untuk dropdown parent
    
    // Field form
    public $menuId;
    public $name;
    public $route;
    public $icon;
    public $parent_id;
    public $order_number = 1;
    
    public $isModalOpen = false;

    public function render()
    {
        // Ambil data menu berbentuk tree
        $this->menus = Menu::whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->orderBy('order_number')->with(['children' => function($q2) {
                    $q2->orderBy('order_number')->with(['children' => function($q3) {
                        $q3->orderBy('order_number');
                    }]);
                }]);
            }])->orderBy('order_number')->get();
            
        // Ambil flat list untuk pilihan dropdown Parent
        $this->flatMenus = Menu::orderBy('name')->get();

        return view('livewire.menu-management');
    }

    public function create()
    {
        $this->resetFields();
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $this->resetFields();
        $menu = Menu::findOrFail($id);
        
        $this->menuId = $menu->id;
        $this->name = $menu->name;
        $this->route = $menu->route;
        $this->icon = $menu->icon;
        $this->parent_id = $menu->parent_id;
        $this->order_number = $menu->order_number;
        
        $this->isModalOpen = true;
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string',
            'parent_id' => 'nullable|exists:menus,id',
            'order_number' => 'required|integer|min:1',
        ]);

        $data = [
            'name' => $this->name,
            'route' => $this->route,
            'icon' => $this->icon,
            'parent_id' => empty($this->parent_id) ? null : $this->parent_id,
            'order_number' => $this->order_number,
        ];

        if ($this->menuId) {
            $menu = Menu::findOrFail($this->menuId);
            
            // Mencegah parent diset ke dirinya sendiri
            if ($this->parent_id == $this->menuId) {
                $this->addError('parent_id', 'Menu tidak bisa menjadi parent untuk dirinya sendiri.');
                return;
            }
            
            $menu->update($data);
            session()->flash('message', 'Menu berhasil diperbarui.');
        } else {
            Menu::create($data);
            session()->flash('message', 'Menu berhasil ditambahkan.');
        }

        $this->isModalOpen = false;
        $this->resetFields();
    }

    public function delete($id)
    {
        Menu::find($id)->delete();
        session()->flash('message', 'Menu berhasil dihapus.');
    }

    private function resetFields()
    {
        $this->menuId = null;
        $this->name = '';
        $this->route = '';
        $this->icon = '';
        $this->parent_id = '';
        $this->order_number = 1;
        $this->resetErrorBag();
    }
}
