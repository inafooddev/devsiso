<div class="mx-auto px-6 py-8">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Sales -->
        <x-card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-base-content/60 mb-1">Total Sales</p>
                    <h3 class="text-2xl font-bold text-base-content">Rp {{ number_format($totalSales, 0, ',', '.') }}</h3>
                    <p class="text-xs text-success mt-1">
                        <span class="font-semibold">+12.5%</span> from last month
                    </p>
                </div>
                <div class="flex-shrink-0 w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                    <x-heroicon-s-currency-dollar class="w-6 h-6 text-primary" />
                </div>
            </div>
        </x-card>

        <!-- Total Customers (Register Outlet) -->
        <x-card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-base-content/60 mb-1">Register Outlet</p>
                    <h3 class="text-2xl font-bold text-base-content">{{ number_format($totalCustomers) }}</h3>
                    <p class="text-xs text-success mt-1">
                        <span class="font-semibold">+8.2%</span> from last month
                    </p>
                </div>
                <div class="flex-shrink-0 w-12 h-12 bg-secondary/10 rounded-full flex items-center justify-center">
                    <x-heroicon-s-users class="w-6 h-6 text-secondary" />
                </div>
            </div>
        </x-card>

        <!-- Total Orders (Active Outlet) -->
        <x-card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-base-content/60 mb-1">Active Outlet</p>
                    <h3 class="text-2xl font-bold text-base-content">{{ number_format($totalOrders) }}</h3>
                    <p class="text-xs text-error mt-1">
                        <span class="font-semibold">-3.1%</span> from last month
                    </p>
                </div>
                <div class="flex-shrink-0 w-12 h-12 bg-accent/10 rounded-full flex items-center justify-center">
                    <x-heroicon-s-shopping-bag class="w-6 h-6 text-accent" />
                </div>
            </div>
        </x-card>

        <!-- Pending Orders (New Outlet) -->
        <x-card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-base-content/60 mb-1">New Outlet</p>
                    <h3 class="text-2xl font-bold text-base-content">{{ number_format($pendingOrders) }}</h3>
                    <p class="text-xs text-warning mt-1">
                        <span class="font-semibold">+5.3%</span> from last month
                    </p>
                </div>
                <div class="flex-shrink-0 w-12 h-12 bg-warning/10 rounded-full flex items-center justify-center">
                    <x-heroicon-s-sparkles class="w-6 h-6 text-warning" />
                </div>
            </div>
        </x-card>
    </div>

    <!-- Charts & Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Orders -->
        <x-card title="Recent Orders" flush>
            <x-slot name="actions">
                <x-ui.button variant="link" size="sm">View All</x-ui.button>
            </x-slot>

            <x-ui.table hover>
                <x-slot name="head">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </x-slot>
                @foreach($recentOrders as $order)
                <tr>
                    <td class="font-medium text-base-content">#{{ $order['id'] }}</td>
                    <td class="text-base-content/80">{{ $order['customer'] }}</td>
                    <td class="text-base-content/80">Rp {{ number_format($order['amount'], 0, ',', '.') }}</td>
                    <td>
                        @php
                            $badgeVariant = match($order['status']) {
                                'Completed' => 'success',
                                'Pending' => 'warning',
                                'Processing' => 'info',
                                default => 'neutral'
                            };
                        @endphp
                        <x-ui.badge variant="{{ $badgeVariant }}" size="sm">{{ $order['status'] }}</x-ui.badge>
                    </td>
                </tr>
                @endforeach
            </x-ui.table>
        </x-card>

        <!-- Top Products -->
        <x-card title="Top Products">
            <x-slot name="actions">
                <x-ui.button variant="link" size="sm">View All</x-ui.button>
            </x-slot>

            <div class="space-y-3">
                @foreach($topProducts as $product)
                <div class="flex items-center justify-between p-3 bg-base-200 rounded-xl hover:bg-base-300 transition-colors duration-200">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                            <x-heroicon-s-cube class="w-5 h-5 text-primary" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-base-content">{{ $product['name'] }}</p>
                            <p class="text-xs text-base-content/60">{{ $product['sales'] }} sales</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-base-content">Rp {{ number_format($product['revenue'], 0, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </x-card>
    </div>

    <!-- Quick Actions -->
    <x-card title="Quick Actions">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button class="flex flex-col items-center justify-center p-5 bg-base-200 rounded-xl hover:bg-base-300 transition-colors duration-200 group">
                <x-heroicon-s-plus-circle class="w-8 h-8 text-primary mb-3 transition-transform group-hover:scale-110" />
                <span class="text-sm font-semibold text-base-content">Add Product</span>
            </button>

            <button class="flex flex-col items-center justify-center p-5 bg-base-200 rounded-xl hover:bg-base-300 transition-colors duration-200 group">
                <x-heroicon-s-user-plus class="w-8 h-8 text-secondary mb-3 transition-transform group-hover:scale-110" />
                <span class="text-sm font-semibold text-base-content">Add Customer</span>
            </button>

            <button class="flex flex-col items-center justify-center p-5 bg-base-200 rounded-xl hover:bg-base-300 transition-colors duration-200 group">
                <x-heroicon-s-shopping-cart class="w-8 h-8 text-accent mb-3 transition-transform group-hover:scale-110" />
                <span class="text-sm font-semibold text-base-content">New Order</span>
            </button>

            <button class="flex flex-col items-center justify-center p-5 bg-base-200 rounded-xl hover:bg-base-300 transition-colors duration-200 group">
                <x-heroicon-s-document-chart-bar class="w-8 h-8 text-info mb-3 transition-transform group-hover:scale-110" />
                <span class="text-sm font-semibold text-base-content">View Reports</span>
            </button>
        </div>
    </x-card>
</div>