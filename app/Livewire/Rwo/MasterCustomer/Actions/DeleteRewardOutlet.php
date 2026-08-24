<?php

namespace App\Livewire\Rwo\MasterCustomer\Actions;

use App\Models\RewardOutlet;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ActivityLogger;

class DeleteRewardOutlet
{
    public function execute(int $outletId): void
    {
        $outlet = RewardOutlet::findOrFail($outletId);
        
        $customerCode = $outlet->customer_code;
        
        // Hapus file foto dari storage
        if ($outlet->foto_ktp) {
            Storage::disk('public')->delete($outlet->foto_ktp);
        }
        if ($outlet->foto_toko) {
            Storage::disk('public')->delete($outlet->foto_toko);
        }
        if ($outlet->foto_toko2) {
            Storage::disk('public')->delete($outlet->foto_toko2);
        }
        if ($outlet->foto_toko3) {
            Storage::disk('public')->delete($outlet->foto_toko3);
        }
        
        // Hapus record dari database
        $outlet->delete();
        
        ActivityLogger::log('Delete RWO', "Menghapus data RWO: {$customerCode}");
    }
}
