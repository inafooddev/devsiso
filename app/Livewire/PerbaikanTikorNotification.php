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

        if (!$user->hasMenuAccess('others.perbaikantikor', 'can_view')) {
            $this->pendingCount = 0;
            return;
        }

        $this->pendingCount = PerbaikanTikorToko::query()
            ->forUserAccess($user, null, 'perbaikan_tikor_toko')
            ->where('status', 'Pending')
            ->count();
    }

    public function render()
    {
        $user = Auth::user();
        if (!$user || !$user->hasMenuAccess('others.perbaikantikor', 'can_view')) {
            return '<div></div>';
        }

        return view('livewire.perbaikan-tikor-notification');
    }
}
