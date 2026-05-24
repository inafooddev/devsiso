<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccessGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * The menus that belong to the access group.
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'access_group_menu')->withTimestamps();
    }

    /**
     * Get the users associated with the access group.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
