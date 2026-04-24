<style>
    /* Custom Scrollbar Tipis & Elegan untuk Sidebar */
    .sidebar-scroll::-webkit-scrollbar {
        width: 5px;
    }
    .sidebar-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .sidebar-scroll::-webkit-scrollbar-thumb {
        background: oklch(var(--p) / 0.3);
        border-radius: 10px;
        transition: background 0.3s ease;
    }
    .sidebar-scroll:hover::-webkit-scrollbar-thumb {
        background: oklch(var(--p) / 0.6);
    }
</style>

<aside 
    x-data="{ 
        isPinned: localStorage.getItem('sidebarPinned') === 'true'
    }"
    x-init="sidebarOpen = isPinned"
    @mouseenter="sidebarOpen = true"
    @mouseleave="if(!isPinned) sidebarOpen = false"
    :class="sidebarOpen ? 'w-64' : 'w-20'" 
    class="bg-base-300 text-base-content transition-[width] duration-500 ease-in-out flex-shrink-0 flex flex-col shadow-2xl relative z-40 border-r border-base-content/5 h-full">
    
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 px-4 border-b border-base-content/10 relative overflow-hidden bg-gradient-to-b from-base-content/5 to-transparent">
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
    <nav class="flex-1 overflow-y-auto overflow-x-hidden sidebar-scroll py-6 px-3 space-y-1.5">
        
        <!-- Dashboard -->
        <div x-data="{ open: @json(request()->routeIs(['dashboard', 'dashboard.distributor-map'])) }">
            <button @click="open = !open"
                    class="group w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-300 ease-out {{ request()->routeIs(['dashboard', 'dashboard.distributor-map']) ? 'bg-gradient-to-r from-primary/10 to-transparent border-l-[3px] border-primary text-base-content shadow-md' : 'border-l-[3px] border-transparent text-base-content/70 hover:bg-base-content/5 hover:text-base-content' }}">
                <div class="flex items-center overflow-hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 flex-shrink-0 transition-all duration-300 ease-out group-hover:scale-110 {{ request()->routeIs(['dashboard', 'dashboard.distributor-map']) ? 'text-primary' : 'text-base-content/50 group-hover:text-primary' }}"><path d="M3 3h7v7H3V3zm0 11h7v7H3v-7zm11-11h7v7h-7V3zm0 11h7v7h-7v-7z"/></svg>
                    <span x-show="sidebarOpen" 
                          x-transition.opacity.duration.300ms
                          class="ml-3 font-medium whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1" x-cloak>
                          Dashboard
                    </span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" x-show="sidebarOpen" :class="open ? 'rotate-180 text-primary' : 'text-base-content/40'" class="w-4 h-4 flex-shrink-0 transition-transform duration-300 ease-in-out group-hover:text-primary/80" x-cloak><path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" /></svg>
            </button>

            <!-- Submenu Dashboard -->
            <ul x-show="open && sidebarOpen" 
                x-transition:enter="transition-all ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition-all ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
                class="mt-1.5 space-y-1 ml-4 relative before:absolute before:inset-y-0 before:left-[9px] before:w-[1px] before:bg-base-content/10" x-cloak>
                <li>
                    <a href="{{ route('dashboard.analytics') }}"
                       class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ request()->routeIs('dashboard.analytics') ? 'bg-primary/10 text-primary font-medium' : 'text-base-content/60 hover:text-base-content hover:bg-base-content/5' }}">
                        <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ request()->routeIs('dashboard.analytics') ? 'scale-150 bg-primary' : 'bg-base-content/20 group-hover:bg-primary' }}"></div>
                        <span class="whitespace-nowrap transition-transform duration-300">Selling In Summary</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.metabase') }}" 
                       class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ request()->routeIs('dashboard.metabase') ? 'bg-primary/10 text-primary font-medium' : 'text-base-content/60 hover:text-base-content hover:bg-base-content/5' }}">
                        <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ request()->routeIs('dashboard.metabase') ? 'scale-150 bg-primary' : 'bg-base-content/20 group-hover:bg-primary' }}"></div>
                        <span class="whitespace-nowrap transition-transform duration-300">Titik Distribusi</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Selling In -->
        <a href="{{ route('selling-in.report') }}"
           class="group w-full flex items-center px-4 py-3 rounded-xl transition-all duration-300 ease-out {{ request()->routeIs('selling-in.report') ? 'bg-gradient-to-r from-primary/10 to-transparent border-l-[3px] border-primary text-base-content shadow-md' : 'border-l-[3px] border-transparent text-base-content/70 hover:bg-base-content/5 hover:text-base-content' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 flex-shrink-0 transition-all duration-300 ease-out group-hover:scale-110 {{ request()->routeIs('selling-in.report') ? 'text-primary' : 'text-base-content/50 group-hover:text-primary' }}"><path d="M3.375 3C2.339 3 1.5 3.84 1.5 4.875v.75c0 1.036.84 1.875 1.875 1.875h17.25c1.035 0 1.875-.84 1.875-1.875v-.75C22.5 3.839 21.66 3 20.625 3H3.375Z" /><path fill-rule="evenodd" d="m3.087 9 .54 9.176A3 3 0 0 0 6.62 21h10.757a3 3 0 0 0 2.995-2.824L20.913 9H3.087Zm6.163 3.75A.75.75 0 0 1 10 12h4a.75.75 0 0 1 0 1.5h-4a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" /></svg>
            <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-medium whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1" x-cloak>Selling In</span>
        </a>

        <!-- Sales Invoice (Selling Out) -->
        @php
            $salesInvoiceActive = request()->routeIs(['sales-configs.*', 'sales-invoices.*', 'sales-invoice-report.index', 'sell-out.process', 'sell-out.process-v2']);
        @endphp
        <div x-data="{ open: @json($salesInvoiceActive) }">
            <button @click="open = !open"
                    class="group w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-300 ease-out {{ $salesInvoiceActive ? 'bg-gradient-to-r from-primary/10 to-transparent border-l-[3px] border-primary text-base-content shadow-md' : 'border-l-[3px] border-transparent text-base-content/70 hover:bg-base-content/5 hover:text-base-content' }}">
                <div class="flex items-center overflow-hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 flex-shrink-0 transition-all duration-300 ease-out group-hover:scale-110 {{ $salesInvoiceActive ? 'text-primary' : 'text-base-content/50 group-hover:text-primary' }}"><path d="M2.25 2.25a.75.75 0 0 0 0 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 0 0-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 0 0 0-1.5H5.378A2.25 2.25 0 0 1 7.5 15h11.218a.75.75 0 0 0 .674-.421 60.358 60.358 0 0 0 2.96-7.228.75.75 0 0 0-.525-.965A60.864 60.864 0 0 0 5.68 4.509l-.232-.867A1.875 1.875 0 0 0 3.636 2.25H2.25ZM3.75 20.25a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0ZM16.5 20.25a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z" /></svg>
                    <span x-show="sidebarOpen" 
                        x-transition.opacity.duration.300ms
                        class="ml-3 font-medium whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1" x-cloak>
                        Selling Out
                    </span>
                </div>
                <svg x-show="sidebarOpen" :class="open ? 'rotate-180 text-primary' : 'text-base-content/40'"
                    class="w-4 h-4 transition-transform duration-300 ease-in-out group-hover:text-primary/80" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
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
                class="mt-1.5 space-y-1 ml-4 relative before:absolute before:inset-y-0 before:left-[9px] before:w-[1px] before:bg-base-content/10" x-cloak>
                
                <!-- Menu: Import -->
                <li>
                    <a href="{{ route('sales-invoice-report.index') }}" 
                    class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ request()->routeIs('sales-invoice-report.index') ? 'bg-primary/10 text-primary font-medium' : 'text-base-content/60 hover:text-base-content hover:bg-base-content/5' }}">
                        <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ request()->routeIs('sales-invoice-report.index') ? 'scale-150 bg-primary' : 'bg-base-content/20 group-hover:bg-primary' }}"></div>
                        <span class="whitespace-nowrap">Import</span>
                    </a>
                </li>

                <!-- Menu: Generate -->
                @php
                    $generateActive = request()->routeIs(['sell-out.process', 'sell-out.process-v2']);
                @endphp
                <li x-data="{ openGenerate: @json($generateActive) }">
                    <button @click="openGenerate = !openGenerate" 
                            class="w-full group flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ $generateActive ? 'bg-secondary/10 text-secondary font-medium' : 'text-secondary/70 hover:text-secondary hover:bg-base-content/5' }}">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ $generateActive ? 'scale-150 bg-secondary' : 'bg-secondary/30 group-hover:bg-secondary' }}"></div>
                            <span class="whitespace-nowrap">Generate</span>
                        </div>
                        <svg :class="openGenerate ? 'rotate-180 text-secondary' : 'text-secondary/60'"
                            class="w-3.5 h-3.5 transition-transform duration-300 ease-in-out group-hover:text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        class="mt-1 space-y-1 ml-5 relative before:absolute before:inset-y-0 before:left-1 before:w-[1px] before:bg-secondary/20" x-cloak>
                        
                        <li>
                            <a href="{{ route('sell-out.process') }}" 
                            class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ request()->routeIs('sell-out.process') ? 'text-secondary font-medium' : 'text-base-content/60 hover:text-secondary/80 hover:bg-base-content/5' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('sell-out.process') ? 'scale-150 bg-secondary' : 'bg-base-content/20 group-hover:bg-secondary' }}"></div>
                                <span class="whitespace-nowrap">Eska Version</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('sell-out.process-v2') }}" 
                            class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ request()->routeIs('sell-out.process-v2') ? 'text-secondary font-medium' : 'text-base-content/60 hover:text-secondary/80 hover:bg-base-content/5' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('sell-out.process-v2') ? 'scale-150 bg-secondary' : 'bg-base-content/20 group-hover:bg-secondary' }}"></div>
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
                    class="group w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-300 ease-out {{ $settingsActive ? 'bg-gradient-to-r from-primary/10 to-transparent border-l-[3px] border-primary text-base-content shadow-md' : 'border-l-[3px] border-transparent text-base-content/70 hover:bg-base-content/5 hover:text-base-content' }}">
                <div class="flex items-center overflow-hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 flex-shrink-0 transition-all duration-300 ease-out group-hover:scale-110 {{ $settingsActive ? 'text-primary' : 'text-base-content/50 group-hover:text-primary' }}"><path d="M21 6.375c0 2.692-4.03 4.875-9 4.875S3 9.067 3 6.375 7.03 1.5 12 1.5s9 2.183 9 4.875Z" /><path d="M12 12.75c2.685 0 5.19-.586 7.078-1.609a8.283 8.283 0 0 0 1.897-1.384c.016.121.025.244.025.368C21 12.817 16.97 15 12 15s-9-2.183-9-4.875c0-.124.009-.247.025-.368a8.285 8.285 0 0 0 1.897 1.384C6.809 12.164 9.315 12.75 12 12.75Z" /><path d="M12 16.5c2.685 0 5.19-.586 7.078-1.609a8.282 8.282 0 0 0 1.897-1.384c.016.121.025.244.025.368 0 2.692-4.03 4.875-9 4.875s-9-2.183-9-4.875c0-.124.009-.247.025-.368a8.284 8.284 0 0 0 1.897 1.384C6.809 15.914 9.315 16.5 12 16.5Z" /><path d="M12 20.25c2.685 0 5.19-.586 7.078-1.609a8.282 8.282 0 0 0 1.897-1.384c.016.121.025.244.025.368 0 2.692-4.03 4.875-9 4.875s-9-2.183-9-4.875c0-.124.009-.247.025-.368a8.284 8.284 0 0 0 1.897 1.384C6.809 19.914 9.315 20.25 12 20.25Z" /></svg>
                    <span x-show="sidebarOpen" 
                        x-transition.opacity.duration.300ms
                        class="ml-3 font-medium whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1" x-cloak>
                        Master Data
                    </span>
                </div>
                <svg x-show="sidebarOpen" :class="openSettings ? 'rotate-180 text-primary' : 'text-base-content/40'"
                    class="w-4 h-4 transition-transform duration-300 ease-in-out group-hover:text-primary/80" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
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
                class="mt-1.5 space-y-1 ml-4 relative before:absolute before:inset-y-0 before:left-[9px] before:w-[1px] before:bg-base-content/10" x-cloak>
                
                <!-- 1. Master Data Sub-Menu (Grouping - Amber) -->
                <li x-data="{ openMaster: @json($masterDataActive) }">
                    <button @click="openMaster = !openMaster" 
                            class="w-full group flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ $masterDataActive ? 'bg-secondary/10 text-secondary font-medium' : 'text-secondary/70 hover:text-secondary hover:bg-base-content/5' }}">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ $masterDataActive ? 'scale-150 bg-secondary' : 'bg-secondary/30 group-hover:bg-secondary' }}"></div>
                            <span class="whitespace-nowrap">Master</span>
                        </div>
                        <svg :class="openMaster ? 'rotate-180 text-secondary' : 'text-secondary/60'"
                            class="w-3.5 h-3.5 transition-transform duration-300 ease-in-out group-hover:text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        class="mt-1 space-y-1 ml-5 relative before:absolute before:inset-y-0 before:left-1 before:w-[1px] before:bg-secondary/20" x-cloak>
                        
                        <!-- Distribusi (Level 3 Grouping - Emerald) -->
                        <li x-data="{ openDist: @json($masterAreaActive) }">
                            <button @click="openDist = !openDist" class="group w-full flex items-center justify-between px-4 py-2 rounded-xl transition-all duration-300 hover:bg-base-content/5 text-sm {{ $masterAreaActive ? 'bg-accent/10 text-accent font-medium' : 'text-accent/70 hover:text-accent hover:bg-base-content/5' }}">
                                <div class="flex items-center">
                                    <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ $masterAreaActive ? 'scale-150 bg-accent' : 'bg-accent/30 group-hover:bg-accent' }}"></div>
                                    <span class="whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1">Distribusi</span>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" :class="openDist ? 'rotate-180 text-accent' : 'text-accent/60'" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-300"><path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" /></svg>
                            </button>
                            <ul x-show="openDist" class="mt-1 space-y-1 ml-6 relative before:absolute before:inset-y-0 before:-left-1 before:w-[1px] before:bg-accent/20" x-cloak>
                                <li><a href="{{ route('master-regions.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-base-content/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('master-regions.index') ? 'text-accent/90' : 'text-base-content/60 hover:text-accent/80' }}"><span class="whitespace-nowrap">Region</span></a></li>
                                <li><a href="{{ route('master-areas.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-base-content/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('master-areas.index') ? 'text-accent/90' : 'text-base-content/60 hover:text-accent/80' }}"><span class="whitespace-nowrap">Area</span></a></li>
                                <li><a href="{{ route('master-supervisors.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-base-content/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('master-supervisors.index') ? 'text-accent/90' : 'text-base-content/60 hover:text-accent/80' }}"><span class="whitespace-nowrap">Supervisor</span></a></li>
                                <li><a href="{{ route('master-branches.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-base-content/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('master-branches.index') ? 'text-accent/90' : 'text-base-content/60 hover:text-accent/80' }}"><span class="whitespace-nowrap">Cabang</span></a></li>
                                <li><a href="{{ route('master-distributors.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-base-content/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('master-distributors.index') ? 'text-accent/90' : 'text-base-content/60 hover:text-accent/80' }}"><span class="whitespace-nowrap">Distributor</span></a></li>
                            </ul>
                        </li>
                        
                        <!-- Product (Level 3 Grouping - Emerald) -->
                        <li x-data="{ openProd: @json($masterProductActive) }">
                            <button @click="openProd = !openProd" class="group w-full flex items-center justify-between px-4 py-2 rounded-xl transition-all duration-300 hover:bg-base-content/5 text-sm {{ $masterProductActive ? 'bg-accent/10 text-accent font-medium' : 'text-accent/70 hover:text-accent hover:bg-base-content/5' }}">
                                <div class="flex items-center">
                                    <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ $masterProductActive ? 'scale-150 bg-accent' : 'bg-accent/30 group-hover:bg-accent' }}"></div>
                                    <span class="whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1">Product</span>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" :class="openProd ? 'rotate-180 text-accent' : 'text-accent/60'" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-300"><path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" /></svg>
                            </button>
                            <ul x-show="openProd" class="mt-1 space-y-1 ml-6 relative before:absolute before:inset-y-0 before:-left-1 before:w-[1px] before:bg-accent/20" x-cloak>
                                <li><a href="{{ route('product-lines.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-base-content/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('product-lines.index') ? 'text-accent/90' : 'text-base-content/60 hover:text-accent/80' }}"><span class="whitespace-nowrap">Line</span></a></li>
                                <li><a href="{{ route('product-brands.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-base-content/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('product-brands.index') ? 'text-accent/90' : 'text-base-content/60 hover:text-accent/80' }}"><span class="whitespace-nowrap">Brand</span></a></li>
                                <li><a href="{{ route('product-groups.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-base-content/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('product-groups.index') ? 'text-accent/90' : 'text-base-content/60 hover:text-accent/80' }}"><span class="whitespace-nowrap">Grup</span></a></li>
                                <li><a href="{{ route('product-sub-brands.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-base-content/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('product-sub-brands.index') ? 'text-accent/90' : 'text-base-content/60 hover:text-accent/80' }}"><span class="whitespace-nowrap">Sub Brand</span></a></li>
                                <li><a href="{{ route('product-masters.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-base-content/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('product-masters.index') ? 'text-accent/90' : 'text-base-content/60 hover:text-accent/80' }}"><span class="whitespace-nowrap">Product Master</span></a></li>
                                <li><a href="{{ route('categories.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-base-content/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('categories.index') ? 'text-accent/90' : 'text-base-content/60 hover:text-accent/80' }}"><span class="whitespace-nowrap">Kategory</span></a></li>
                                <li><a href="{{ route('product-categories.index') }}" class="group block px-4 py-1.5 rounded-lg hover:bg-base-content/5 hover:translate-x-1 transition-all duration-200 text-xs {{ request()->routeIs('product-categories.index') ? 'text-accent/90' : 'text-base-content/60 hover:text-accent/80' }}"><span class="whitespace-nowrap">Product Kategori</span></a></li>
                            </ul>
                        </li>

                        <!-- Salesmen (Level 3 Normal - Amber Tint) -->
                        <li>
                            <a href="{{ route('salesmans.index') }}" 
                            class="group w-full flex items-center justify-between px-4 py-2 rounded-xl hover:bg-base-content/5 hover:translate-x-1 transition-all duration-300 text-sm {{ request()->routeIs('salesmans.index') ? 'text-secondary/90' : 'text-base-content/60 hover:text-amber-100' }}">
                                <div class="flex items-center">
                                    <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('salesmans.index') ? 'scale-150 bg-secondary' : 'bg-base-content/20 group-hover:bg-secondary' }}"></div>
                                    <span class="whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1">Salesmen</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- 2. Mapping Data Sub-Menu (Grouping - Amber) -->
                <li x-data="{ openMapping: @json($mappingDataActive) }">
                    <button @click="openMapping = !openMapping" 
                            class="w-full group flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ $mappingDataActive ? 'bg-secondary/10 text-secondary font-medium' : 'text-secondary/70 hover:text-secondary hover:bg-base-content/5' }}">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ $mappingDataActive ? 'scale-150 bg-secondary' : 'bg-secondary/30 group-hover:bg-secondary' }}"></div>
                            <span class="whitespace-nowrap">Mapping</span>
                        </div>
                        <svg :class="openMapping ? 'rotate-180 text-secondary' : 'text-secondary/60'"
                            class="w-3.5 h-3.5 transition-transform duration-300 ease-in-out group-hover:text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        class="mt-1 space-y-1 ml-5 relative before:absolute before:inset-y-0 before:left-1 before:w-[1px] before:bg-secondary/20" x-cloak>
                        
                        <li>
                            <a href="{{ route('product-mappings.index') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-base-content/5 hover:translate-x-1 text-sm {{ request()->routeIs('product-mappings.index') ? 'text-secondary/90' : 'text-base-content/60 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('product-mappings.index') ? 'scale-150 bg-secondary' : 'bg-base-content/20 group-hover:bg-secondary' }}"></div>
                                <span class="whitespace-nowrap">Product</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('salesman-mappings.index') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-base-content/5 hover:translate-x-1 text-sm {{ request()->routeIs('salesman-mappings.index') ? 'text-secondary/90' : 'text-base-content/60 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('salesman-mappings.index') ? 'scale-150 bg-secondary' : 'bg-base-content/20 group-hover:bg-secondary' }}"></div>
                                <span class="whitespace-nowrap">Salesmen</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- 3. Unmapping Data Sub-Menu (Grouping - Amber) -->
                <li x-data="{ openUnmapping: @json($unmappingDataActive) }">
                    <button @click="openUnmapping = !openUnmapping" 
                            class="w-full group flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ $unmappingDataActive ? 'bg-secondary/10 text-secondary font-medium' : 'text-secondary/70 hover:text-secondary hover:bg-base-content/5' }}">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ $unmappingDataActive ? 'scale-150 bg-secondary' : 'bg-secondary/30 group-hover:bg-secondary' }}"></div>
                            <span class="whitespace-nowrap">Unmapping</span>
                        </div>
                        <svg :class="openUnmapping ? 'rotate-180 text-secondary' : 'text-secondary/60'"
                            class="w-3.5 h-3.5 transition-transform duration-300 ease-in-out group-hover:text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        class="mt-1 space-y-1 ml-5 relative before:absolute before:inset-y-0 before:left-1 before:w-[1px] before:bg-secondary/20" x-cloak>
                        
                        <li>
                            <a href="{{ route('mapping.unmapped-products') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-base-content/5 hover:translate-x-1 text-sm {{ request()->routeIs('mapping.unmapped-products') ? 'text-secondary/90' : 'text-base-content/60 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('mapping.unmapped-products') ? 'scale-150 bg-secondary' : 'bg-base-content/20 group-hover:bg-secondary' }}"></div>
                                <span class="whitespace-nowrap">Product</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('mapping.unmapped-salesmans') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-base-content/5 hover:translate-x-1 text-sm {{ request()->routeIs('mapping.unmapped-salesmans') ? 'text-secondary/90' : 'text-base-content/60 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('mapping.unmapped-salesmans') ? 'scale-150 bg-secondary' : 'bg-base-content/20 group-hover:bg-secondary' }}"></div>
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
                    class="group w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-300 ease-out {{ $eskalinkActive ? 'bg-gradient-to-r from-primary/10 to-transparent border-l-[3px] border-primary text-base-content shadow-md' : 'border-l-[3px] border-transparent text-base-content/70 hover:bg-base-content/5 hover:text-base-content' }}">
                <div class="flex items-center overflow-hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 flex-shrink-0 transition-all duration-300 ease-out group-hover:scale-110 {{ $eskalinkActive ? 'text-primary' : 'text-base-content/50 group-hover:text-primary' }}"><path d="M21.721 12.752a9.711 9.711 0 0 0-.945-5.003 12.754 12.754 0 0 1-4.339 2.708 18.991 18.991 0 0 1-.214 4.772 17.165 17.165 0 0 0 5.498-2.477ZM14.634 15.55a17.324 17.324 0 0 0 .332-4.647c-.952.227-1.945.347-2.966.347-1.021 0-2.014-.12-2.966-.347a17.515 17.515 0 0 0 .332 4.647 17.385 17.385 0 0 0 5.268 0ZM9.772 17.119a18.963 18.963 0 0 0 4.456 0A17.182 17.182 0 0 1 12 21.724a17.18 17.18 0 0 1-2.228-4.605ZM7.777 15.23a18.87 18.87 0 0 1-.214-4.774 12.753 12.753 0 0 1-4.34-2.708 9.711 9.711 0 0 0-.944 5.004 17.165 17.165 0 0 0 5.498 2.477ZM21.356 14.752a9.765 9.765 0 0 1-7.478 6.817 18.64 18.64 0 0 0 1.988-4.718 18.627 18.627 0 0 0 5.49-2.098ZM2.644 14.752c1.682.971 3.53 1.688 5.49 2.099a18.64 18.64 0 0 0 1.988 4.718 9.765 9.765 0 0 1-7.478-6.816ZM13.878 2.43a9.755 9.755 0 0 1 6.116 3.986 11.267 11.267 0 0 1-3.746 2.504 18.63 18.63 0 0 0-2.37-6.49ZM12 2.276a17.152 17.152 0 0 1 2.805 7.121c-.897.23-1.837.353-2.805.353-.968 0-1.908-.122-2.805-.353A17.151 17.151 0 0 1 12 2.276ZM10.122 2.43a18.629 18.629 0 0 0-2.37 6.49 11.266 11.266 0 0 1-3.746-2.504 9.754 9.754 0 0 1 6.116-3.985Z" /></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-medium whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1" x-cloak>Eskalink</span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" x-show="sidebarOpen" :class="openEskalink ? 'rotate-180 text-primary' : 'text-base-content/40'" class="w-4 h-4 flex-shrink-0 transition-transform duration-300 ease-in-out group-hover:text-primary/80" x-cloak><path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" /></svg>
            </button>
            
            <ul x-show="openEskalink && sidebarOpen" 
                x-transition:enter="transition-all ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition-all ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
                class="mt-1.5 space-y-1 ml-4 relative before:absolute before:inset-y-0 before:left-[9px] before:w-[1px] before:bg-base-content/10" x-cloak>
                
                <!-- Sub-Menu 1: Customer Eskalink (Grouping - Amber) -->
                <li x-data="{ openCustEska: @json($customerEskalinkActive) }">
                    <button @click="openCustEska = !openCustEska" 
                            class="w-full group flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ $customerEskalinkActive ? 'bg-secondary/10 text-secondary font-medium' : 'text-secondary/70 hover:text-secondary hover:bg-base-content/5' }}">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ $customerEskalinkActive ? 'scale-150 bg-secondary' : 'bg-secondary/30 group-hover:bg-secondary' }}"></div>
                            <span class="whitespace-nowrap">Customer</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" :class="openCustEska ? 'rotate-180 text-secondary' : 'text-secondary/60'" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-300 ease-in-out group-hover:text-secondary"><path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" /></svg>
                    </button>
                    
                    <ul x-show="openCustEska" 
                        x-transition:enter="transition-all ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition-all ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                        class="mt-1 space-y-1 ml-5 relative before:absolute before:inset-y-0 before:left-1 before:w-[1px] before:bg-secondary/20" x-cloak>
                        
                        <li>
                            <a href="{{ route('customer-eska.index') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-base-content/5 hover:translate-x-1 text-sm {{ request()->routeIs('customer-eska.index') ? 'text-secondary/90' : 'text-base-content/60 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('customer-eska.index') ? 'scale-150 bg-secondary' : 'bg-base-content/20 group-hover:bg-secondary' }}"></div>
                                <span class="whitespace-nowrap">Principal</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('customer-eska-dist.index') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-base-content/5 hover:translate-x-1 text-sm {{ request()->routeIs('customer-eska-dist.index') ? 'text-secondary/90' : 'text-base-content/60 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('customer-eska-dist.index') ? 'scale-150 bg-secondary' : 'bg-base-content/20 group-hover:bg-secondary' }}"></div>
                                <span class="whitespace-nowrap">Distributor</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('customer-eska-map.index') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-base-content/5 hover:translate-x-1 text-sm {{ request()->routeIs('customer-eska-map.index') ? 'text-secondary/90' : 'text-base-content/60 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('customer-eska-map.index') ? 'scale-150 bg-secondary' : 'bg-base-content/20 group-hover:bg-secondary' }}"></div>
                                <span class="whitespace-nowrap">Mapping</span>
                            </a>
                        </li>

                        <!-- Unmapping (Level 3 Grouping - Emerald) -->
                        <li x-data="{ openUnmap: @json($custUnmapActive) }">
                            <button @click="openUnmap = !openUnmap" 
                                    class="w-full group flex items-center justify-between px-4 py-2 rounded-xl transition-all duration-300 hover:bg-base-content/5 hover:translate-x-1 text-sm {{ $custUnmapActive ? 'bg-accent/10 text-accent font-medium' : 'text-accent/70 hover:text-accent hover:bg-base-content/5' }}">
                                <div class="flex items-center">
                                    <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ $custUnmapActive ? 'scale-150 bg-accent' : 'bg-accent/30 group-hover:bg-accent' }}"></div>
                                    <span class="whitespace-nowrap">Unmapping</span>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" :class="openUnmap ? 'rotate-180 text-accent' : 'text-accent/60'" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-300 ease-in-out group-hover:text-accent"><path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" /></svg>
                            </button>
                            
                            <ul x-show="openUnmap" 
                                x-transition:enter="transition-all ease-out duration-300"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition-all ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                                class="mt-1 space-y-1 ml-5 relative before:absolute before:inset-y-0 before:left-1 before:w-[1px] before:bg-accent/20" x-cloak>
                                
                                <li>
                                    <a href="{{ route('customer.csv.export') }}" class="group flex items-center px-4 py-1.5 rounded-xl transition-all duration-300 hover:bg-base-content/5 hover:translate-x-1 text-xs {{ request()->routeIs('customer.csv.export') ? 'text-accent/90' : 'text-base-content/60 hover:text-accent/80' }}">
                                        <span class="whitespace-nowrap">Principal</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('customer-eska-unmap.index') }}" class="group flex items-center px-4 py-1.5 rounded-xl transition-all duration-300 hover:bg-base-content/5 hover:translate-x-1 text-xs {{ request()->routeIs('customer-eska-unmap.index') ? 'text-accent/90' : 'text-base-content/60 hover:text-accent/80' }}">
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
                            class="w-full group flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ $produkEskalinkActive ? 'bg-secondary/10 text-secondary font-medium' : 'text-secondary/70 hover:text-secondary hover:bg-base-content/5' }}">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ $produkEskalinkActive ? 'scale-150 bg-secondary' : 'bg-secondary/30 group-hover:bg-secondary' }}"></div>
                            <span class="whitespace-nowrap">Produk</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" :class="openProdEska ? 'rotate-180 text-secondary' : 'text-secondary/60'" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-300 ease-in-out group-hover:text-secondary"><path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" /></svg>
                    </button>
                    
                    <ul x-show="openProdEska" 
                        x-transition:enter="transition-all ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition-all ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                        class="mt-1 space-y-1 ml-5 relative before:absolute before:inset-y-0 before:left-1 before:w-[1px] before:bg-secondary/20" x-cloak>
                        
                        <li>
                            <a href="{{ route('produk-eska.index') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-base-content/5 hover:translate-x-1 text-sm {{ request()->routeIs('produk-eska.index') ? 'text-secondary/90' : 'text-base-content/60 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('produk-eska.index') ? 'scale-150 bg-secondary' : 'bg-base-content/20 group-hover:bg-secondary' }}"></div>
                                <span class="whitespace-nowrap">Master Produk</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('produk-eska-map.index') }}" class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-base-content/5 hover:translate-x-1 text-sm {{ request()->routeIs('produk-eska-map.index') ? 'text-secondary/90' : 'text-base-content/60 hover:text-amber-100' }}">
                                <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ request()->routeIs('produk-eska-map.index') ? 'scale-150 bg-secondary' : 'bg-base-content/20 group-hover:bg-secondary' }}"></div>
                                <span class="whitespace-nowrap">Mapping Produk</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Sub-Menu 3: Selling Out Eska (Normal - Cyan Tint) -->
                <li>
                    <a href="{{ route('dashboard.sales-comparison') }}" class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ $sellingOutEskaActive ? 'bg-primary/10 text-primary font-medium' : 'text-base-content/60 hover:text-base-content hover:bg-base-content/5' }}">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ $sellingOutEskaActive ? 'scale-150 bg-primary' : 'bg-base-content/20 group-hover:bg-primary' }}"></div>
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
                    class="group w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-300 ease-out {{ $callPlanTeamEliteActive ? 'bg-gradient-to-r from-primary/10 to-transparent border-l-[3px] border-primary text-base-content shadow-md' : 'border-l-[3px] border-transparent text-base-content/70 hover:bg-base-content/5 hover:text-base-content' }}">
                <div class="flex items-center overflow-hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 flex-shrink-0 transition-all duration-300 ease-out group-hover:scale-110 {{ $callPlanTeamEliteActive ? 'text-primary' : 'text-base-content/50 group-hover:text-primary' }}"><path fill-rule="evenodd" d="M8.161 2.58a1.875 1.875 0 0 1 1.678 0l4.993 2.498c.106.052.23.052.336 0l3.869-1.935A1.875 1.875 0 0 1 21.75 4.82v12.485c0 .71-.401 1.36-1.037 1.677l-4.875 2.437a1.875 1.875 0 0 1-1.676 0l-4.994-2.497a.375.375 0 0 0-.336 0l-3.868 1.935A1.875 1.875 0 0 1 2.25 19.18V6.695c0-.71.401-1.36 1.036-1.677l4.875-2.437ZM9 6a.75.75 0 0 1 .75.75v9.5a.75.75 0 0 1-1.5 0v-9.5A.75.75 0 0 1 9 6Zm6.75 2.75a.75.75 0 0 0-1.5 0v9.5a.75.75 0 0 0 1.5 0v-9.5Z" clip-rule="evenodd" /></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-medium whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1" x-cloak>Call Plan</span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" x-show="sidebarOpen" :class="open ? 'rotate-180 text-primary' : 'text-base-content/40'" class="w-4 h-4 flex-shrink-0 transition-transform duration-300 ease-in-out group-hover:text-primary/80" x-cloak><path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" /></svg>
            </button>
            <ul x-show="open && sidebarOpen" 
                x-transition:enter="transition-all ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition-all ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
                class="mt-1.5 space-y-1 ml-4 relative before:absolute before:inset-y-0 before:left-[9px] before:w-[1px] before:bg-base-content/10" x-cloak>
                <li>
                    <a href="{{ route('call-plan.index') }}" class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ request()->routeIs('call-plan.index') ? 'bg-primary/10 text-primary font-medium' : 'text-base-content/60 hover:text-base-content hover:bg-base-content/5' }}">
                        <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ request()->routeIs('call-plan.index') ? 'scale-150 bg-primary' : 'bg-base-content/20 group-hover:bg-primary' }}"></div>
                        <span class="whitespace-nowrap">JKS</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('plan-call-team-elite.import') }}" class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ request()->routeIs('plan-call-team-elite.import') ? 'bg-primary/10 text-primary font-medium' : 'text-base-content/60 hover:text-base-content hover:bg-base-content/5' }}">
                        <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ request()->routeIs('plan-call-team-elite.import') ? 'scale-150 bg-primary' : 'bg-base-content/20 group-hover:bg-primary' }}"></div>
                        <span class="whitespace-nowrap">Import</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('plan-call-team-elite.toko-pareto') }}" class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ request()->routeIs('plan-call-team-elite.toko-pareto') ? 'bg-primary/10 text-primary font-medium' : 'text-base-content/60 hover:text-base-content hover:bg-base-content/5' }}">
                        <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ request()->routeIs('plan-call-team-elite.toko-pareto') ? 'scale-150 bg-primary' : 'bg-base-content/20 group-hover:bg-primary' }}"></div>
                        <span class="whitespace-nowrap">List Toko Pareto</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Menu: Jobs -->
        <div x-data="{ openJobs: false }" class="mt-2">
            <button @click="openJobs = !openJobs"
                class="group w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-300 ease-out border-l-[3px] border-transparent text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
            <div class="flex items-center overflow-hidden">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 flex-shrink-0 transition-all duration-300 ease-out group-hover:scale-110 text-base-content/50 group-hover:text-primary"><path fill-rule="evenodd" d="M7.5 5.25a3 3 0 0 1 3-3h3a3 3 0 0 1 3 3v.205c.933.085 1.857.197 2.774.334 1.454.218 2.476 1.483 2.476 2.917v3.033c0 1.211-.734 2.352-1.936 2.752A24.726 24.726 0 0 1 12 15.75c-2.73 0-5.357-.442-7.814-1.259-1.202-.4-1.936-1.541-1.936-2.752V8.706c0-1.434 1.022-2.7 2.476-2.917A48.814 48.814 0 0 1 7.5 5.455V5.25Zm7.5 0v.09a49.488 49.488 0 0 0-6 0v-.09a1.5 1.5 0 0 1 1.5-1.5h3a1.5 1.5 0 0 1 1.5 1.5Zm-3 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" /><path d="M3 18.4v-2.796a4.3 4.3 0 0 0 .713.31A26.226 26.226 0 0 0 12 17.25c2.892 0 5.68-.468 8.287-1.335.252-.084.49-.189.713-.311V18.4c0 1.452-1.047 2.728-2.523 2.923-2.12.282-4.282.427-6.477.427a49.19 49.19 0 0 1-6.477-.427C4.047 21.128 3 19.852 3 18.4Z" /></svg>
                <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-medium whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1" x-cloak>Jobs</span>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" x-show="sidebarOpen" :class="openJobs ? 'rotate-180 text-primary' : 'text-base-content/40'" class="w-4 h-4 flex-shrink-0 transition-transform duration-300 ease-in-out group-hover:text-primary/80" x-cloak><path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" /></svg>
            </button>
            
            <ul x-show="openJobs && sidebarOpen" 
            x-transition:enter="transition-all ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition-all ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
            class="mt-1.5 space-y-1 ml-4 relative before:absolute before:inset-y-0 before:left-[9px] before:w-[1px] before:bg-base-content/10" x-cloak>

            <li>
                <a href="https://sfa.asiatop.co.id/#/" target="_blank" class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm text-base-content/60 hover:text-base-content hover:bg-base-content/5">
                <div class="w-1.5 h-1.5 rounded-full bg-slate-600 mr-3 transition-all duration-300 group-hover:scale-150 group-hover:bg-cyan-400"></div>
                <span class="whitespace-nowrap">Eskalink</span>
                </a>
            </li>
            
            <li>
                <a href="https://jobs.asiatop.co.id:3013/#Schedule" target="_blank" class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm text-base-content/60 hover:text-base-content hover:bg-base-content/5">
                <div class="w-1.5 h-1.5 rounded-full bg-slate-600 mr-3 transition-all duration-300 group-hover:scale-150 group-hover:bg-cyan-400"></div>
                <span class="whitespace-nowrap">3013</span>
                </a>
            </li>
            <li>
                <a href="https://sfa.asiatop.co.id:3012/#Login" target="_blank" class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm text-base-content/60 hover:text-base-content hover:bg-base-content/5">
                <div class="w-1.5 h-1.5 rounded-full bg-slate-600 mr-3 transition-all duration-300 group-hover:scale-150 group-hover:bg-cyan-400"></div>
                <span class="whitespace-nowrap">3012</span>
                </a>
            </li>
            <li>
                <a href="http://192.168.1.92:3012/#Schedule" target="_blank" class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm text-base-content/60 hover:text-base-content hover:bg-base-content/5">
                <div class="w-1.5 h-1.5 rounded-full bg-slate-600 mr-3 transition-all duration-300 group-hover:scale-150 group-hover:bg-cyan-400"></div>
                <span class="whitespace-nowrap">SISO</span>
                </a>
            </li>
            </ul>
        </div>
        
    </nav>

    <!-- Toggle Button -->
    <div class="p-4 bg-base-300 border-t border-base-content/10 z-10 relative">
        <button 
            @click="isPinned = !isPinned; sidebarOpen = isPinned; localStorage.setItem('sidebarPinned', isPinned)"
            class="btn btn-ghost btn-sm w-full flex items-center justify-center gap-2"
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


