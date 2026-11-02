<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MasterCalender extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'master_calender';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'date';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'day',
        'day_number',
        'week_year',
        'week_month',
        'month',
        'quarter',
        'semester',
        'year',
        'libur_nasional',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date:Y-m-d',
        'day_number' => 'integer',
        'week_year' => 'integer',
        'week_month' => 'integer',
        'month' => 'integer',
        'quarter' => 'integer',
        'semester' => 'integer',
        'year' => 'integer',
        'libur_nasional' => 'boolean',
    ];

    /**
     * Daftar nama hari Indonesia
     */
    public static array $dayNames = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    /**
     * Daftar nama bulan Indonesia
     */
    public static array $monthNames = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Helper untuk menghitung atribut kalender secara otomatis dari tanggal
     */
    public static function calculateAttributes(string $dateString): array
    {
        $carbon = Carbon::parse($dateString);
        $dayNum = $carbon->dayOfWeekIso; // 1 = Senin, ..., 7 = Minggu
        $dayName = self::$dayNames[$dayNum] ?? $carbon->locale('id')->isoFormat('dddd');
        $month = $carbon->month;
        $year = $carbon->year;
        $quarter = (int) ceil($month / 3);
        $semester = $month <= 6 ? 1 : 2;

        // Cek data jika sudah tersimpan di database
        $existing = self::find($dateString);
        if ($existing) {
            return [
                'day' => $existing->day,
                'day_number' => $existing->day_number,
                'week_year' => $existing->week_year,
                'week_month' => $existing->week_month,
                'month' => $existing->month,
                'quarter' => $existing->quarter,
                'semester' => $existing->semester,
                'year' => $existing->year,
                'libur_nasional' => (bool) $existing->libur_nasional,
            ];
        }

        // Cek record tepat 1 hari sebelumnya jika ada
        $prevDate = $carbon->copy()->subDay()->format('Y-m-d');
        $prevRecord = self::find($prevDate);

        if ($prevRecord) {
            // Minggu dimulai pada hari Minggu (day_number 7)
            if ($dayNum == 7) {
                $weekYear = $prevRecord->week_year + 1;
                $weekMonth = ($prevRecord->week_month % 4) + 1;
            } else {
                $weekYear = $prevRecord->week_year;
                $weekMonth = $prevRecord->week_month;
            }

            return [
                'day' => $dayName,
                'day_number' => $dayNum,
                'week_year' => $weekYear,
                'week_month' => $weekMonth,
                'month' => $month,
                'quarter' => $quarter,
                'semester' => $semester,
                'year' => $year,
                'libur_nasional' => ($dayNum == 7),
            ];
        }

        // Estimasi default
        $startOfYear = Carbon::create($year, 1, 1);
        $dayOfYear = $carbon->dayOfYear;
        $weekYear = max(1, (int) ceil($dayOfYear / 7));
        $weekMonth = (($weekYear - 1) % 4) + 1;

        return [
            'day' => $dayName,
            'day_number' => $dayNum,
            'week_year' => $weekYear,
            'week_month' => $weekMonth,
            'month' => $month,
            'quarter' => $quarter,
            'semester' => $semester,
            'year' => $year,
            'libur_nasional' => ($dayNum == 7),
        ];
    }
}
