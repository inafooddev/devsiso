<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceDistributor extends Model
{
    use HasFactory;

    protected $table = 'sales_invoice_distributor';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'bigint';

    public $timestamps = true; // created_at & updated_at

    // Kolom yang boleh diisi mass assignment
    protected $fillable = [
        'distributor_code', 'invoice_type', 'invoice_no', 'invoice_date',
        'order_no', 'order_date', 'salesman_code', 'salesman_name',
        'customer_code', 'customer_name', 'address', 'product_code',
        'product_name', 'carton_qty', 'pack_qty', 'pcs_qty', 'quantity',
        'unit', 'bonus', 'is_bonus', 'unit_price', 'gross_amount',
        'discount1','discount2','discount3','discount4','discount5','discount6','discount7','discount8',
        'total_discount','dpp','tax','net_amount'
    ];

        protected $casts = [
        'id' => 'integer',
        'invoice_date' => 'date',
        'order_date' => 'date',
        'carton_qty' => 'decimal:6',
        'pack_qty' => 'decimal:6',
        'pcs_qty' => 'decimal:6',
        'quantity' => 'decimal:6',
        'bonus' => 'decimal:6',
        'is_bonus' => 'boolean',
        'unit_price' => 'decimal:6',
        'gross_amount' => 'decimal:6',
        'discount1' => 'decimal:6',
        'discount2' => 'decimal:6',
        'discount3' => 'decimal:6',
        'discount4' => 'decimal:6',
        'discount5' => 'decimal:6',
        'discount6' => 'decimal:6',
        'discount7' => 'decimal:6',
        'discount8' => 'decimal:6',
        'total_discount' => 'decimal:6',
        'dpp' => 'decimal:6',
        'tax' => 'decimal:6',
        'net_amount' => 'decimal:6',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
