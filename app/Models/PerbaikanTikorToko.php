<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerbaikanTikorToko extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'perbaikan_tikor_toko';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'region_code',
        'area_code',
        'distributor_code',
        'sales_code',
        'customer_code',
        'latitude',
        'longitude',
        'status',
        'foto',
        'keterangan',
        'timestamp',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'timestamp' => 'datetime',
    ];

    /**
     * Get the distributor implementasi eskalink associated with the record.
     * die.eskalink_code = distributor_code
     */
    public function distributorImplementasiEskalink()
    {
        return $this->belongsTo(DistributorImplementasiEskalink::class, 'distributor_code', 'eskalink_code');
    }

    /**
     * Get the customer prc eska associated with the record by customer_code.
     */
    public function customerPrcEska()
    {
        return $this->belongsTo(CustomerPrcEska::class, 'customer_code', 'custno');
    }

    /**
     * Helper to query customer_prc_eska using both distributor_code and customer_code.
     * cpe.kodecabang = distributor_code AND cpe.custno = customer_code
     */
    public function customerPrcEskaQuery()
    {
        return CustomerPrcEska::where('kodecabang', $this->distributor_code)
            ->where('custno', $this->customer_code);
    }

    /**
     * Helper to query fsalesman records from the database using sales_code and distributor_code.
     * fs.SLSNO = sales_code AND fs.KD = distributor_code
     */
    public function fsalesmanQuery()
    {
        return \Illuminate\Support\Facades\DB::table('fsalesman')
            ->where('SLSNO', $this->sales_code)
            ->where('KD', $this->distributor_code);
    }
}
