<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsentifHeaderGrupRegion extends Model
{
    use HasFactory;

    protected $table = 'insentif_header_grup_regions';
    protected $guarded = [];

    public function header()
    {
        return $this->belongsTo(InsentifHeaderGrup::class, 'insentif_header_grup_id', 'id');
    }
}
