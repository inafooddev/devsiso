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

    // Temporary payload storage path (to avoid double parsing Excel)
    public $tempPayloadFile = null;

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
        $this->importStep = 1;
        $this->isImportModalOpen = true;
    }

    protected function cleanupTempPayload()
    {
        if ($this->tempPayloadFile && Storage::disk('local')->exists($this->tempPayloadFile)) {
            Storage::disk('local')->delete($this->tempPayloadFile);
        }
        $this->tempPayloadFile = null;
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

            if (count($import->errors) > 0) {
                $this->importErrors = $import->errors;
                session()->flash('error', 'Terdapat ' . count($import->errors) . ' baris data yang bermasalah. Silakan download Log Error untuk melihat detailnya.');
            } else {
                $this->importErrors = [];
                $this->previewTotalRows = $import->successCount;
                $this->previewDuplicateRows = $import->duplicateCount;
                $this->previewTotalTeams = count($import->distinctTeams);
                
                $this->previewExistingRows = JksTeamElite::whereBetween('tanggal', [$this->importStartDate, $this->importEndDate])
                    ->whereIn('kode_team', $import->distinctTeams)
                    ->count();

                // Save verified payload to temporary storage to avoid parsing Excel twice in executeImport()
                $payload = [
                    'validInserts'  => $import->validInserts,
                    'distinctTeams' => $import->distinctTeams,
                    'successCount'  => $import->successCount,
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

            \App\Helpers\ActivityLogger::log('Import JKS Team Elite', "Mengimpor " . $successCount . " data JKS. Metode: {$this->importMethod}");

            $message = $successCount . ' Data berhasil diimport (Metode: ' . strtoupper(str_replace('_', ' ', $this->importMethod)) . ').';
            $this->isImportModalOpen = false;
            $this->excel_file = null;
            $this->importStep = 1;
            $this->importErrors = [];
            
            // Beritahu parent untuk reload data
            $this->dispatch('refresh-jks-data', message: $message);

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengeksekusi import: ' . $e->getMessage());
        }
    }

    public function downloadErrorLog()
    {
        $errorText = "LAPORAN ERROR IMPORT JKS TEAM ELITE\n";
        $errorText .= "Tanggal Cetak: " . now()->format('Y-m-d H:i:s') . "\n";
        $errorText .= str_repeat("=", 80) . "\n\n";

        foreach ($this->importErrors as $err) {
            $errorText .= "- " . $err . "\n";
        }

        $errorText .= "\n" . str_repeat("=", 80) . "\n";
        $errorText .= "Silakan perbaiki data pada Excel Anda lalu lakukan import ulang.\n";

        $fileName = 'Error_Import_JKS_Team_Elite_' . now()->format('Ymd_His') . '.txt';

        return response()->streamDownload(function () use ($errorText) {
            echo $errorText;
        }, $fileName);
    }

    public function render()
    {
        return view('livewire.jks-team-elite.import-modal');
    }
}
