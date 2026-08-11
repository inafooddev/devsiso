<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class NewSellIn extends Component
{
    // Filter properties
    public string $selectedYear = '2024';
    public string $selectedEntity = 'ALL'; // Could be 'National', 'Area', 'Cabang', 'Supervisor'

    // Dropdown options
    public array $yearOptions = ['2022', '2023', '2024', '2025'];
    public array $entitiesOption = ['ALL' => 'National', 'AREA_1' => 'Area 1', 'CAB_A' => 'Cabang A'];

    // Stats
    public array $kpiData = [
        'target' => 1500000000,
        'actual' => 1250000000,
        'achievement' => 83.33,
        'growth' => 12.5,
    ];

    // Dummy Chart JSON
    public string $chartSalesTrend = '[]';
    public string $chartContribution = '[]';

    public function mount()
    {
        $this->loadDummyData();
    }

    public function applyFilter()
    {
        // Dummy action for filter
        $this->loadDummyData();
        $this->dispatch('charts-updated');
    }

    private function loadDummyData()
    {
        // 1. Sales Trend Area Chart
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $actuals = [];
        $targets = [];
        
        // Generate random realistic looking sales data
        $base = 80000000;
        for ($i = 0; $i < 12; $i++) {
            $targets[] = $base * 1.1;
            $actuals[] = $base * (rand(85, 120) / 100);
            $base *= 1.05; // slight upward trend
        }

        $this->chartSalesTrend = json_encode([
            'labels' => $months,
            'actuals' => $actuals,
            'targets' => $targets,
        ]);

        // 2. Contribution Chart (Donut)
        $this->chartContribution = json_encode([
            'labels' => ['Jawa Timur', 'Jawa Barat', 'Jawa Tengah', 'Sumatera', 'Sulawesi'],
            'series' => [45, 25, 15, 10, 5],
        ]);
    }

    public function render()
    {
        // Dummy table data
        $tableData = [
            ['name' => 'John Doe', 'role' => 'Supervisor', 'target' => 500000, 'actual' => 550000, 'ach' => 110],
            ['name' => 'Jane Smith', 'role' => 'Supervisor', 'target' => 600000, 'actual' => 450000, 'ach' => 75],
            ['name' => 'Budi Santoso', 'role' => 'Cabang', 'target' => 1200000, 'actual' => 1150000, 'ach' => 95.8],
        ];

        return view('livewire.dashboard.new-sell-in', [
            'tableData' => $tableData
        ])->layout('layouts.app', ['title' => 'Unified Sell-In Dashboard']);
    }
}
