<?php

namespace App\Livewire\MasterData\MasterCalenders;

use Livewire\Component;
use App\Models\MasterCalender;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use App\Traits\EnforcesMenuPermissions;
use App\Exports\MasterCalendersExport;
use App\Exports\MasterCalendersTemplateExport;
use App\Imports\MasterCalendersImport;
use App\Helpers\ActivityLogger;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination, EnforcesMenuPermissions, WithFileUploads;

    protected $paginationTheme = 'tailwind';
    protected string $menuRoute = 'master-calenders.index';

    // Filters
    public $search = '';
    public $yearFilter = '';
    public $monthFilter = '';
    public $liburFilter = '';
    public $weekMonthFilter = '';
    public $perPage = 50;

    // Modals
    public $isFormModalOpen = false;
    public $isEditing = false;
    public $isDeleteModalOpen = false;
    public $isImportModalOpen = false;
    public $isGenerateModalOpen = false;

    // Form fields
    public $date;
    public $originalDate;
    public $day;
    public $day_number;
    public $week_year;
    public $week_month;
    public $month;
    public $quarter;
    public $semester;
    public $year;
    public $libur_nasional = false;

    // Delete & Import state
    public $dateToDelete;
    public $importFile;

    // Generator state
    public $generateYear;
    public $generateStartDate;
    public $generateEndDate;
    public $generateStartWeekYear = 1;
    public $generateStartWeekMonth = 1;
    public $overwriteExisting = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'yearFilter' => ['except' => ''],
        'monthFilter' => ['except' => ''],
        'liburFilter' => ['except' => ''],
        'weekMonthFilter' => ['except' => ''],
    ];

    public function mount()
    {
        // Default tahun filter ke tahun sekarang jika ada data, atau tahun terbaru
        $latestYear = MasterCalender::max('year') ?? date('Y');
        $this->yearFilter = (string) $latestYear;

        $nextYear = ((int)$latestYear) + 1;
        $this->generateYear = $nextYear;
        $this->generateStartDate = "{$nextYear}-01-01";
        $this->generateEndDate = "{$nextYear}-12-31";
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatedYearFilter() { $this->resetPage(); }
    public function updatedMonthFilter() { $this->resetPage(); }
    public function updatedLiburFilter() { $this->resetPage(); }
    public function updatedWeekMonthFilter() { $this->resetPage(); }
    public function updatedPerPage() { $this->resetPage(); }

    /**
     * Auto calculate values when user enters a date in form
     */
    public function updatedDate($value)
    {
        if (empty($value)) return;

        try {
            $attrs = MasterCalender::calculateAttributes($value);
            $this->day = $attrs['day'];
            $this->day_number = $attrs['day_number'];
            $this->month = $attrs['month'];
            $this->quarter = $attrs['quarter'];
            $this->semester = $attrs['semester'];
            $this->year = $attrs['year'];
            $this->week_year = $attrs['week_year'];
            $this->week_month = $attrs['week_month'];
            if (!$this->isEditing) {
                $this->libur_nasional = $attrs['libur_nasional'];
            }
        } catch (\Exception $e) {
            // Abaikan jika format belum selesai diketik
        }
    }

    /**
     * Sync generator start/end date when generateYear changes
     */
    public function updatedGenerateYear($val)
    {
        if (is_numeric($val) && $val >= 2000) {
            $this->generateStartDate = "{$val}-01-01";
            $this->generateEndDate = "{$val}-12-31";
        }
    }

    /**
     * Validation rules
     */
    protected function rules()
    {
        return [
            'date' => [
                'required',
                'date',
                $this->isEditing 
                    ? Rule::unique('master_calender', 'date')->ignore($this->originalDate, 'date')
                    : Rule::unique('master_calender', 'date'),
            ],
            'day' => 'required|string|max:15',
            'day_number' => 'required|integer|between:1,7',
            'week_year' => 'required|integer|min:1',
            'week_month' => 'required|integer|between:1,6',
            'month' => 'required|integer|between:1,12',
            'quarter' => 'required|integer|between:1,4',
            'semester' => 'required|integer|between:1,2',
            'year' => 'required|integer|min:2000',
            'libur_nasional' => 'boolean',
        ];
    }

    protected function messages()
    {
        return [
            'date.required' => 'Tanggal harus diisi.',
            'date.unique' => 'Tanggal ini sudah terdaftar dalam sistem.',
            'day.required' => 'Nama hari harus diisi.',
            'day_number.required' => 'Nomor hari harus diisi (1-7).',
            'week_year.required' => 'Minggu tahun harus diisi.',
            'week_month.required' => 'Minggu bulan harus diisi.',
            'month.required' => 'Bulan harus diisi.',
            'quarter.required' => 'Kuartal harus diisi.',
            'semester.required' => 'Semester harus diisi.',
            'year.required' => 'Tahun harus diisi.',
        ];
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEditing = false;
        
        // Default tanggal ke hari ini atau hari esok
        $defaultDate = Carbon::today()->format('Y-m-d');
        $this->date = $defaultDate;
        $this->updatedDate($defaultDate);

        $this->isFormModalOpen = true;
    }

    public function openEditModal($dateString)
    {
        $this->resetValidation();
        $record = MasterCalender::findOrFail($dateString);

        $dateFormatted = $record->date instanceof Carbon 
            ? $record->date->format('Y-m-d') 
            : substr((string)$record->date, 0, 10);

        $this->originalDate = $dateFormatted;
        $this->date = $dateFormatted;
        $this->day = $record->day;
        $this->day_number = $record->day_number;
        $this->week_year = $record->week_year;
        $this->week_month = $record->week_month;
        $this->month = $record->month;
        $this->quarter = $record->quarter;
        $this->semester = $record->semester;
        $this->year = $record->year;
        $this->libur_nasional = (bool)$record->libur_nasional;

        $this->isEditing = true;
        $this->isFormModalOpen = true;
    }

    private function resetForm()
    {
        $this->originalDate = null;
        $this->date = null;
        $this->day = null;
        $this->day_number = null;
        $this->week_year = null;
        $this->week_month = null;
        $this->month = null;
        $this->quarter = null;
        $this->semester = null;
        $this->year = null;
        $this->libur_nasional = false;
    }

    public function save()
    {
        $this->authorizeAction('can_edit');
        $this->validate();

        $data = [
            'date' => $this->date,
            'day' => $this->day,
            'day_number' => $this->day_number,
            'week_year' => $this->week_year,
            'week_month' => $this->week_month,
            'month' => $this->month,
            'quarter' => $this->quarter,
            'semester' => $this->semester,
            'year' => $this->year,
            'libur_nasional' => (bool)$this->libur_nasional,
        ];

        if ($this->isEditing) {
            $record = MasterCalender::findOrFail($this->originalDate);
            $record->update($data);
            ActivityLogger::log('Update Master Calender', "Memperbarui kalender tanggal {$this->date} ({$this->day})");
            session()->flash('message', "Data kalender tanggal {$this->date} berhasil diperbarui.");
        } else {
            MasterCalender::create($data);
            ActivityLogger::log('Create Master Calender', "Menambahkan tanggal baru: {$this->date} ({$this->day})");
            session()->flash('message', "Tanggal {$this->date} berhasil ditambahkan ke kalender.");
        }

        $this->isFormModalOpen = false;
        $this->resetForm();
    }

    public function confirmDelete($dateString)
    {
        $this->dateToDelete = $dateString;
        $this->isDeleteModalOpen = true;
    }

    public function delete()
    {
        $this->authorizeAction('can_edit');

        if (!$this->dateToDelete) {
            $this->isDeleteModalOpen = false;
            return;
        }

        try {
            $record = MasterCalender::find($this->dateToDelete);
            if ($record) {
                $record->delete();
                ActivityLogger::log('Delete Master Calender', "Menghapus kalender tanggal {$this->dateToDelete}");
                session()->flash('message', "Data kalender tanggal {$this->dateToDelete} berhasil dihapus.");
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus data kalender: ' . $e->getMessage());
        }

        $this->isDeleteModalOpen = false;
        $this->dateToDelete = null;
    }

    /**
     * Quick toggle libur nasional status directly from table row
     */
    public function toggleLiburNasional($dateString)
    {
        $this->authorizeAction('can_edit');

        $record = MasterCalender::find($dateString);
        if ($record) {
            $record->libur_nasional = !$record->libur_nasional;
            $record->save();

            $statusText = $record->libur_nasional ? 'Libur Nasional' : 'Hari Kerja';
            ActivityLogger::log('Toggle Libur Master Calender', "Ubah status tanggal {$dateString} menjadi {$statusText}");
            session()->flash('message', "Status tanggal {$dateString} diubah menjadi {$statusText}.");
        }
    }

    public function openGenerateModal()
    {
        $this->isGenerateModalOpen = true;
    }

    /**
     * Bulk generate calendar for a whole year or date range
     */
    public function generateYearlyCalendar()
    {
        $this->authorizeAction('can_edit');

        $this->validate([
            'generateStartDate' => 'required|date',
            'generateEndDate' => 'required|date|after_or_equal:generateStartDate',
            'generateStartWeekYear' => 'required|integer|min:1',
            'generateStartWeekMonth' => 'required|integer|between:1,6',
        ]);

        $start = Carbon::parse($this->generateStartDate);
        $end = Carbon::parse($this->generateEndDate);

        $current = $start->copy();
        $createdCount = 0;
        $updatedCount = 0;

        $weekYear = (int)$this->generateStartWeekYear;
        $weekMonth = (int)$this->generateStartWeekMonth;

        DB::beginTransaction();
        try {
            while ($current->lte($end)) {
                $dateStr = $current->format('Y-m-d');
                $dayNum = $current->dayOfWeekIso; // 1 = Senin, ..., 7 = Minggu
                $dayName = MasterCalender::$dayNames[$dayNum] ?? $current->locale('id')->isoFormat('dddd');
                $month = $current->month;
                $year = $current->year;
                $quarter = (int) ceil($month / 3);
                $semester = $month <= 6 ? 1 : 2;

                // Jika hari Minggu (dayNum 7), mulai minggu baru
                if ($current->gt($start) && $dayNum == 7) {
                    $weekYear++;
                    $weekMonth = ($weekMonth % 4) + 1;
                }

                $existing = MasterCalender::find($dateStr);

                if ($existing) {
                    if ($this->overwriteExisting) {
                        $existing->update([
                            'day' => $dayName,
                            'day_number' => $dayNum,
                            'week_year' => $weekYear,
                            'week_month' => $weekMonth,
                            'month' => $month,
                            'quarter' => $quarter,
                            'semester' => $semester,
                            'year' => $year,
                            'libur_nasional' => ($dayNum == 7),
                        ]);
                        $updatedCount++;
                    }
                } else {
                    MasterCalender::create([
                        'date' => $dateStr,
                        'day' => $dayName,
                        'day_number' => $dayNum,
                        'week_year' => $weekYear,
                        'week_month' => $weekMonth,
                        'month' => $month,
                        'quarter' => $quarter,
                        'semester' => $semester,
                        'year' => $year,
                        'libur_nasional' => ($dayNum == 7),
                    ]);
                    $createdCount++;
                }

                $current->addDay();
            }

            DB::commit();

            ActivityLogger::log('Generate Calendar', "Generate kalender periode {$this->generateStartDate} s/d {$this->generateEndDate}. Baru: {$createdCount}, Update: {$updatedCount}");
            session()->flash('message', "Kalender berhasil digenerate! Baru ditambahkan: {$createdCount} hari, Diperbarui: {$updatedCount} hari.");
            $this->isGenerateModalOpen = false;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal generate kalender: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $this->authorizeAction('can_view');

        return Excel::download(new MasterCalendersExport([
            'search' => $this->search,
            'yearFilter' => $this->yearFilter,
            'monthFilter' => $this->monthFilter,
            'liburFilter' => $this->liburFilter,
            'weekMonthFilter' => $this->weekMonthFilter,
        ]), 'Data_Master_Calender_' . date('Ymd_His') . '.xlsx');
    }

    public function downloadTemplate()
    {
        $this->authorizeAction('can_view');
        return Excel::download(new MasterCalendersTemplateExport, 'Template_Import_Master_Calender.xlsx');
    }

    public function import()
    {
        $this->authorizeAction('can_edit');

        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $import = new MasterCalendersImport();
            Excel::import($import, $this->importFile);

            $message = "Proses Import Selesai. Berhasil: {$import->importedCount}. Gagal/Dilewati: {$import->skippedCount}.";
            ActivityLogger::log('Import Master Calender', "Import data Kalender. Sukses: {$import->importedCount}, Skip: {$import->skippedCount}");

            if ($import->skippedCount > 0) {
                session()->flash('error', $message . ' (Periksa kelengkapan file Excel Anda)');
            } else {
                session()->flash('message', $message);
            }

            $this->isImportModalOpen = false;
            $this->importFile = null;
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal melakukan import data: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = MasterCalender::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('date', 'ilike', '%' . $this->search . '%')
                  ->orWhere('day', 'ilike', '%' . $this->search . '%');
            });
        }

        if (!empty($this->yearFilter)) {
            $query->where('year', $this->yearFilter);
        }

        if (!empty($this->monthFilter)) {
            $query->where('month', $this->monthFilter);
        }

        if ($this->liburFilter !== '' && $this->liburFilter !== null) {
            $query->where('libur_nasional', (bool)$this->liburFilter);
        }

        if (!empty($this->weekMonthFilter)) {
            $query->where('week_month', $this->weekMonthFilter);
        }

        // Hitung ringkasan statistik dari query terfilter
        $totalDays = (clone $query)->count();
        $totalLibur = (clone $query)->where('libur_nasional', true)->count();
        $totalKerja = $totalDays - $totalLibur;

        $calenders = $query->orderBy('date', 'asc')->paginate($this->perPage);

        // Opsi tahun yang tersedia di DB
        $availableYears = MasterCalender::select('year')
            ->distinct()
            ->whereNotNull('year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [(int)date('Y')];
        }

        return view('livewire.master-data.master-calenders.index', [
            'calenders' => $calenders,
            'availableYears' => $availableYears,
            'totalDays' => $totalDays,
            'totalLibur' => $totalLibur,
            'totalKerja' => $totalKerja,
            'monthNames' => MasterCalender::$monthNames,
        ])->layout('layouts.app');
    }
}
