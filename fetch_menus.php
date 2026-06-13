<?php
$menus = \App\Models\Menu::all()->toArray();
file_put_contents('menus_dump.json', json_encode($menus, JSON_PRETTY_PRINT));
echo "Done";
