<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::first();
if ($user) {
    auth()->login($user);
}

try {
    // create a mock class to bypass authorization
    $comp = new class extends App\Livewire\Report\AnalisaKunjungan\Index {
        public function authorizeAction($action = 'can_edit') { return true; }
        public function getAccessLevel() { return 'region'; }
    };
    $comp->appliedRegion = 'INAJWA1';
    $comp->appliedStartDate = '2024-01-01';
    $comp->appliedEndDate = date('Y-m-d');
    $comp->activeTab = 'detail';
    
    $res = $comp->export();
    echo "Success: " . get_class($res) . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
