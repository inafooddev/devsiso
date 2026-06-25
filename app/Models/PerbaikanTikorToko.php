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
        'source',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
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
     * Get the exact customer prc eska record using both distributor_code and customer_code.
     * Use $item->exact_customer instead of $item->customerPrcEska to avoid wrong branch data.
     */
    public function getExactCustomerAttribute()
    {
        if (!array_key_exists('exact_customer', $this->relations)) {
            $customer = CustomerPrcEska::where('kodecabang', $this->distributor_code)
                ->where('custno', $this->customer_code)
                ->first();
            
            if (!$customer) {
                // Fallback to list_toko_pareto_team_elite for Tim Elite submissions
                $eliteCustomer = \Illuminate\Support\Facades\DB::table('list_toko_pareto_team_elite')
                    ->where('distributor_code', $this->distributor_code)
                    ->where('customer_code_prc', $this->customer_code)
                    ->first();
                    
                if ($eliteCustomer) {
                    $customer = (object) [
                        'custname' => $eliteCustomer->customer_name,
                        'la' => $eliteCustomer->latitude,
                        'lg' => $eliteCustomer->longitude,
                    ];
                }
            }

            $this->setRelation('exact_customer', $customer);
        }
        return $this->getRelation('exact_customer');
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
