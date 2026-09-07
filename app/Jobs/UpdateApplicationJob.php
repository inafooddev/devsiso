<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Models\ImportBatch;

class UpdateApplicationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 jam
    protected $batchId;

    public function __construct($batchId = null)
    {
        $this->batchId = $batchId;
    }

    public function handle(): void
    {
        $batch = null;
        if ($this->batchId) {
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                $batch->update(['status' => 'processing']);
                $batch->addLog('info', "Memulai proses Update Application...");
            }
        }

        try {
            $basePath = base_path();

            // STEP 1: Git Pull
            $this->runCommand("git pull", "Tahap 1", $basePath, $batch);

            // STEP 2: NPM Build
            $this->runCommand("npm run build", "Tahap 2", $basePath, $batch);

            // STEP 3: Optimize Clear
            $this->runCommand("php artisan optimize:clear", "Tahap 3", $basePath, $batch);

            if ($batch) {
                $batch->addLog('success', "Proses Selesai. Seluruh tahapan update aplikasi berhasil dijalankan.");
                $batch->update(['status' => 'completed']);
            }
            Log::info("UpdateApplicationJob selesai.");

        } catch (\Exception $e) {
            if ($batch) {
                $batch->addLog('error', "Terjadi kesalahan: " . $e->getMessage());
                $batch->update(['status' => 'failed']);
            }
            Log::error("UpdateApplicationJob Error: " . $e->getMessage());
            throw $e;
        }
    }

    protected function runCommand($commandStr, $stepName, $basePath, $batch)
    {
        if ($batch) $batch->addLog('warning', "Mengeksekusi $stepName: $commandStr ...");
        
        $process = Process::fromShellCommandline($commandStr);
        $process->setWorkingDirectory($basePath);
        $process->setTimeout(1800); // 30 mins

        $process->run(function ($type, $buffer) use ($batch, $commandStr) {
            // Trim to avoid empty lines
            $cleanBuffer = trim($buffer);
            if (!empty($cleanBuffer)) {
                Log::info("[$commandStr] " . $cleanBuffer);
                // Optionally log some output to UI (disabled to prevent overload, but errors can be logged)
                if (Process::ERR === $type) {
                    Log::warning("[$commandStr ERR] " . $cleanBuffer);
                }
            }
        });

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if ($batch) $batch->addLog('success', "$stepName Selesai.");
    }
}
