<?php

namespace App\Livewire\Rwo\MasterCustomer\Actions;

use App\Livewire\Rwo\MasterCustomer\Forms\RewardOutletForm;
use App\Models\RewardOutlet;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ActivityLogger;

class SaveRewardOutlet
{
    public function execute(RewardOutletForm $form): RewardOutlet
    {
        $data = $form->except([
            'outletId', 
            'foto_ktp', 'existing_foto_ktp', 
            'foto_toko', 'existing_foto_toko', 
            'foto_toko2', 'existing_foto_toko2', 
            'foto_toko3', 'existing_foto_toko3'
        ]);

        $isEditing = !empty($form->outletId);

        $this->handleUploads($form, $data, $isEditing);

        if ($isEditing) {
            $outlet = RewardOutlet::findOrFail($form->outletId);
            $outlet->update($data);
            ActivityLogger::log('Update RWO', "Memperbarui data RWO: {$outlet->customer_code}");
        } else {
            $outlet = RewardOutlet::create($data);
            ActivityLogger::log('Create RWO', "Menambahkan data RWO baru: {$outlet->customer_code}");
        }

        return $outlet;
    }

    private function handleUploads(RewardOutletForm $form, array &$data, bool $isEditing): void
    {
        // Handle Foto KTP
        if ($form->foto_ktp) {
            if ($isEditing && $form->existing_foto_ktp) {
                Storage::disk('public')->delete($form->existing_foto_ktp);
            }
            $data['foto_ktp'] = $form->foto_ktp->store('rwo/ktp', 'public');
        }

        // Handle Foto Toko (by GPS)
        if ($form->foto_toko) {
            if ($isEditing && $form->existing_foto_toko) {
                Storage::disk('public')->delete($form->existing_foto_toko);
            }
            $data['foto_toko'] = $form->foto_toko->store('rwo/toko', 'public');
        }

        // Handle Foto Toko 2 (Tampak Depan)
        if ($form->foto_toko2) {
            if ($isEditing && $form->existing_foto_toko2) {
                Storage::disk('public')->delete($form->existing_foto_toko2);
            }
            $data['foto_toko2'] = $form->foto_toko2->store('rwo/toko', 'public');
        }

        // Handle Foto Toko 3 (Tampak Dalam)
        if ($form->foto_toko3) {
            if ($isEditing && $form->existing_foto_toko3) {
                Storage::disk('public')->delete($form->existing_foto_toko3);
            }
            $data['foto_toko3'] = $form->foto_toko3->store('rwo/toko', 'public');
        }
    }
}
