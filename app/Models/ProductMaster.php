<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductMaster extends Model
{
    use HasFactory;

    protected $table = 'product_masters';
    protected $primaryKey = 'product_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'product_id', 
        'line_id', 
        'line_name', 
        'brand_id', 
        'brand_name', 
        'product_group_id', 
        'brand_unit_name', 
        'sub_brand_id', 
        'sub_brand_name', 
        'product_name', 
        'is_active', 
        'base_unit', 
        'uom1', 'uom2', 'uom3', 
        'conv_unit1', 'conv_unit2', 'conv_unit3', 
        'price_zone1', 'price_zone2', 'price_zone3', 'price_zone4', 'price_zone5'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'conv_unit1' => 'decimal:2',
        'conv_unit2' => 'decimal:2',
        'conv_unit3' => 'decimal:2',
        'price_zone1' => 'decimal:2',
        'price_zone2' => 'decimal:2',
        'price_zone3' => 'decimal:2',
        'price_zone4' => 'decimal:2',
        'price_zone5' => 'decimal:2',
    ];

    public function productLine()
    {
        return $this->belongsTo(ProductLine::class, 'line_id', 'line_id');
    }

    public function productBrand()
    {
        return $this->belongsTo(ProductBrand::class, 'brand_id', 'brand_id');
    }

    public function productGroup()
    {
        return $this->belongsTo(ProductGroup::class, 'product_group_id', 'product_group_id');
    }

    public function productSubBrand()
    {
        return $this->belongsTo(ProductSubBrand::class, 'sub_brand_id', 'sub_brand_id');
    }

    /**
     * Relasi Many-to-Many ke Categories.
     */
    public function categories(): BelongsToMany
    {
        // [PERUBAHAN] Menambahkan withTimestamps()
        return $this->belongsToMany(Category::class, 'product_categories', 'product_id', 'category_id')
                    ->withTimestamps();
    }
}

