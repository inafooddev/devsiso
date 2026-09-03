<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportReaktivasiToko extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'v_report_reaktivasi_toko';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
