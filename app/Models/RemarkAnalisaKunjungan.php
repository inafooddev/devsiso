<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemarkAnalisaKunjungan extends Model
{
    use HasFactory;

    protected $table = 'remark_analisa_kunjungan';

    protected $fillable = [
        'visit_id',
        'muid',
        'custno',
        'tanggal',
        'remark',
        'created_by'
    ];
}
