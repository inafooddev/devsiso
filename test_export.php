<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $component = app(\App\Livewire\PlanCallTeamElite\ListTokoPareto::class);
    $reflection = new \ReflectionClass($component);
    $method = $reflection->getMethod('getBaseQuery');
    $method->setAccessible(true);
    
    $query = $method->invoke($component);
    
    $export = new \App\Exports\ListTokoParetoExport($query);
    
    Maatwebsite\Excel\Facades\Excel::store($export, 'test_export_pareto.xlsx', 'local');
    echo "SUCCESS: File generated at " . storage_path('app/test_export_pareto.xlsx') . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
