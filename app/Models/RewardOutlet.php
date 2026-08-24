<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardOutlet extends Model
{
    use HasFactory;

    protected $table = 'reward_outlet';

    protected $fillable = [
        'region_code',
        'region_name',
        'area_code',
        'area_name',
        'branch_name',
        'eskalink_code',
        'customer_code',
        'customer_name',
        'alamat',
        'no_hp',
        'latitude',
        'longitude',
        'nama_pemilik_toko',
        'nama_ktp',
        'nik_ktp',
        'foto_ktp',
        'nama_bank',
        'no_rekening',
        'nama_pemilik_norek',
        'foto_toko',
        'foto_toko2',
        'foto_toko3',
        'keterangan',
        'is_valid',
        'validasi_rekening',
        'finance_by',
        'finance_note',
        'finance_noted_at',
        'finalized_at',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'validasi_rekening' => 'boolean',
        'finance_noted_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];


    /**
     * Get dynamic status based on completeness of required identity/bank fields.
     */
    public function getStatusAttribute()
    {
        // 11 field yang wajib — konsisten dengan definisi KPI Cards & scopeFilterType('lengkap')
        $stringFields = [
            $this->no_hp,
            $this->nama_pemilik_toko,
            $this->nama_ktp,
            $this->nik_ktp,
            $this->foto_ktp,
            $this->no_rekening,
            $this->nama_bank,
            $this->nama_pemilik_norek,
            $this->foto_toko2,
            $this->foto_toko3,
        ];

        foreach ($stringFields as $field) {
            if (empty(trim($field ?? ''))) {
                return 'Not Complete';
            }
        }

        // Koordinat tikor (dihitung 1 item)
        if (empty(trim($this->latitude ?? '')) || empty(trim($this->longitude ?? ''))) {
            return 'Not Complete';
        }

        return 'Complete';
    }

    /**
     * Scope for filtering completeness of data
     */
    public function scopeFilterType($query, string $type)
    {
        return match ($type) {
            // Filter from summary table
            'lengkap' => $query->whereRaw("TRIM(COALESCE(no_hp, '')) NOT IN ('', '0')")
                               ->whereRaw("TRIM(COALESCE(nama_pemilik_toko, '')) NOT IN ('', '0')")
                               ->whereRaw("TRIM(COALESCE(nama_ktp, '')) NOT IN ('', '0')")
                               ->whereRaw("TRIM(COALESCE(nik_ktp, '')) NOT IN ('', '0')")
                               ->whereRaw("TRIM(COALESCE(foto_ktp, '')) NOT IN ('', '0')")
                               ->whereRaw("TRIM(COALESCE(no_rekening, '')) NOT IN ('', '0')")
                               ->whereRaw("TRIM(COALESCE(nama_bank, '')) NOT IN ('', '0')")
                               ->whereRaw("TRIM(COALESCE(nama_pemilik_norek, '')) NOT IN ('', '0')")
                               ->whereRaw("TRIM(COALESCE(foto_toko2, '')) NOT IN ('', '0')")
                               ->whereRaw("TRIM(COALESCE(foto_toko3, '')) NOT IN ('', '0')")
                               ->whereRaw("TRIM(COALESCE(latitude, '')) NOT IN ('', '0')")
                               ->whereRaw("TRIM(COALESCE(longitude, '')) NOT IN ('', '0')"),
            'belum_lengkap' => $query->where(function($q) {
                                $q->whereRaw("TRIM(COALESCE(no_hp, '')) IN ('', '0')")
                                  ->orWhereRaw("TRIM(COALESCE(nama_pemilik_toko, '')) IN ('', '0')")
                                  ->orWhereRaw("TRIM(COALESCE(nama_ktp, '')) IN ('', '0')")
                                  ->orWhereRaw("TRIM(COALESCE(nik_ktp, '')) IN ('', '0')")
                                  ->orWhereRaw("TRIM(COALESCE(foto_ktp, '')) IN ('', '0')")
                                  ->orWhereRaw("TRIM(COALESCE(no_rekening, '')) IN ('', '0')")
                                  ->orWhereRaw("TRIM(COALESCE(nama_bank, '')) IN ('', '0')")
                                  ->orWhereRaw("TRIM(COALESCE(nama_pemilik_norek, '')) IN ('', '0')")
                                  ->orWhereRaw("TRIM(COALESCE(foto_toko2, '')) IN ('', '0')")
                                  ->orWhereRaw("TRIM(COALESCE(foto_toko3, '')) IN ('', '0')")
                                  ->orWhereRaw("TRIM(COALESCE(latitude, '')) IN ('', '0')")
                                  ->orWhereRaw("TRIM(COALESCE(longitude, '')) IN ('', '0')");
                               }),
            'missing_no_hp' => $query->whereRaw("TRIM(COALESCE(no_hp, '')) IN ('', '0')"),
            'missing_nama_pemilik_toko' => $query->whereRaw("TRIM(COALESCE(nama_pemilik_toko, '')) IN ('', '0')"),
            'missing_nama_ktp' => $query->whereRaw("TRIM(COALESCE(nama_ktp, '')) IN ('', '0')"),
            'missing_nik_ktp' => $query->whereRaw("TRIM(COALESCE(nik_ktp, '')) IN ('', '0')"),
            'missing_foto_ktp' => $query->whereRaw("TRIM(COALESCE(foto_ktp, '')) IN ('', '0')"),
            'missing_no_rekening' => $query->whereRaw("TRIM(COALESCE(no_rekening, '')) IN ('', '0')"),
            'missing_nama_bank' => $query->whereRaw("TRIM(COALESCE(nama_bank, '')) IN ('', '0')"),
            'missing_nama_pemilik_norek' => $query->whereRaw("TRIM(COALESCE(nama_pemilik_norek, '')) IN ('', '0')"),
            'missing_foto_toko' => $query->whereRaw("TRIM(COALESCE(foto_toko, '')) IN ('', '0')"),
            'missing_is_valid' => $query->where(fn($q) => $q->where('is_valid', false)->orWhereNull('is_valid')),
            
            // Filter from index toolbar & KPI Cards
            'tanpa_ktp' => $query->whereRaw("TRIM(COALESCE(nik_ktp, '')) IN ('', '0')"),
            'tanpa_foto_ktp' => $query->whereRaw("TRIM(COALESCE(foto_ktp, '')) IN ('', '0')"),
            'tanpa_rekening' => $query->where(fn($q) => $q->where('validasi_rekening', false)->orWhereNull('validasi_rekening')),
            'tanpa_foto_toko' => $query->whereRaw("TRIM(COALESCE(foto_toko, '')) IN ('', '0')"),
            'tanpa_tikor' => $query->where(fn($q) => $q->whereRaw("TRIM(COALESCE(latitude, '')) IN ('', '0')")->orWhereRaw("TRIM(COALESCE(longitude, '')) IN ('', '0')")),
            'tidak_valid' => $query->where(fn($q) => $q->where('is_valid', false)->orWhereNull('is_valid')),
            'valid' => $query->where('is_valid', true),
            'complete' => $query->where(fn($q) => $q->whereNotNull('nama_pemilik_toko')->where('nama_pemilik_toko', '!=', '')
                               ->whereNotNull('nama_ktp')->where('nama_ktp', '!=', '')
                               ->whereNotNull('nik_ktp')->where('nik_ktp', '!=', '')
                               ->whereNotNull('nama_bank')->where('nama_bank', '!=', '')
                               ->whereNotNull('no_rekening')->where('no_rekening', '!=', '')
                               ->whereNotNull('nama_pemilik_norek')->where('nama_pemilik_norek', '!=', '')),
            'not_complete' => $query->where(fn($q) => $q->whereNull('nama_pemilik_toko')->orWhere('nama_pemilik_toko', '')
                               ->orWhereNull('nama_ktp')->orWhere('nama_ktp', '')
                               ->orWhereNull('nik_ktp')->orWhere('nik_ktp', '')
                               ->orWhereNull('nama_bank')->orWhere('nama_bank', '')
                               ->orWhereNull('no_rekening')->orWhere('no_rekening', '')
                               ->orWhereNull('nama_pemilik_norek')->orWhere('nama_pemilik_norek', '')),
            default => $query
        };
    }

    /**
     * Scope for searching
     */
    public function scopeSearch($query, string $search)
    {
        if (empty(trim($search))) {
            return $query;
        }

        return $query->where(function($q) use ($search) {
            $q->where('region_name', 'ilike', '%' . $search . '%')
              ->orWhere('area_name', 'ilike', '%' . $search . '%')
              ->orWhere('branch_name', 'ilike', '%' . $search . '%')
              ->orWhere('customer_name', 'ilike', '%' . $search . '%')
              ->orWhere('customer_code', 'ilike', '%' . $search . '%')
              ->orWhere('eskalink_code', 'ilike', '%' . $search . '%');
        });
    }

    /**
     * Helper to check if the record is finalized by Finance.
     */
    public function isFinalized(): bool
    {
        return !is_null($this->finalized_at);
    }

    /**
     * Relationship: The user (Finance) who reviewed/finalized this record.
     */
    public function financeBy()
    {
        return $this->belongsTo(User::class, 'finance_by');
    }
}

