<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JksTeamElite extends Model
{
    use HasFactory;

    /**
     * Tabel yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'jks_team_elite';

    /**
     * Kolom-kolom yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tanggal',
        'kode_team',
        'nama_team',
        'kode_region',
        'nama_region',
        'kode_area',
        'nama_area',
        'distributor_code',
        'distributor_name',
        'custno',
        'custname',
        'addres',
    ];

    /**
     * Casting tipe data atribut (kolom).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal' => 'date',
    ];
}
