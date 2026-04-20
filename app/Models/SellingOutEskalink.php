<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellingOutEskalink extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model ini.
     */
    protected $table = 'selling_out_eskalink';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     */
    protected $fillable = [
        'region_code',
        'region_name',
        'entity_code',
        'entity_name',
        'branch_code',
        'branch_name',
        'area_code',
        'area_name',
        'sales_code',
        'sales_name',
        'cust_code_prc',
        'cust_code_dist',
        'cust_name',
        'cust_address',
        'cust_city',
        'sub_channel',
        'type_outlet',
        'ord_no',
        'ord_date',
        'invoice_no',
        'invoice_type',
        'invoice_date',
        'prd_brand',
        'product_group_1',
        'product_group_2',
        'product_group_3',
        'prd_code',
        'prd_name',
        'qty1_car',
        'qty2_pck',
        'qty3_pcs',
        'qty4_pcs',
        'qty5_pcs',
        'flag_bonus',
        'gross_amount',
        'line_discount_1',
        'line_discount_2',
        'line_discount_3',
        'line_discount_4',
        'line_discount_5',
        'line_discount_6',
        'line_discount_7',
        'line_discount_8',
        'total_line_discount',
        'dpp',
        'tax',
        'nett_amount',
        'category_item',
        'vtkp',
        'npd',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data asli.
     */
    protected $casts = [
        'ord_date' => 'date',
        'invoice_date' => 'date',
        'qty1_car' => 'decimal:2',
        'qty2_pck' => 'decimal:2',
        'qty3_pcs' => 'decimal:2',
        'qty4_pcs' => 'decimal:2',
        'qty5_pcs' => 'decimal:2',
        'gross_amount' => 'decimal:6',
        'line_discount_1' => 'decimal:6',
        'line_discount_2' => 'decimal:6',
        'line_discount_3' => 'decimal:6',
        'line_discount_4' => 'decimal:6',
        'line_discount_5' => 'decimal:6',
        'line_discount_6' => 'decimal:6',
        'line_discount_7' => 'decimal:6',
        'line_discount_8' => 'decimal:6',
        'total_line_discount' => 'decimal:6',
        'dpp' => 'decimal:6',
        'tax' => 'decimal:6',
        'nett_amount' => 'decimal:6',
    ];
}