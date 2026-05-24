<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\Menu;

class AdminRoleMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::where('name', 'admin')->first();
        
        if (!$role) {
            $this->command->error('Role admin tidak ditemukan!');
            return;
        }

        $menus = Menu::all();
        $inserts = [];

        foreach ($menus as $menu) {
            $inserts[] = [
                'menu_id' => $menu->id,
                'role_id' => $role->id,
                'can_edit' => 1,
                'can_import' => 1,
                'can_export' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Hapus akses menu lama untuk admin
        DB::table('menu_role')->where('role_id', $role->id)->delete();

        // Insert full access
        if (count($inserts) > 0) {
            DB::table('menu_role')->insert($inserts);
        }

        $this->command->info('Full access untuk role admin berhasil di-seed.');
    }
}
