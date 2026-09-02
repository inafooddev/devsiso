<?php
$dir = __DIR__ . '/resources/views/livewire/jobs';
$files = [
    $dir . '/sellout-per-cabang-sqlserver.blade.php',
    $dir . '/update-ao-percabang.blade.php',
    $dir . '/update-salesmans.blade.php',
    $dir . '/update-sellin-per-cabang.blade.php',
    $dir . '/zv-so-per-toko-2026.blade.php',
    $dir . '/zv-summary-team-elite.blade.php'
];

$search = <<<EOT
                    <button wire:click="startProcess" wire:loading.attr="disabled" wire:target="startProcess"
                        class="btn btn-sm btn-primary rounded-xl normal-case shadow-sm shadow-primary/20">
EOT;

$replace = <<<EOT
                    <button wire:click="startProcess" 
                        wire:loading.attr="disabled" 
                        wire:target="startProcess"
                        {{ in_array(\$batchStatus, ['pending', 'processing']) ? 'disabled' : '' }}
                        class="btn btn-sm btn-primary rounded-xl normal-case shadow-sm shadow-primary/20 {{ in_array(\$batchStatus, ['pending', 'processing']) ? 'opacity-50 cursor-not-allowed' : '' }}">
EOT;

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    $newContent = str_replace($search, $replace, $content);
    
    // Also handle if they are on one line
    $search2 = '<button wire:click="startProcess" wire:loading.attr="disabled" wire:target="startProcess" class="btn btn-sm btn-primary rounded-xl normal-case shadow-sm shadow-primary/20">';
    $newContent = str_replace($search2, $replace, $newContent);
    
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Updated button in " . basename($file) . "\n";
    }
}
