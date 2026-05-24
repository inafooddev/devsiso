<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'userid',
        'name',
        'email',
        'password',
        'region_code',
        'access_group_id',
    ];

    protected $casts = [
        'region_code' => 'array',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the access group associated with the user.
     */
    public function accessGroup(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AccessGroup::class);
    }

    /**
     * The menus that belong to the user.
     */
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_user')->withTimestamps();
    }

    protected $menuAccessCache = null;

    /**
     * Check if user has specific access to a menu route.
     * Actions: can_view, can_edit, can_import, can_export.
     */
    public function hasMenuAccess($routeName, $action = 'can_view')
    {
        if ($this->menuAccessCache === null) {
            $this->menuAccessCache = [];
            
            // Group menus (View only)
            if ($this->accessGroup) {
                $groupMenus = $this->accessGroup->menus()->get();
                foreach($groupMenus as $m) {
                    if ($m->route) {
                        $this->menuAccessCache[$m->route] = [
                            'can_view' => true,
                            'can_edit' => false,
                            'can_import' => false,
                            'can_export' => false,
                        ];
                    }
                }
            }

            // Role menus with pivot permissions
            $roleIds = $this->roles->pluck('id');
            if ($roleIds->isNotEmpty()) {
                $roleMenus = \App\Models\Menu::whereHas('roles', function($q) use ($roleIds) {
                    $q->whereIn('roles.id', $roleIds);
                })->with(['roles' => function($q) use ($roleIds) {
                    $q->whereIn('roles.id', $roleIds);
                }])->get();

                foreach($roleMenus as $m) {
                    if ($m->route) {
                        if (!isset($this->menuAccessCache[$m->route])) {
                            $this->menuAccessCache[$m->route] = [
                                'can_view' => false,
                                'can_edit' => false,
                                'can_import' => false,
                                'can_export' => false,
                            ];
                        }
                        
                        foreach($m->roles as $r) {
                            if ($r->pivot->can_edit) $this->menuAccessCache[$m->route]['can_edit'] = true;
                            if ($r->pivot->can_import) $this->menuAccessCache[$m->route]['can_import'] = true;
                            if ($r->pivot->can_export) $this->menuAccessCache[$m->route]['can_export'] = true;
                        }
                    }
                }
            }
        }

        return !empty($this->menuAccessCache[$routeName][$action]);
    }
}
