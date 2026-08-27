<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\JksMasterCustomerImport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JksMasterCustomerImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout

    protected $filePath;
    protected $isAdmin;
    protected $allowedDistributors;
    protected $importMethod;
    protected $jobId;

    public function __construct($filePath, $isAdmin, $allowedDistributors, $importMethod, $jobId)
    {
        $this->filePath = $filePath;
        $this->isAdmin = $isAdmin;
        $this->allowedDistributors = $allowedDistributors;
        $this->importMethod = $importMethod;
        $this->jobId = $jobId;
    }

    public function handle()
    {
        try {
            $import = new JksMasterCustomerImport($this->isAdmin, $this->allowedDistributors, $this->importMethod, $this->jobId);
            
            Log::info("Starting Background Excel::import with file {$this->filePath} for Job ID {$this->jobId}");
            Excel::import($import, $this->filePath, 'local');
            Log::info("Background Excel::import finished for Job ID {$this->jobId}");

            // The 'completed' status should ideally be set after the entire file is processed.
            // Since Excel::import is synchronous inside this job, we can set it here.
            $cacheKey = "import_progress_{$this->jobId}";
            $progress = Cache::get($cacheKey, [
                'status' => 'processing',
                'success' => 0,
                'insert' => 0,
                'update' => 0,
                'skip' => 0,
                'error' => 0,
                'logs' => [],
                'skipLogs' => []
            ]);

            $progress['status'] = 'completed';
            $progress['success'] = $import->successCount;
            $progress['insert'] = $import->insertCount;
            $progress['update'] = $import->updateCount;
            $progress['skip'] = $import->skipCount;
            $progress['error'] = count($import->errorLogs);

            Cache::put($cacheKey, $progress, 3600);
            
        } catch (\Exception $e) {
            Log::error("JksMasterCustomerImportJob failed: " . $e->getMessage());
            $cacheKey = "import_progress_{$this->jobId}";
            $progress = Cache::get($cacheKey, [
                'status' => 'processing',
                'success' => 0,
                'insert' => 0,
                'update' => 0,
                'skip' => 0,
                'error' => 0,
                'logs' => [],
                'skipLogs' => []
            ]);
            $progress['status'] = 'failed';
            $progress['error']++;
            $progress['logs'][] = "CRITICAL ERROR: " . $e->getMessage();
            Cache::put($cacheKey, $progress, 3600);
        }
    }
}
