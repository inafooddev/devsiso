<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class SuratKesepakatanBersamaImport implements ToCollection, WithHeadingRow
{
    public $successCount = 0;
    public $errorList = [];
    public $updatedList = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $kuartal = $row['kuartal'] ?? null;
            $distributorCode = $row['distributor_code'] ?? null;
            $customerCode = $row['customer_code'] ?? null;
            $status = strtolower($row['status_approval'] ?? '');
            $reason = $row['alasan_penolakan'] ?? null;

            if (!$kuartal || !$customerCode || !$distributorCode) {
                continue; // Skip invalid rows
            }
            
            $isApproved = null;
            if ($status === 'approve' || $status === 'approved') {
                $isApproved = true;
                $reason = null;
            } elseif ($status === 'reject' || $status === 'rejected') {
                $isApproved = false;
                if (!$reason || $reason == '-') {
                    $this->errorList[] = "Toko [{$customerCode}] - Alasan wajib diisi jika status Reject";
                    continue;
                }
            }

            // Cek apakah data sudah ada di skb
            $existing = DB::table('surat_kesepakatan_bersama_rwo')
                ->where('kuartal', $kuartal)
                ->where('distributor_code', $distributorCode)
                ->where('customer_code', $customerCode)
                ->first();

            if ($existing) {
                // Update
                DB::table('surat_kesepakatan_bersama_rwo')
                    ->where('id', $existing->id)
                    ->update([
                        'is_approved' => $isApproved,
                        'reason' => $reason,
                        'updated_at' => now(),
                    ]);
                
                $statusText = $isApproved === true ? 'Approve' : ($isApproved === false ? 'Reject' : 'Pending');
                $this->updatedList[] = "Toko: {$customerCode} - SKB Diperbarui menjadi {$statusText}";
                $this->successCount++;
            } else {
                 $this->errorList[] = "Toko [{$customerCode}] - SKB belum tersedia di tabel. Hanya bisa update data yang sudah ada.";
            }
        }
    }
}
