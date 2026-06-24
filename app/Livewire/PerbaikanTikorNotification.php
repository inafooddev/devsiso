<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PerbaikanTikorToko;
use Illuminate\Support\Facades\Auth;

class PerbaikanTikorNotification extends Component
{
    public $pendingCount = 0;

    protected $listeners = ['refreshNotifications' => 'updateCounts'];

    public function mount()
    {
        $this->updateCounts();
    }

    public function updateCounts()
    {
        $user = Auth::user();
        if (!$user) return;

        $query = PerbaikanTikorToko::where('status', 'Pending');
        if (!$user->hasRole('admin') && !empty($user->region_code)) {
            $query->whereIn('region_code', is_array($user->region_code) ? $user->region_code : [$user->region_code]);
        }

        $this->pendingCount = $query->count();
    }

    public function render()
    {
        return view('livewire.perbaikan-tikor-notification');
    }
}
