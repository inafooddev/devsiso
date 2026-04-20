<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_categories';

    // [PERUBAHAN] Default Laravel sudah menganggap 'id' sebagai primary key
    // dan auto-incrementing integer, jadi properti di bawah ini
    // tidak perlu didefinisikan secara eksplisit lagi.
    // protected $primaryKey = 'id';
    // protected $keyType = 'string';
    // public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // 'id' tidak perlu di-fillable karena auto-increment
        'product_id',
        'category_id',
    ];

    /**
     * Get the product master associated with the category mapping.
     */
    public function productMaster()
    {
        return $this->belongsTo(ProductMaster::class, 'product_id', 'product_id');
    }

    /**
     * Get the category associated with the product mapping.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }
}

