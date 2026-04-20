<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public $totalSales = 125000000;
    public $totalCustomers = 1248;
    public $totalOrders = 856;
    public $pendingOrders = 45;
    
    public $recentOrders = [];
    public $topProducts = [];
    
    public function mount()
    {
        // Dummy data untuk recent orders
        $this->recentOrders = [
            [
                'id' => '1001',
                'customer' => 'John Doe',
                'amount' => 1250000,
                'status' => 'Completed'
            ],
            [
                'id' => '1002',
                'customer' => 'Jane Smith',
                'amount' => 850000,
                'status' => 'Processing'
            ],
            [
                'id' => '1003',
                'customer' => 'Bob Johnson',
                'amount' => 2100000,
                'status' => 'Pending'
            ],
            [
                'id' => '1004',
                'customer' => 'Alice Brown',
                'amount' => 950000,
                'status' => 'Completed'
            ],
            [
                'id' => '1005',
                'customer' => 'Charlie Wilson',
                'amount' => 1750000,
                'status' => 'Completed'
            ],
        ];
        
        // Dummy data untuk top products
        $this->topProducts = [
            [
                'name' => 'Hitam Manis 36',
                'sales' => 145,
                'revenue' => 18500000
            ],
            [
                'name' => 'Kelapa Extra 28',
                'sales' => 230,
                'revenue' => 35000000
            ],
            [
                'name' => 'Marie Susu 20',
                'sales' => 189,
                'revenue' => 25000000
            ],
            [
                'name' => 'Fortius 10',
                'sales' => 98,
                'revenue' => 28000000
            ],
            [
                'name' => 'Goodbis 21',
                'sales' => 167,
                'revenue' => 15000000
            ],
        ];
    }
    
    public function render()
    {
        return view('livewire.dashboard')
            ->layout('layouts.app', ['title' => 'Dashboard']);
    }
}