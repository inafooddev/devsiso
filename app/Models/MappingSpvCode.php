<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MappingSpvCode extends Model
{
    use HasFactory;

    /**
     * Tabel yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'mapping_spv_code';

    /**
     * Kolom-kolom yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'branch_code',
        'supervisor_code',
    ];
}