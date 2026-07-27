<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankGaransi extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_code',
        'nama_bank',
        'nomor_jaminan',
        'nomor_seri',
        'nilai_jaminan',
        'tanggal_terbit',
        'tanggal_jatuh_tempo',
        'status_perpanjangan',
        'keterangan',
        'dokumen_lampiran',
        'progress_status',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'nilai_jaminan' => 'decimal:2',
    ];

    public function getStatusAttribute()
    {
        if (!$this->tanggal_jatuh_tempo) {
            return 'Aktif';
        }
        
        return $this->tanggal_jatuh_tempo->endOfDay()->isPast() ? 'Expired' : 'Aktif';
    }

    public function distributor()
    {
        return $this->belongsTo(MasterDistributor::class, 'distributor_code', 'distributor_code');
    }

    public function followUps()
    {
        return $this->hasMany(BankGaransiFollowUp::class, 'bank_garansi_id')->orderBy('created_at', 'desc');
    }
}
