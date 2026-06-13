@php
    $currentRoute = Route::currentRouteName();
    // Jika sedang dalam proses request Livewire (XHR), Route name akan berubah menjadi livewire.update
    // Jadi kita gunakan path dari referer untuk mengembalikan identitas halamannya.
    if (in_array($currentRoute, ['livewire.update', 'livewire.message']) || empty($currentRoute)) {
        $referer = request()->header('referer', '');
        if (str_contains($referer, '/sales-configs')) {
            $currentRoute = 'sales-configs.index';
        } elseif (str_contains($referer, '/import-sales-invoices')) {
            $currentRoute = 'sales-invoices.import';
        } elseif (str_contains($referer, '/sales-invoice-report') || str_contains($referer, '/sell-out')) {
            $currentRoute = 'sales-invoice-report.index';
        }
    }
@endphp

@canImport('sales-invoice-report.index')
<div role="tablist" class="tabs tabs-boxed w-fit bg-base-200/60 p-1 rounded-lg shadow-sm border border-base-300 gap-1 -mb-1 md:-mb-2 lg:-mb-4">
  <a role="tab" 
     href="{{ route('sales-invoice-report.index') }}" 
     class="tab tab-sm px-3 rounded-md {{ $currentRoute === 'sales-invoice-report.index' ? 'tab-active !bg-primary !text-primary-content font-bold shadow-sm pointer-events-none cursor-default' : 'hover:bg-base-300/60 text-base-content/70 font-medium' }} transition-all">
    <x-heroicon-o-chart-bar class="w-3.5 h-3.5 mr-1.5" />
    Summary
  </a>

  <a role="tab" 
     href="{{ route('sales-invoices.import') }}" 
     class="tab tab-sm px-3 rounded-md {{ $currentRoute === 'sales-invoices.import' ? 'tab-active !bg-primary !text-primary-content font-bold shadow-sm pointer-events-none cursor-default' : 'hover:bg-base-300/60 text-base-content/70 font-medium' }} transition-all">
    <x-heroicon-o-arrow-up-tray class="w-3.5 h-3.5 mr-1.5" />
    Import
  </a>
  
  <a role="tab" 
     href="{{ route('sales-configs.index') }}" 
     class="tab tab-sm px-3 rounded-md {{ str_starts_with($currentRoute, 'sales-configs') ? 'tab-active !bg-primary !text-primary-content font-bold shadow-sm pointer-events-none cursor-default' : 'hover:bg-base-300/60 text-base-content/70 font-medium' }} transition-all">
    <x-heroicon-o-cog-6-tooth class="w-3.5 h-3.5 mr-1.5" />
    Config
  </a>
</div>
@endcanImport
