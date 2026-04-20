<style>
    /* Custom Scrollbar Tipis & Elegan untuk Sidebar */
    .sidebar-scroll::-webkit-scrollbar {
        width: 5px;
    }
    .sidebar-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .sidebar-scroll::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 10px;
        transition: background 0.3s ease;
    }
    .sidebar-scroll:hover::-webkit-scrollbar-thumb {
        background: rgba(34, 211, 238, 0.4); /* Cyan glow on hover */
    }
</style>

<aside 
    x-data="{ isPinned: false }"
    x-init="sidebarOpen = false"
    @mouseenter="sidebarOpen = true"
    @mouseleave="if(!isPinned) sidebarOpen = false"
    :class="sidebarOpen ? 'w-64' : 'w-20'" 
    class="bg-[#081c3a] text-white transition-[width] duration-500 ease-in-out flex-shrink-0 hidden md:flex flex-col shadow-2xl relative z-40 border-r border-white/5">
    
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 px-4 border-b border-white/10 relative overflow-hidden bg-gradient-to-b from-white/5 to-transparent">
        <!-- Full Logo -->
        <img x-show="sidebarOpen"
             x-transition:enter="transition ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             src="{{ asset('build/assets/logo.png') }}"
             class="h-10 absolute"
             x-cloak>

        <!-- Icon Logo -->
        <img x-show="!sidebarOpen"
             x-transition:enter="transition ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 -translate-x-4 scale-75"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             src="{{ asset('build/assets/logo-icon.png') }}"
             class="h-8 absolute"
             x-cloak>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto sidebar-scroll py-6 px-3 space-y-1.5">
        
        <!-- Dashboard -->
        <div x-data="{ open: @json(request()->routeIs(['dashboard', 'dashboard.distributor-map'])) }">
            <button @click="open = !open"
                    class="group w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-300 ease-out {{ request()->routeIs(['dashboard', 'dashboard.distributor-map']) ? 'bg-gradient-to-r from-cyan-500/10 to-transparent border-l-[3px] border-cyan-400 text-white shadow-md' : 'border-l-[3px] border-transparent text-slate-300 hover:bg-white/5 hover:text-white' }}">
                <div class="flex items-center overflow-hidden">
                    <svg class="w-5 h-5 flex-shrink-0 transition-all duration-300 ease-out group-hover:scale-110 {{ request()->routeIs(['dashboard', 'dashboard.distributor-map']) ? 'text-cyan-400 drop-shadow-[0_0_8px_rgba(34,211,238,0.5)]' : 'text-slate-400 group-hover:text-cyan-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <!-- Modern Dashboard Grid Icon -->
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span x-show="sidebarOpen" 
                          x-transition.opacity.duration.300ms
                          class="ml-3 font-medium whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1" x-cloak>
                          Dashboard
                    </span>
                </div>
                <svg x-show="sidebarOpen" :class="open ? 'rotate-180 text-cyan-400' : 'text-slate-500'"
                     class="w-4 h-4 transition-transform duration-300 ease-in-out group-hover:text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <!-- Submenu Dashboard -->
            <ul x-show="open && sidebarOpen" 
                x-transition:enter="transition-all ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition-all ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
                class="mt-1.5 space-y-1 ml-4 relative before:absolute before:inset-y-0 before:left-[9px] before:w-[1px] before:bg-slate-700/50" x-cloak>
                <li>
                    <a href="{{ route('dashboard') }}"
                       class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ request()->routeIs('dashboard') ? 'bg-cyan-500/10 text-cyan-300 font-medium' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                        <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ request()->routeIs('dashboard') ? 'scale-150 bg-cyan-400 shadow-[0_0_6px_rgba(34,211,238,0.8)]' : 'bg-slate-600 group-hover:bg-cyan-400' }}"></div>
                        <span class="whitespace-nowrap transition-transform duration-300">aioueo</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.metabase') }}" 
                       class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ request()->routeIs('dashboard.metabase') ? 'bg-cyan-500/10 text-cyan-300 font-medium' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                        <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ request()->routeIs('dashboard.metabase') ? 'scale-150 bg-cyan-400 shadow-[0_0_6px_rgba(34,211,238,0.8)]' : 'bg-slate-600 group-hover:bg-cyan-400' }}"></div>
                        <span class="whitespace-nowrap transition-transform duration-300">Titik Distribusi</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Selling In -->
        <a href="{{ route('selling-in.report') }}"
           class="group w-full flex items-center px-4 py-3 rounded-xl transition-all duration-300 ease-out {{ request()->routeIs('selling-in.report') ? 'bg-gradient-to-r from-cyan-500/10 to-transparent border-l-[3px] border-cyan-400 text-white shadow-md' : 'border-l-[3px] border-transparent text-slate-300 hover:bg-white/5 hover:text-white' }}">
            <svg class="w-5 h-5 flex-shrink-0 transition-all duration-300 ease-out group-hover:scale-110 {{ request()->routeIs('selling-in.report') ? 'text-cyan-400 drop-shadow-[0_0_8px_rgba(34,211,238,0.5)]' : 'text-slate-400 group-hover:text-cyan-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <!-- Inbox/Inbound Box Icon -->
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-medium whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1" x-cloak>Selling In</span>
        </a>

        <!-- Sales Invoice (Selling Out) -->
        @php
            $salesInvoiceActive = request()->routeIs(['sales-configs.*', 'sales-invoices.*', 'sales-invoice-report.index', 'sell-out.process', 'sell-out.process-v2']);
        @endphp
        <div x-data="{ open: @json($salesInvoiceActive) }">
            <button @click="open = !open"
                    class="group w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-300 ease-out {{ $salesInvoiceActive ? 'bg-gradient-to-r from-cyan-500/10 to-transparent border-l-[3px] border-cyan-400 text-white shadow-md' : 'border-l-[3px] border-transparent text-slate-300 hover:bg-white/5 hover:text-white' }}">
                <div class="flex items-center overflow-hidden">
                    <svg class="w-5 h-5 flex-shrink-0 transition-all duration-300 ease-out group-hover:scale-110 {{ $salesInvoiceActive ? 'text-cyan-400 drop-shadow-[0_0_8px_rgba(34,211,238,0.5)]' : 'text-slate-400 group-hover:text-cyan-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <!-- Shopping Cart / Outbound Icon -->
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span x-show="sidebarOpen" 
                        x-transition.opacity.duration.300ms
                        class="ml-3 font-medium whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1" x-cloak>
                        Selling Out
                    </span>
                </div>
                <svg x-show="sidebarOpen" :class="open ? 'rotate-180 text-cyan-400' : 'text-slate-500'"
                    class="w-4 h-4 transition-transform duration-300 ease-in-out group-hover:text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            
            <ul x-show="open && sidebarOpen" 
                x-transition:enter="transition-all ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition-all ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
                class="mt-1.5 space-y-1 ml-4 relative before:absolute before:inset-y-0 before:left-[9px] before:w-[1px] before:bg-slate-700/50" x-cloak>
                
                <!-- Menu: Import -->
                <li>
                    <a href="{{ route('sales-invoice-report.index') }}" 
                    class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ request()->routeIs('sales-invoice-report.index') ? 'bg-cyan-500/10 text-cyan-300 font-medium' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                        <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ request()->routeIs('sales-invoice-report.index') ? 'scale-150 bg-cyan-400 shadow-[0_0_6px_rgba(34,211,238,0.8)]' : 'bg-slate-600 group-hover:bg-cyan-400' }}"></div>
                        <span class="whitespace-nowrap">Import</span>
                    </a>
                </li>

                <!-- Menu: Generate (Grouping - Warna Amber/Oranye Sesuai Permintaan) -->
                @php
                    $generateActive = request()->routeIs(['sell-out.process', 'sell-out.process-v2']);
                @endphp
                <li x-data="{ openGenerate: @json($generateActive) }">
                    <button @click="openGenerate = !openGenerate" 
                            class="w-full group flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ $generateActive ? 'bg-amber-500/10 text-amber-400 font-medium' : 'text-amber-500/80 hover:text-amber-300 hover:bg-white/5' }}">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ $generateActive ? 'scale-150 bg-amber-400 shadow-[0_0_6px_rgba(251,191,36,0.8)]' : 'bg-amber-700/60 group-hover:bg-amber-400' }}"></div>
                            <span class="whitespace-nowrap">Generate</span>
                        </div>
                        <svg :class="openGenerate ? 'rotate-180 text-amber-400' : 'text-amber-600'"
                            class="w-3.5 h-3.5 transition-transform duration-300 ease-in-out group-hover:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <ul x-show="openGenerate" 
                        x-transition:enter="transition-all ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition-all ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                        class="mt-1 space-y-1 ml-5 relative before:absolute before:inset-y-0 before:left-1 before:w-[1px] before:bg-amber-800/30" x-cloak>
                        
                        <li>
                            <a href="{{ route('sell-out.process') }}" 
                            class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ request()->routeIs('sell-out.process') ? 'text-amber-300' : 'text-slate-400 hover:text-amber-100 hover:bg-white/5' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('sell-out.process') ? 'scale-150 bg-amber-400' : 'bg-slate-600 group-hover:bg-amber-400' }}"></div>
                                <span class="whitespace-nowrap">Eska Version</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('sell-out.process-v2') }}" 
                            class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ request()->routeIs('sell-out.process-v2') ? 'text-amber-300' : 'text-slate-400 hover:text-amber-100 hover:bg-white/5' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('sell-out.process-v2') ? 'scale-150 bg-amber-400' : 'bg-slate-600 group-hover:bg-amber-400' }}"></div>
                                <span class="whitespace-nowrap">Default Version</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

        <!-- Settings (Master Data) -->
        @php
            $masterAreaActive = request()->routeIs(['master-regions.index', 'master-areas.index', 'master-supervisors.index', 'master-branches.index', 'master-distributors.index']);
            $masterProductActive = request()->routeIs(['product-lines.index', 'product-brands.index', 'product-groups.index', 'product-sub-brands.index', 'product-masters.index', 'categories.index', 'product-categories.index']);
            $masterSalesmanActive = request()->routeIs('salesmans.index');
            $masterCustomerActive = request()->routeIs(['under-construction', 'under-bounce']);
            $masterDataActive = $masterAreaActive || $masterProductActive || $masterSalesmanActive || $masterCustomerActive;

            $mappingDataActive = request()->routeIs(['product-mappings.index', 'salesman-mappings.index', 'customers.data', 'under-construction']);
            $unmappingDataActive = request()->routeIs(['mapping.unmapped-products', 'mapping.unmapped-salesmans']);
            $settingsActive = $masterDataActive || $mappingDataActive || $unmappingDataActive;
        @endphp

        <div x-data="{ openSettings: @json($settingsActive) }" class="mt-2">
            <button @click="openSettings = !openSettings"
                    class="group w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-300 ease-out {{ $settingsActive ? 'bg-gradient-to-r from-cyan-500/10 to-transparent border-l-[3px] border-cyan-400 text-white shadow-md' : 'border-l-[3px] border-transparent text-slate-300 hover:bg-white/5 hover:text-white' }}">
                <div class="flex items-center overflow-hidden">
                    <svg class="w-5 h-5 flex-shrink-0 transition-all duration-300 ease-out group-hover:scale-110 {{ $settingsActive ? 'text-cyan-400 drop-shadow-[0_0_8px_rgba(34,211,238,0.5)]' : 'text-slate-400 group-hover:text-cyan-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <!-- Database / Master Data Icon -->
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                    </svg>
                    <span x-show="sidebarOpen" 
                        x-transition.opacity.duration.300ms
                        class="ml-3 font-medium whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1" x-cloak>
                        Master Data
                    </span>
                </div>
                <svg x-show="sidebarOpen" :class="openSettings ? 'rotate-180 text-cyan-400' : 'text-slate-500'"
                    class="w-4 h-4 transition-transform duration-300 ease-in-out group-hover:text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            
            <ul x-show="openSettings && sidebarOpen" 
                x-transition:enter="transition-all ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition-all ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
                class="mt-1.5 space-y-1 ml-4 relative before:absolute before:inset-y-0 before:left-[9px] before:w-[1px] before:bg-slate-700/50" x-cloak>
                
                <!-- 1. Master Data Sub-Menu (Grouping - Amber) -->
                <li x-data="{ openMaster: @json($masterDataActive) }">
                    <button @click="openMaster = !openMaster" 
                            class="w-full group flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ $masterDataActive ? 'bg-amber-500/10 text-amber-400 font-medium' : 'text-amber-500/80 hover:text-amber-300 hover:bg-white/5' }}">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ $masterDataActive ? 'scale-150 bg-amber-400 shadow-[0_0_6px_rgba(251,191,36,0.8)]' : 'bg-amber-700/60 group-hover:bg-amber-400' }}"></div>
                            <span class="whitespace-nowrap">Master</span>
                        </div>
                        <svg :class="openMaster ? 'rotate-180 text-amber-400' : 'text-amber-600'"
                            class="w-3.5 h-3.5 transition-transform duration-300 ease-in-out group-hover:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <ul x-show="openMaster" 
                        x-transition:enter="transition-all ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition-all ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                        class="mt-1 space-y-1 ml-5 relative before:absolute before:inset-y-0 before:left-1 before:w-[1px] before:bg-amber-800/30" x-cloak>
                        
                        <!-- Distribusi (Level 3 Grouping - Emerald) -->
                        <li x-data="{ openDist: @json($masterAreaActive) }">
                            <button @click="openDist = !openDist" class="group w-full flex items-center justify-between px-4 py-2 rounded-xl transition-all duration-300 hover:bg-white/5 text-sm {{ $masterAreaActive ? 'bg-emerald-500/10 text-emerald-400 font-medium' : 'text-emerald-500/80 hover:text-emerald-300 hover:bg-white/5' }}">
                                <div class="flex items-center">
                                    <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ $masterAreaActive ? 'scale-150 bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.8)]' : 'bg-emerald-700/60 group-hover:bg-emerald-400' }}"></div>
                                    <span class="whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1">Distribusi</span>
                                </div>
                                <svg :class="openDist ? 'rotate-180 text-emerald-400' : 'text-emerald-600'" class="w-3 h-3 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <ul x-show="openDist" class="mt-1 space-y-1 ml-6 relative before:absolute before:inset-y-0 before:-left-1 before:w-[1px] before:bg-emerald-800/30" x-cloak>
                                <li><a href="{{ route('master-regions.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-white/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('master-regions.index') ? 'text-emerald-300' : 'text-slate-400 hover:text-emerald-100' }}"><span class="whitespace-nowrap">Region</span></a></li>
                                <li><a href="{{ route('master-areas.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-white/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('master-areas.index') ? 'text-emerald-300' : 'text-slate-400 hover:text-emerald-100' }}"><span class="whitespace-nowrap">Area</span></a></li>
                                <li><a href="{{ route('master-supervisors.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-white/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('master-supervisors.index') ? 'text-emerald-300' : 'text-slate-400 hover:text-emerald-100' }}"><span class="whitespace-nowrap">Supervisor</span></a></li>
                                <li><a href="{{ route('master-branches.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-white/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('master-branches.index') ? 'text-emerald-300' : 'text-slate-400 hover:text-emerald-100' }}"><span class="whitespace-nowrap">Cabang</span></a></li>
                                <li><a href="{{ route('master-distributors.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-white/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('master-distributors.index') ? 'text-emerald-300' : 'text-slate-400 hover:text-emerald-100' }}"><span class="whitespace-nowrap">Distributor</span></a></li>
                            </ul>
                        </li>
                        
                        <!-- Product (Level 3 Grouping - Emerald) -->
                        <li x-data="{ openProd: @json($masterProductActive) }">
                            <button @click="openProd = !openProd" class="group w-full flex items-center justify-between px-4 py-2 rounded-xl transition-all duration-300 hover:bg-white/5 text-sm {{ $masterProductActive ? 'bg-emerald-500/10 text-emerald-400 font-medium' : 'text-emerald-500/80 hover:text-emerald-300 hover:bg-white/5' }}">
                                <div class="flex items-center">
                                    <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ $masterProductActive ? 'scale-150 bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.8)]' : 'bg-emerald-700/60 group-hover:bg-emerald-400' }}"></div>
                                    <span class="whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1">Product</span>
                                </div>
                                <svg :class="openProd ? 'rotate-180 text-emerald-400' : 'text-emerald-600'" class="w-3 h-3 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <ul x-show="openProd" class="mt-1 space-y-1 ml-6 relative before:absolute before:inset-y-0 before:-left-1 before:w-[1px] before:bg-emerald-800/30" x-cloak>
                                <li><a href="{{ route('product-lines.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-white/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('product-lines.index') ? 'text-emerald-300' : 'text-slate-400 hover:text-emerald-100' }}"><span class="whitespace-nowrap">Line</span></a></li>
                                <li><a href="{{ route('product-brands.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-white/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('product-brands.index') ? 'text-emerald-300' : 'text-slate-400 hover:text-emerald-100' }}"><span class="whitespace-nowrap">Brand</span></a></li>
                                <li><a href="{{ route('product-groups.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-white/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('product-groups.index') ? 'text-emerald-300' : 'text-slate-400 hover:text-emerald-100' }}"><span class="whitespace-nowrap">Grup</span></a></li>
                                <li><a href="{{ route('product-sub-brands.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-white/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('product-sub-brands.index') ? 'text-emerald-300' : 'text-slate-400 hover:text-emerald-100' }}"><span class="whitespace-nowrap">Sub Brand</span></a></li>
                                <li><a href="{{ route('product-masters.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-white/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('product-masters.index') ? 'text-emerald-300' : 'text-slate-400 hover:text-emerald-100' }}"><span class="whitespace-nowrap">Product Master</span></a></li>
                                <li><a href="{{ route('categories.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-white/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('categories.index') ? 'text-emerald-300' : 'text-slate-400 hover:text-emerald-100' }}"><span class="whitespace-nowrap">Kategory</span></a></li>
                                <li><a href="{{ route('product-categories.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-white/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('product-categories.index') ? 'text-emerald-300' : 'text-slate-400 hover:text-emerald-100' }}"><span class="whitespace-nowrap">Product Kategori</span></a></li>
                            </ul>
                        </li>

                        <!-- Salesmen (Level 3 Normal - Amber Tint) -->
                        <li>
                            <a href="{{ route('salesmans.index') }}" 
                            class="group w-full flex items-center justify-between px-4 py-2 rounded-xl hover:bg-white/5 hover:translate-x-1 transition-all duration-300 text-sm {{ request()->routeIs('salesmans.index') ? 'text-amber-300' : 'text-slate-400 hover:text-amber-100' }}">
                                <div class="flex items-center">
                                    <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('salesmans.index') ? 'scale-150 bg-amber-400' : 'bg-slate-600 group-hover:bg-amber-400' }}"></div>
                                    <span class="whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1">Salesmen</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- 2. Mapping Data Sub-Menu (Grouping - Amber) -->
                <li x-data="{ openMapping: @json($mappingDataActive) }">
                    <button @click="openMapping = !openMapping" 
                            class="w-full group flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ $mappingDataActive ? 'bg-amber-500/10 text-amber-400 font-medium' : 'text-amber-500/80 hover:text-amber-300 hover:bg-white/5' }}">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ $mappingDataActive ? 'scale-150 bg-amber-400 shadow-[0_0_6px_rgba(251,191,36,0.8)]' : 'bg-amber-700/60 group-hover:bg-amber-400' }}"></div>
                            <span class="whitespace-nowrap">Mapping</span>
                        </div>
                        <svg :class="openMapping ? 'rotate-180 text-amber-400' : 'text-amber-600'"
                            class="w-3.5 h-3.5 transition-transform duration-300 ease-in-out group-hover:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <ul x-show="openMapping" 
                        x-transition:enter="transition-all ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition-all ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                        class="mt-1 space-y-1 ml-5 relative before:absolute before:inset-y-0 before:left-1 before:w-[1px] before:bg-amber-800/30" x-cloak>
                        
                        <li>
                            <a href="{{ route('product-mappings.index') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-white/5 hover:translate-x-1 text-sm {{ request()->routeIs('product-mappings.index') ? 'text-amber-300' : 'text-slate-400 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('product-mappings.index') ? 'scale-150 bg-amber-400' : 'bg-slate-600 group-hover:bg-amber-400' }}"></div>
                                <span class="whitespace-nowrap">Product</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('salesman-mappings.index') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-white/5 hover:translate-x-1 text-sm {{ request()->routeIs('salesman-mappings.index') ? 'text-amber-300' : 'text-slate-400 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('salesman-mappings.index') ? 'scale-150 bg-amber-400' : 'bg-slate-600 group-hover:bg-amber-400' }}"></div>
                                <span class="whitespace-nowrap">Salesmen</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- 3. Unmapping Data Sub-Menu (Grouping - Amber) -->
                <li x-data="{ openUnmapping: @json($unmappingDataActive) }">
                    <button @click="openUnmapping = !openUnmapping" 
                            class="w-full group flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ $unmappingDataActive ? 'bg-amber-500/10 text-amber-400 font-medium' : 'text-amber-500/80 hover:text-amber-300 hover:bg-white/5' }}">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ $unmappingDataActive ? 'scale-150 bg-amber-400 shadow-[0_0_6px_rgba(251,191,36,0.8)]' : 'bg-amber-700/60 group-hover:bg-amber-400' }}"></div>
                            <span class="whitespace-nowrap">Unmapping</span>
                        </div>
                        <svg :class="openUnmapping ? 'rotate-180 text-amber-400' : 'text-amber-600'"
                            class="w-3.5 h-3.5 transition-transform duration-300 ease-in-out group-hover:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <ul x-show="openUnmapping" 
                        x-transition:enter="transition-all ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition-all ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                        class="mt-1 space-y-1 ml-5 relative before:absolute before:inset-y-0 before:left-1 before:w-[1px] before:bg-amber-800/30" x-cloak>
                        
                        <li>
                            <a href="{{ route('mapping.unmapped-products') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-white/5 hover:translate-x-1 text-sm {{ request()->routeIs('mapping.unmapped-products') ? 'text-amber-300' : 'text-slate-400 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('mapping.unmapped-products') ? 'scale-150 bg-amber-400' : 'bg-slate-600 group-hover:bg-amber-400' }}"></div>
                                <span class="whitespace-nowrap">Product</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('mapping.unmapped-salesmans') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-white/5 hover:translate-x-1 text-sm {{ request()->routeIs('mapping.unmapped-salesmans') ? 'text-amber-300' : 'text-slate-400 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('mapping.unmapped-salesmans') ? 'scale-150 bg-amber-400' : 'bg-slate-600 group-hover:bg-amber-400' }}"></div>
                                <span class="whitespace-nowrap">Salesmen</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

        <!-- Eskalink Main Menu -->
        @php
            $customerEskalinkActive = request()->routeIs(['customer-principle.index', 'customer-eska-unmap.index', 'customer-eska.index', 'customer-eska-dist.index', 'customer-eska-map.index', 'customer.csv.export']);
            $custUnmapActive = request()->routeIs(['customer.csv.export', 'customer-eska-unmap.index']);
            $produkEskalinkActive = request()->routeIs(['produk-eska.index', 'produk-eska-map.index']);
            $sellingOutEskaActive = request()->routeIs('dashboard.sales-comparison');
            $eskalinkActive = $customerEskalinkActive || $produkEskalinkActive || $sellingOutEskaActive;
        @endphp

        <div x-data="{ openEskalink: @json($eskalinkActive) }" class="mt-2">
            <button @click="openEskalink = !openEskalink"
                    class="group w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-300 ease-out {{ $eskalinkActive ? 'bg-gradient-to-r from-cyan-500/10 to-transparent border-l-[3px] border-cyan-400 text-white shadow-md' : 'border-l-[3px] border-transparent text-slate-300 hover:bg-white/5 hover:text-white' }}">
                <div class="flex items-center overflow-hidden">
                    <svg class="w-5 h-5 flex-shrink-0 transition-all duration-300 ease-out group-hover:scale-110 {{ $eskalinkActive ? 'text-cyan-400 drop-shadow-[0_0_8px_rgba(34,211,238,0.5)]' : 'text-slate-400 group-hover:text-cyan-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <!-- Globe / Network Icon -->
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-medium whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1" x-cloak>Eskalink</span>
                </div>
                <svg x-show="sidebarOpen" :class="openEskalink ? 'rotate-180 text-cyan-400' : 'text-slate-500'" class="w-4 h-4 transition-transform duration-300 ease-in-out group-hover:text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            
            <ul x-show="openEskalink && sidebarOpen" 
                x-transition:enter="transition-all ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition-all ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
                class="mt-1.5 space-y-1 ml-4 relative before:absolute before:inset-y-0 before:left-[9px] before:w-[1px] before:bg-slate-700/50" x-cloak>
                
                <!-- Sub-Menu 1: Customer Eskalink (Grouping - Amber) -->
                <li x-data="{ openCustEska: @json($customerEskalinkActive) }">
                    <button @click="openCustEska = !openCustEska" 
                            class="w-full group flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ $customerEskalinkActive ? 'bg-amber-500/10 text-amber-400 font-medium' : 'text-amber-500/80 hover:text-amber-300 hover:bg-white/5' }}">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ $customerEskalinkActive ? 'scale-150 bg-amber-400 shadow-[0_0_6px_rgba(251,191,36,0.8)]' : 'bg-amber-700/60 group-hover:bg-amber-400' }}"></div>
                            <span class="whitespace-nowrap">Customer</span>
                        </div>
                        <svg :class="openCustEska ? 'rotate-180 text-amber-400' : 'text-amber-600'" class="w-3.5 h-3.5 transition-transform duration-300 ease-in-out group-hover:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <ul x-show="openCustEska" 
                        x-transition:enter="transition-all ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition-all ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                        class="mt-1 space-y-1 ml-5 relative before:absolute before:inset-y-0 before:left-1 before:w-[1px] before:bg-amber-800/30" x-cloak>
                        
                        <li>
                            <a href="{{ route('customer-eska.index') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-white/5 hover:translate-x-1 text-sm {{ request()->routeIs('customer-eska.index') ? 'text-amber-300' : 'text-slate-400 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('customer-eska.index') ? 'scale-150 bg-amber-400' : 'bg-slate-600 group-hover:bg-amber-400' }}"></div>
                                <span class="whitespace-nowrap">Principal</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('customer-eska-dist.index') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-white/5 hover:translate-x-1 text-sm {{ request()->routeIs('customer-eska-dist.index') ? 'text-amber-300' : 'text-slate-400 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('customer-eska-dist.index') ? 'scale-150 bg-amber-400' : 'bg-slate-600 group-hover:bg-amber-400' }}"></div>
                                <span class="whitespace-nowrap">Distributor</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('customer-eska-map.index') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-white/5 hover:translate-x-1 text-sm {{ request()->routeIs('customer-eska-map.index') ? 'text-amber-300' : 'text-slate-400 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('customer-eska-map.index') ? 'scale-150 bg-amber-400' : 'bg-slate-600 group-hover:bg-amber-400' }}"></div>
                                <span class="whitespace-nowrap">Mapping</span>
                            </a>
                        </li>

                        <!-- Unmapping (Level 3 Grouping - Emerald) -->
                        <li x-data="{ openUnmap: @json($custUnmapActive) }">
                            <button @click="openUnmap = !openUnmap" 
                                    class="w-full group flex items-center justify-between px-4 py-2 rounded-xl transition-all duration-300 hover:bg-white/5 hover:translate-x-1 text-sm {{ $custUnmapActive ? 'bg-emerald-500/10 text-emerald-400 font-medium' : 'text-emerald-500/80 hover:text-emerald-300 hover:bg-white/5' }}">
                                <div class="flex items-center">
                                    <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ $custUnmapActive ? 'scale-150 bg-emerald-400 shadow-[0_0_6px_rgba(52,211,153,0.8)]' : 'bg-emerald-700/60 group-hover:bg-emerald-400' }}"></div>
                                    <span class="whitespace-nowrap">Unmapping</span>
                                </div>
                                <svg :class="openUnmap ? 'rotate-180 text-emerald-400' : 'text-emerald-600'" class="w-3 h-3 transition-transform duration-300 ease-in-out group-hover:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            
                            <ul x-show="openUnmap" 
                                x-transition:enter="transition-all ease-out duration-300"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition-all ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                                class="mt-1 space-y-1 ml-5 relative before:absolute before:inset-y-0 before:left-1 before:w-[1px] before:bg-emerald-800/30" x-cloak>
                                
                                <li>
                                    <a href="{{ route('customer.csv.export') }}" class="group flex items-center px-4 py-1.5 rounded-xl transition-all duration-300 hover:bg-white/5 hover:translate-x-1 text-xs {{ request()->routeIs('customer.csv.export') ? 'text-emerald-300' : 'text-slate-400 hover:text-emerald-100' }}">
                                        <span class="whitespace-nowrap">Principal</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('customer-eska-unmap.index') }}" class="group flex items-center px-4 py-1.5 rounded-xl transition-all duration-300 hover:bg-white/5 hover:translate-x-1 text-xs {{ request()->routeIs('customer-eska-unmap.index') ? 'text-emerald-300' : 'text-slate-400 hover:text-emerald-100' }}">
                                        <span class="whitespace-nowrap">Distributor</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <!-- Sub-Menu 2: Produk Eskalink (Grouping - Amber) -->
                <li x-data="{ openProdEska: @json($produkEskalinkActive) }">
                    <button @click="openProdEska = !openProdEska" 
                            class="w-full group flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ $produkEskalinkActive ? 'bg-amber-500/10 text-amber-400 font-medium' : 'text-amber-500/80 hover:text-amber-300 hover:bg-white/5' }}">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ $produkEskalinkActive ? 'scale-150 bg-amber-400 shadow-[0_0_6px_rgba(251,191,36,0.8)]' : 'bg-amber-700/60 group-hover:bg-amber-400' }}"></div>
                            <span class="whitespace-nowrap">Produk</span>
                        </div>
                        <svg :class="openProdEska ? 'rotate-180 text-amber-400' : 'text-amber-600'" class="w-3.5 h-3.5 transition-transform duration-300 ease-in-out group-hover:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <ul x-show="openProdEska" 
                        x-transition:enter="transition-all ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition-all ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                        class="mt-1 space-y-1 ml-5 relative before:absolute before:inset-y-0 before:left-1 before:w-[1px] before:bg-amber-800/30" x-cloak>
                        
                        <li>
                            <a href="{{ route('produk-eska.index') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-white/5 hover:translate-x-1 text-sm {{ request()->routeIs('produk-eska.index') ? 'text-amber-300' : 'text-slate-400 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('produk-eska.index') ? 'scale-150 bg-amber-400' : 'bg-slate-600 group-hover:bg-amber-400' }}"></div>
                                <span class="whitespace-nowrap">Master Produk</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('produk-eska-map.index') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-white/5 hover:translate-x-1 text-sm {{ request()->routeIs('produk-eska-map.index') ? 'text-amber-300' : 'text-slate-400 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('produk-eska-map.index') ? 'scale-150 bg-amber-400' : 'bg-slate-600 group-hover:bg-amber-400' }}"></div>
                                <span class="whitespace-nowrap">Mapping Produk</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Sub-Menu 3: Selling Out Eska (Normal - Cyan Tint) -->
                <li>
                    <a href="{{ route('dashboard.sales-comparison') }}" class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ $sellingOutEskaActive ? 'bg-cyan-500/10 text-cyan-300 font-medium' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ $sellingOutEskaActive ? 'scale-150 bg-cyan-400 shadow-[0_0_6px_rgba(34,211,238,0.8)]' : 'bg-slate-600 group-hover:bg-cyan-400' }}"></div>
                            <span class="whitespace-nowrap">Comparasi SO Eska</span>
                        </div>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Call Plan -->
        @php
            $callPlanTeamEliteActive = request()->routeIs(['call-plan.index', 'plan-call-team-elite.import', 'plan-call-team-elite.toko-pareto', 'geotagging.reverse']);
        @endphp
        <div x-data="{ open: @json($callPlanTeamEliteActive) }" class="mt-2">
            <button @click="open = !open"
                    class="group w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-300 ease-out {{ $callPlanTeamEliteActive ? 'bg-gradient-to-r from-cyan-500/10 to-transparent border-l-[3px] border-cyan-400 text-white shadow-md' : 'border-l-[3px] border-transparent text-slate-300 hover:bg-white/5 hover:text-white' }}">
                <div class="flex items-center overflow-hidden">
                    <svg class="w-5 h-5 flex-shrink-0 transition-all duration-300 ease-out group-hover:scale-110 {{ $callPlanTeamEliteActive ? 'text-cyan-400 drop-shadow-[0_0_8px_rgba(34,211,238,0.5)]' : 'text-slate-400 group-hover:text-cyan-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <!-- Map / Route Icon -->
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-medium whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1" x-cloak>Call Plan</span>
                </div>
                <svg x-show="sidebarOpen" :class="open ? 'rotate-180 text-cyan-400' : 'text-slate-500'" class="w-4 h-4 transition-transform duration-300 ease-in-out group-hover:text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <ul x-show="open && sidebarOpen" 
                x-transition:enter="transition-all ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition-all ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
                class="mt-1.5 space-y-1 ml-4 relative before:absolute before:inset-y-0 before:left-[9px] before:w-[1px] before:bg-slate-700/50" x-cloak>
                <li>
                    <a href="{{ route('call-plan.index') }}" class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ request()->routeIs('call-plan.index') ? 'bg-cyan-500/10 text-cyan-300 font-medium' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                        <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ request()->routeIs('call-plan.index') ? 'scale-150 bg-cyan-400 shadow-[0_0_6px_rgba(34,211,238,0.8)]' : 'bg-slate-600 group-hover:bg-cyan-400' }}"></div>
                        <span class="whitespace-nowrap">JKS</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('plan-call-team-elite.import') }}" class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ request()->routeIs('plan-call-team-elite.import') ? 'bg-cyan-500/10 text-cyan-300 font-medium' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                        <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ request()->routeIs('plan-call-team-elite.import') ? 'scale-150 bg-cyan-400 shadow-[0_0_6px_rgba(34,211,238,0.8)]' : 'bg-slate-600 group-hover:bg-cyan-400' }}"></div>
                        <span class="whitespace-nowrap">Import</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('plan-call-team-elite.toko-pareto') }}" class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ request()->routeIs('plan-call-team-elite.toko-pareto') ? 'bg-cyan-500/10 text-cyan-300 font-medium' : 'text-slate-400 hover:text-slate-100 hover:bg-white/5' }}">
                        <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ request()->routeIs('plan-call-team-elite.toko-pareto') ? 'scale-150 bg-cyan-400 shadow-[0_0_6px_rgba(34,211,238,0.8)]' : 'bg-slate-600 group-hover:bg-cyan-400' }}"></div>
                        <span class="whitespace-nowrap">List Toko Pareto</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Menu: Jobs -->
        <div x-data="{ openJobs: false }" class="mt-2">
            <button @click="openJobs = !openJobs"
                class="group w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-300 ease-out border-l-[3px] border-transparent text-slate-300 hover:bg-white/5 hover:text-white">
            <div class="flex items-center overflow-hidden">
                <svg class="w-5 h-5 flex-shrink-0 transition-all duration-300 ease-out group-hover:scale-110 text-slate-400 group-hover:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <!-- Briefcase / Job Icon -->
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-medium whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1" x-cloak>Jobs</span>
            </div>
            <svg x-show="sidebarOpen" :class="openJobs ? 'rotate-180 text-cyan-400' : 'text-slate-500'" class="w-4 h-4 transition-transform duration-300 ease-in-out group-hover:text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            </button>
            
            <ul x-show="openJobs && sidebarOpen" 
            x-transition:enter="transition-all ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition-all ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
            class="mt-1.5 space-y-1 ml-4 relative before:absolute before:inset-y-0 before:left-[9px] before:w-[1px] before:bg-slate-700/50" x-cloak>

            <li>
                <a href="https://sfa.asiatop.co.id/#/" target="_blank" class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm text-slate-400 hover:text-slate-100 hover:bg-white/5">
                <div class="w-1.5 h-1.5 rounded-full bg-slate-600 mr-3 transition-all duration-300 group-hover:scale-150 group-hover:bg-cyan-400"></div>
                <span class="whitespace-nowrap">Eskalink</span>
                </a>
            </li>
            
            <li>
                <a href="https://jobs.asiatop.co.id:3013/#Schedule" target="_blank" class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm text-slate-400 hover:text-slate-100 hover:bg-white/5">
                <div class="w-1.5 h-1.5 rounded-full bg-slate-600 mr-3 transition-all duration-300 group-hover:scale-150 group-hover:bg-cyan-400"></div>
                <span class="whitespace-nowrap">3013</span>
                </a>
            </li>
            <li>
                <a href="https://sfa.asiatop.co.id:3012/#Login" target="_blank" class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm text-slate-400 hover:text-slate-100 hover:bg-white/5">
                <div class="w-1.5 h-1.5 rounded-full bg-slate-600 mr-3 transition-all duration-300 group-hover:scale-150 group-hover:bg-cyan-400"></div>
                <span class="whitespace-nowrap">3012</span>
                </a>
            </li>
            <li>
                <a href="http://192.168.1.92:3012/#Schedule" target="_blank" class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm text-slate-400 hover:text-slate-100 hover:bg-white/5">
                <div class="w-1.5 h-1.5 rounded-full bg-slate-600 mr-3 transition-all duration-300 group-hover:scale-150 group-hover:bg-cyan-400"></div>
                <span class="whitespace-nowrap">SISO</span>
                </a>
            </li>
            </ul>
        </div>
        
    </nav>

    <!-- Toggle Button -->
    <div class="p-4 bg-[#081c3a] border-t border-white/5 z-10 relative">
        <button 
            @click="isPinned = !isPinned; sidebarOpen = isPinned"
            class="group w-full flex items-center justify-center px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 hover:shadow-lg transition-all duration-300 ease-in-out text-slate-400 hover:text-cyan-400 border border-white/5"
            title="Pin / Unpin Sidebar"
        >
            <svg x-show="isPinned" class="w-5 h-5 transition-transform duration-300 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
            <svg x-show="!isPinned" class="w-5 h-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
</aside>