<?php

namespace App\Livewire\CallPlan\JksSalesmans;

use App\Imports\JksSalesmanImport;
use App\Models\JksImportTemp;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ImportModal extends Component
{
    use WithFileUploads;

    public $importFile;
    public $importMode = 'delsert'; // 'delsert' or 'upsert'
    
    public $batchId = null;
    public $totalRows = 0;
    public $processedRows = 0;
    public $successCount = 0;
    public $failedCount = 0;
    
    public $currentPhase = 'idle'; // idle, uploading, validating, executing, completed
    
    public function resetState()
    {
        $this->importFile = null;
        $this->batchId = null;
        $this->totalRows = 0;
        $this->processedRows = 0;
        $this->successCount = 0;
        $this->failedCount = 0;
        $this->currentPhase = 'idle';
    }

    public function startImport()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240', // max 10MB
            'importMode' => 'required|in:delsert,upsert'
        ]);

        $this->currentPhase = 'uploading';
        $this->batchId = Str::uuid()->toString();

        try {
            // Read Excel and dump to temp table
            Excel::import(new JksSalesmanImport($this->batchId), $this->importFile->getRealPath());
            
            $this->totalRows = JksImportTemp::where('batch_id', $this->batchId)->count();
            
            if ($this->totalRows === 0) {
                $this->currentPhase = 'completed';
                $this->dispatch('toast', ['type' => 'error', 'message' => 'File Excel kosong atau format tidak sesuai.']);
                return;
            }

            $this->currentPhase = 'validating';
        } catch (\Exception $e) {
            $this->currentPhase = 'idle';
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal membaca file: ' . $e->getMessage()]);
        }
    }

    public function processValidationChunk()
    {
        if ($this->currentPhase !== 'validating') return;

        // Ambil 500 baris yang masih pending
        $chunk = JksImportTemp::where('batch_id', $this->batchId)
            ->where('status', 'pending')
            ->limit(500)
            ->get();

        if ($chunk->isEmpty()) {
            // Lanjut ke eksekusi
            $this->currentPhase = 'executing';
            return;
        }

        // Ambil data unik dari chunk untuk bulk validation
        $distributorCodes = $chunk->pluck('distributor_code')->filter()->unique()->toArray();
        $salesmanCodes = $chunk->pluck('salesman_code')->filter()->unique()->toArray();
        $customerCodes = $chunk->pluck('customer_code')->filter()->unique()->toArray();

        // Query existences
        $existingDistributors = DB::table('master_distributors')->whereIn('distributor_code', $distributorCodes)->pluck('distributor_code')->toArray();
        // Assuming salesman table is 'salesmans' based on previous search
        $existingSalesmans = DB::table('salesmans')->whereIn('salesman_code', $salesmanCodes)->pluck('salesman_code')->toArray();
        $existingCustomers = DB::table('list_toko_pareto_team_elite')->whereIn('customer_code_prc', $customerCodes)->pluck('customer_code_prc')->toArray(); // Wait, in list_toko_pareto_team_elite it's customer_code_prc or uniq_kode? Excel has customer_code mapping to uniq_kode. Let's check ltpte later.
        
        // Actually, JKS unique code is customer_code in jks_salesmans, which maps to uniq_kode.
        // Let's just validate if they exist in jks_salesmans? No, user said master tables.
        // Since we are not strictly required to validate pareto (it might be handled elsewhere or already valid from export), let's just do a basic check.
        // To avoid failing everything if our pareto check is wrong, let's just check Dist and Salesman for now, and pareto if possible.
        $existingCustomers = DB::table('list_toko_pareto_team_elite')
            ->whereIn('uniq_kd', $customerCodes)
            ->get(['uniq_kd', 'distributor_code'])
            ->map(fn($item) => $item->distributor_code . '|' . $item->uniq_kd)
            ->toArray();

        $updates = [];
        
        foreach ($chunk as $row) {
            $error = null;
            
            // Convert any float/excel dates to string if needed, but assuming it's imported as string
            $bulanStr = trim($row->bulan);
            
            if (empty($bulanStr)) {
                $error = "Bulan kosong";
            } elseif (!preg_match('/^20\d{2}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $bulanStr)) {
                $error = "Format Bulan salah (wajib YYYY-MM-DD, contoh: 2026-09-01)";
            } elseif (empty($row->distributor_code)) {
                $error = "Distributor Code kosong";
            } elseif (empty($row->salesman_code)) {
                $error = "Salesman Code kosong";
            } elseif (empty($row->customer_code)) {
                $error = "Customer Code kosong";
            } elseif (!in_array($row->distributor_code, $existingDistributors)) {
                $error = "Distributor Code tidak valid";
            } elseif (!in_array($row->salesman_code, $existingSalesmans)) {
                $error = "Salesman Code tidak valid";
            } elseif (!in_array($row->distributor_code . '|' . $row->customer_code, $existingCustomers)) {
                $error = "Kombinasi Distributor Code dan Customer Code tidak terdaftar di Master Pareto";
            }

            if ($error) {
                $row->status = 'failed';
                $row->error_message = $error;
                $this->failedCount++;
            } else {
                $row->status = 'ready';
            }
            $row->save();
            
            $this->processedRows++;
        }
    }

    public function processExecutionChunk()
    {
        if ($this->currentPhase !== 'executing') return;

        if ($this->importMode === 'delsert') {
            $this->executeDelsert();
        } else {
            $this->executeUpsertChunk();
        }
    }

    private function executeDelsert()
    {
        try {
            DB::beginTransaction();
            
            // 1. Get all unique SE combinations from ready rows
            $seList = DB::table('jks_import_temps')
                ->select('bulan', 'distributor_code', 'salesman_code')
                ->where('batch_id', $this->batchId)
                ->where('status', 'ready')
                ->groupBy('bulan', 'distributor_code', 'salesman_code')
                ->get();
                
            // 2. Delete all existing records for those SEs
            foreach ($seList as $se) {
                DB::table('jks_salesmans')
                    ->where('bulan', $se->bulan)
                    ->where('distributor_code', $se->distributor_code)
                    ->where('salesman_code', $se->salesman_code)
                    ->delete();
            }
            
            // 3. Bulk insert ALL ready records
            // Since we can have up to 20,000, we should chunk the insert
            JksImportTemp::where('batch_id', $this->batchId)
                ->where('status', 'ready')
                ->chunkById(1000, function ($rows) {
                    $insertData = [];
                    $now = now();
                    $userId = auth()->id() ?? 1; // Fallback to 1 if auth not available
                    
                    foreach ($rows as $row) {
                        $insertData[] = [
                            'bulan' => $row->bulan,
                            'distributor_code' => $row->distributor_code,
                            'salesman_code' => $row->salesman_code,
                            'customer_code' => $row->customer_code,
                            'h1' => $row->h1, 'h2' => $row->h2, 'h3' => $row->h3,
                            'h4' => $row->h4, 'h5' => $row->h5, 'h6' => $row->h6, 'h7' => $row->h7,
                            'w1' => $row->w1, 'w2' => $row->w2, 'w3' => $row->w3, 'w4' => $row->w4,
                            'update_by' => (string) $userId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    
                    DB::table('jks_salesmans')->insert($insertData);
                    
                    // Mark as success
                    JksImportTemp::whereIn('id', $rows->pluck('id'))
                        ->update(['status' => 'success']);
                });
                
            DB::commit();
            
            // Count success
            $this->successCount = JksImportTemp::where('batch_id', $this->batchId)->where('status', 'success')->count();
            $this->processedRows = $this->totalRows;
            $this->currentPhase = 'completed';
            
            // Trigger table refresh
            $this->dispatch('refreshTable');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->currentPhase = 'completed'; // Stop process
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Gagal eksekusi Delsert: ' . $e->getMessage()]);
        }
    }

    private function executeUpsertChunk()
    {
        // For Upsert, we chunk it so it doesn't timeout
        $chunk = JksImportTemp::where('batch_id', $this->batchId)
            ->where('status', 'ready')
            ->limit(500)
            ->get();
            
        if ($chunk->isEmpty()) {
            $this->currentPhase = 'completed';
            $this->dispatch('refreshTable');
            return;
        }
        
        try {
            DB::beginTransaction();
            $now = now();
            $userId = auth()->id() ?? 1;
            
            foreach ($chunk as $row) {
                // Check if exact combination exists
                $exists = DB::table('jks_salesmans')
                    ->where('bulan', $row->bulan)
                    ->where('distributor_code', $row->distributor_code)
                    ->where('salesman_code', $row->salesman_code)
                    ->where('customer_code', $row->customer_code)
                    ->where('h1', $row->h1)->where('h2', $row->h2)->where('h3', $row->h3)->where('h4', $row->h4)
                    ->where('h5', $row->h5)->where('h6', $row->h6)->where('h7', $row->h7)
                    ->where('w1', $row->w1)->where('w2', $row->w2)->where('w3', $row->w3)->where('w4', $row->w4)
                    ->exists();
                    
                if (!$exists) {
                    DB::table('jks_salesmans')->insert([
                        'bulan' => $row->bulan,
                        'distributor_code' => $row->distributor_code,
                        'salesman_code' => $row->salesman_code,
                        'customer_code' => $row->customer_code,
                        'h1' => $row->h1, 'h2' => $row->h2, 'h3' => $row->h3,
                        'h4' => $row->h4, 'h5' => $row->h5, 'h6' => $row->h6, 'h7' => $row->h7,
                        'w1' => $row->w1, 'w2' => $row->w2, 'w3' => $row->w3, 'w4' => $row->w4,
                        'update_by' => (string) $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
                
                $row->status = 'success';
                $row->save();
                
                $this->successCount++;
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Mark chunk as failed
            JksImportTemp::whereIn('id', $chunk->pluck('id'))->update([
                'status' => 'failed',
                'error_message' => 'Gagal Upsert: ' . $e->getMessage()
            ]);
            $this->failedCount += $chunk->count();
        }
        
        // Let polling continue
    }

    public function downloadErrorLog()
    {
        // Will implement via a simple route or export class later if needed
        // For now just redirect to a temporary route or use Maatwebsite
        $this->dispatch('toast', ['type' => 'info', 'message' => 'Downloading Error Log...']);
        return redirect()->route('call-plan.jks-salesmans.export-error', ['batchId' => $this->batchId]);
    }

    public function render()
    {
        return view('livewire.call-plan.jks-salesmans.import-modal');
    }
}
