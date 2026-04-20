<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanCallTeamElite extends Model
{
    use HasFactory;

    /**
     * Tabel yang terhubung dengan model ini.
     * Karena nama tabelnya tidak menggunakan bentuk jamak (plural) standar bahasa Inggris,
     * kita harus mendefinisikannya secara eksplisit.
     *
     * @var string
     */
    protected $table = 'plan_call_team_elite';

    /**
     * Kolom-kolom yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tanggal',
        'minggu',
        'level',
        'kode_sales',
        'cabang',
        'kode_toko',
        'nama_toko',
        'pilar',
        'target',
    ];

    /**
     * Casting tipe data atribut (kolom).
     * Mengubah kolom tertentu menjadi tipe data PHP native saat diakses.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal' => 'date',
        'target' => 'decimal:6', // Pastikan pembacaannya presisi desimal
    ];
}