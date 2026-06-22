<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilAuditToko extends Model
{
    use HasFactory;

    protected $table = 'hasil_audit_toko';

    protected $fillable = [
        'auditor',
        'distributor_code',
        'customer_code',
        'customer_name',
        'customer_address',
        'latitude',
        'longitude',
        'foto_audit1',
        'foto_audit2',
        'foto_audit3',
        'keterangan_hasil_audit',
    ];
}
