<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsentifHeaderGrup extends Model
{
    use HasFactory;

    protected $table = 'insentif_header_grups';
    protected $guarded = [];

    public function details()
    {
        return $this->hasMany(InsentifHeaderGrupDetail::class, 'insentif_header_grup_id', 'id');
    }

    public function regions()
    {
        return $this->hasMany(InsentifHeaderGrupRegion::class, 'insentif_header_grup_id', 'id');
    }
}
