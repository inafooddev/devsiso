<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'route',
        'icon',
        'parent_id',
        'order_number',
        'is_active',
    ];

    /**
     * Get the parent menu that owns this menu.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Get the child menus for this menu.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order_number');
    }

    /**
     * The users that belong to the menu.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'menu_user')->withTimestamps();
    }
}
