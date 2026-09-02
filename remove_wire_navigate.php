<?php
$dir = __DIR__ . '/resources/views/livewire/jobs';
$files = [
    $dir . '/sellout-per-cabang-sqlserver.blade.php',
    $dir . '/so-full-join.blade.php',
    $dir . '/update-ao-percabang.blade.php',
    $dir . '/update-salesmans.blade.php',
    $dir . '/update-sellin-per-cabang.blade.php',
    $dir . '/zv-so-per-toko-2026.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    // Hapus string ' wire:navigate' dari link tab menu
    $content = str_replace(' wire:navigate', '', $content);
    file_put_contents($file, $content);
    echo "Removed wire:navigate from " . basename($file) . "\n";
}
