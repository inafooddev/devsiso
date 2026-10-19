<?php

namespace App\Livewire\JksTeamElite;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use App\Models\JksTeamElite;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JksTeamEliteTemplateExport;
use App\Imports\JksTeamEliteImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Traits\EnforcesMenuPermissions;

class ImportModal extends Component
{
    use WithFileUploads, EnforcesMenuPermissions;

    protected string $menuRoute = 'jks-team-elite.index';

    public $isImportModalOpen = false;
    
    // Import Field
    public $excel_file;
    public $importErrors = [];
    public $importStep = 1;
    public $importMethod = 'full_sync';
    public $importStartDate = '';
    public $importEndDate = '';

    // Preview Metrics
    public $previewTotalRows = 0;
    public $previewDuplicateRows = 0;
    public $previewTotalTeams = 0;
    public $previewExistingRows = 0;
    public $previewSampleData = [];
    public $previewCategorizedErrors = [];
    public $previewMinDate = null;
    public $previewMaxDate = null;
    public $isDateSpanMismatch = false;

    // Temporary storage paths & counters
    public $tempPayloadFile = null;
    public $tempErrorFile = null;
    public $totalErrorCount = 0;

    public function mount()
    {
        // Set default import dates
        $this->importStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->importEndDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    #[On('open-import-modal')]
    public function openImportModal()
    {
        $this->cleanupTempPayload();
        $this->resetValidation();
        $this->excel_file = null;
        $this->importErrors = [];
        $this->totalErrorCount = 0;
        $this->previewCategorizedErrors = [];
        $this->previewSampleData = [];
        $this->previewMinDate = null;
        $this->previewMaxDate = null;
        $this->isDateSpanMismatch = false;
        $this->importStep = 1;
        $this->isImportModalOpen = true;
    }

    protected function cleanupTempPayload()
    {
        if ($this->tempPayloadFile && Storage::disk('local')->exists($this->tempPayloadFile)) {
            Storage::disk('local')->delete($this->tempPayloadFile);
        }
        if ($this->tempErrorFile && Storage::disk('local')->exists($this->tempErrorFile)) {
            Storage::disk('local')->delete($this->tempErrorFile);
        }
        $this->tempPayloadFile = null;
        $this->tempErrorFile = null;
        $this->previewSampleData = [];

        // Auto-Purge Garbage Collection: bersihkan file temp yang berusia > 2 jam
        try {
            $files = Storage::disk('local')->files('temp');
            $twoHoursAgo = now()->subHours(2)->getTimestamp();
            foreach ($files as $file) {
                if (Storage::disk('local')->lastModified($file) < $twoHoursAgo) {
                    Storage::disk('local')->delete($file);
                }
            }
        } catch (\Exception $e) {
            // Ignore error agar tidak menghambat alur modal
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new JksTeamEliteTemplateExport, 'template_import_jks_team_elite_' . date('Ymd_His') . '.xlsx');
    }

    public function previewImport()
    {
        $this->authorizeAction('can_import');

        $this->validate([
            'excel_file' => 'required|mimes:xls,xlsx,csv|max:10240', // Maks 10MB
            'importStartDate' => 'required|date',
            'importEndDate' => 'required|date|after_or_equal:importStartDate',
            'importMethod' => 'required|in:full_sync,partial_update',
        ]);

        $this->cleanupTempPayload();

        try {
            // Pass date range to import constructor to guard against out-of-range dates
            $import = new JksTeamEliteImport($this->importStartDate, $this->importEndDate);
            Excel::import($import, $this->excel_file->getRealPath());

            if (count($import->errors) > 0 || $import->rawRowsCount === 0 || count($import->validInserts) === 0) {
                $allErrors = $import->errors;
                if ($import->rawRowsCount === 0 && empty($allErrors)) {
                    $allErrors[] = "File Excel tidak memiliki baris data yang valid atau format kolom tidak sesuai template. Pastikan kolom 'Tanggal', 'Kode Team', 'Distributor Code', dan 'CustNo' tersedia.";
                    $import->categorizedErrors['empty_required']++;
                }

                $this->totalErrorCount = count($allErrors);
                $this->previewCategorizedErrors = array_filter($import->categorizedErrors, fn($v) => $v > 0);

                // Cap error di Livewire state maksimal 50 baris untuk melindungi DOM & memori browser
                $this->importErrors = array_slice($allErrors, 0, 50);

                // Tulis seluruh error log lengkap ke file disk temporary untuk download streaming
                $errorFileName = 'temp/error_log_' . (auth()->id() ?? 'guest') . '_' . time() . '.txt';
                $errorText = "LAPORAN ERROR IMPORT JKS TEAM ELITE\n";
                $errorText .= "Tanggal Cetak: " . now()->format('Y-m-d H:i:s') . "\n";
                $errorText .= "Total Error  : " . count($allErrors) . " baris\n";
                $errorText .= str_repeat("=", 80) . "\n\n";
                foreach ($allErrors as $err) {
                    $errorText .= "- " . $err . "\n";
                }
                $errorText .= "\n" . str_repeat("=", 80) . "\n";
                $errorText .= "Silakan perbaiki data pada Excel Anda lalu lakukan import ulang.\n";

                Storage::disk('local')->put($errorFileName, $errorText);
                $this->tempErrorFile = $errorFileName;

                session()->flash('error', 'Terdapat ' . count($allErrors) . ' baris data yang bermasalah. Silakan periksa ringkasan dan download Log Error.');
            } else {
                $this->importErrors = [];
                $this->totalErrorCount = 0;
                $this->previewCategorizedErrors = [];
                $this->previewTotalRows = $import->successCount;
                $this->previewDuplicateRows = $import->duplicateCount;
                $this->previewTotalTeams = count($import->distinctTeams);
                $this->previewSampleData = $import->samplePreview;
                $this->previewMinDate = $import->minDate;
                $this->previewMaxDate = $import->maxDate;

                // Cek disparitas rentang tanggal jika Full Sync
                $this->isDateSpanMismatch = ($this->importMethod === 'full_sync') && 
                    ($this->previewMinDate > $this->importStartDate || $this->previewMaxDate < $this->importEndDate);
                
                $this->previewExistingRows = JksTeamElite::whereBetween('tanggal', [$this->importStartDate, $this->importEndDate])
                    ->whereIn('kode_team', $import->distinctTeams)
                    ->count();

                // Save verified payload to temporary storage to avoid parsing Excel twice in executeImport()
                $payload = [
                    'validInserts'       => $import->validInserts,
                    'distinctTeams'      => $import->distinctTeams,
                    'successCount'       => $import->successCount,
                    'samplePreview'      => $import->samplePreview,
                    'minDate'            => $import->minDate,
                    'maxDate'            => $import->maxDate,
                    'existingRowsCount'  => $this->previewExistingRows,
                ];

                $fileName = 'temp/import_jks_' . (auth()->id() ?? 'guest') . '_' . time() . '.json';
                Storage::disk('local')->put($fileName, json_encode($payload));
                $this->tempPayloadFile = $fileName;

                $this->importStep = 2;
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    public function executeImport()
    {
        $this->authorizeAction('can_import');

        try {
            // Read verified payload from temporary storage (Instant execution, no Excel re-parsing!)
            if (!$this->tempPayloadFile || !Storage::disk('local')->exists($this->tempPayloadFile)) {
                $this->importStep = 1;
                session()->flash('error', 'Sesi payload import telah kedaluwarsa atau file hilang. Silakan preview ulang.');
                return;
            }

            $content = Storage::disk('local')->get($this->tempPayloadFile);
            $payload = json_decode($content, true);

            if (!$payload || empty($payload['validInserts'])) {
                $this->importStep = 1;
                session()->flash('error', 'Tidak ada baris data valid yang ditemukan untuk diimport.');
                return;
            }

            $validInserts  = $payload['validInserts'];
            $distinctTeams = $payload['distinctTeams'];
            $successCount  = $payload['successCount'];

            DB::transaction(function () use ($validInserts, $distinctTeams) {
                if ($this->importMethod === 'full_sync') {
                    JksTeamElite::whereBetween('tanggal', [$this->importStartDate, $this->importEndDate])
                        ->whereIn('kode_team', $distinctTeams)
                        ->delete();
                    
                    foreach (array_chunk($validInserts, 500) as $chunk) {
                        JksTeamElite::insert($chunk);
                    }
                } else {
                    foreach (array_chunk($validInserts, 500) as $chunk) {
                        JksTeamElite::upsert(
                            $chunk, 
                            ['tanggal', 'kode_team', 'distributor_code', 'custno'], 
                            ['nama_team', 'kode_region', 'nama_region', 'kode_area', 'nama_area', 'distributor_name', 'custname', 'addres', 'updated_at']
                        );
                    }
                }
            });

            // Cleanup temp file
            $this->cleanupTempPayload();

            $teamListStr = count($distinctTeams) <= 5 
                ? implode(', ', $distinctTeams) 
                : implode(', ', array_slice($distinctTeams, 0, 5)) . ' (+' . (count($distinctTeams) - 5) . ' tim lainnya)';

            $auditMessage = "Mengimpor {$successCount} data JKS. Metode: " . strtoupper(str_replace('_', ' ', $this->importMethod))
                . " | Periode: {$this->importStartDate} s/d {$this->importEndDate}"
                . " | Tim: [{$teamListStr}]"
                . ($this->importMethod === 'full_sync' ? " | Data lama dihapus: ~{$this->previewExistingRows} baris" : "");

            \App\Helpers\ActivityLogger::log('Import JKS Team Elite', $auditMessage);

            $message = $successCount . ' Data berhasil diimport (Metode: ' . strtoupper(str_replace('_', ' ', $this->importMethod)) . ').';
            $this->isImportModalOpen = false;
            $this->excel_file = null;
            $this->importStep = 1;
            $this->importErrors = [];
            $this->totalErrorCount = 0;
            $this->previewCategorizedErrors = [];
            
            // Beritahu parent untuk reload data
            $this->dispatch('refresh-jks-data', message: $message);

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengeksekusi import: ' . $e->getMessage());
        }
    }

    public function downloadErrorLog()
    {
        $fileName = 'Error_Import_JKS_Team_Elite_' . now()->format('Ymd_His') . '.txt';

        if ($this->tempErrorFile && Storage::disk('local')->exists($this->tempErrorFile)) {
            return Storage::disk('local')->download($this->tempErrorFile, $fileName);
        }

        $errorText = "LAPORAN ERROR IMPORT JKS TEAM ELITE\n";
        $errorText .= "Tanggal Cetak: " . now()->format('Y-m-d H:i:s') . "\n";
        $errorText .= str_repeat("=", 80) . "\n\n";

        foreach ($this->importErrors as $err) {
            $errorText .= "- " . $err . "\n";
        }

        $errorText .= "\n" . str_repeat("=", 80) . "\n";
        $errorText .= "Silakan perbaiki data pada Excel Anda lalu lakukan import ulang.\n";

        return response()->streamDownload(function () use ($errorText) {
            echo $errorText;
        }, $fileName);
    }

    public function render()
    {
        return view('livewire.jks-team-elite.import-modal');
    }
}
