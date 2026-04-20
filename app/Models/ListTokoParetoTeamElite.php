<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListTokoParetoTeamElite extends Model
{
    use HasFactory;

    /**
     * Tabel yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'list_toko_pareto_team_elite';

    /**
     * Kolom-kolom yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'distributor_code',
        'customer_code_prc',
        'customer_name',
        'customer_address',
        'kecamatan',
        'desa',
        'latitude',
        'longitude',
        'pilar',
        'target',
    ];

    /**
     * Casting tipe data atribut (kolom).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'target'      => 'decimal:8',
    ];
}