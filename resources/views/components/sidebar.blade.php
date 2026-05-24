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
             src="{{ asset('icon/logo.png') }}"
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
             src="{{ asset('icon/logo-icon.png') }}"
             class="h-8 absolute"
             x-cloak>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto overflow-x-hidden sidebar-scroll py-6 px-3 space-y-1.5">
        
        @php
            if (auth()->check()) {
                $user = auth()->user();
                $accessGroupId = $user->access_group_id;
                
                if ($accessGroupId) {
                    // Get all menu IDs that are in the user's access group
                    $allowedMenuIds = \Illuminate\Support\Facades\DB::table('access_group_menu')
                        ->where('access_group_id', $accessGroupId)
                        ->pluck('menu_id')
                        ->toArray();
                    
                    if (count($allowedMenuIds) > 0) {
                        // Get all ancestors (parent menus) so hierarchy can be built
                        // We need all menus and filter in PHP to show parents that have visible children
                        $allMenus = \App\Models\Menu::orderBy('order_number')
                            ->get()
                            ->keyBy('id');
                        
                        // Add parent IDs recursively
                        $visibleIds = collect($allowedMenuIds);
                        foreach ($allowedMenuIds as $mid) {
                            $menu = $allMenus->get($mid);
                            while ($menu && $menu->parent_id) {
                                $visibleIds->push($menu->parent_id);
                                $menu = $allMenus->get($menu->parent_id);
                            }
                        }
                        $visibleIds = $visibleIds->unique()->toArray();
                        
                        // Build tree with only visible menus
                        $userMenus = \App\Models\Menu::whereNull('parent_id')
                            ->whereIn('id', $visibleIds)
                            ->with(['children' => function($q) use ($visibleIds) {
                                $q->whereIn('id', $visibleIds)
                                  ->orderBy('order_number')
                                  ->with(['children' => function($q2) use ($visibleIds) {
                                      $q2->whereIn('id', $visibleIds)
                                         ->orderBy('order_number')
                                         ->with(['children' => function($q3) use ($visibleIds) {
                                             $q3->whereIn('id', $visibleIds)
                                                ->orderBy('order_number');
                                         }]);
                                  }]);
                            }])
                            ->orderBy('order_number')
                            ->get();
                    } else {
                        $userMenus = collect();
                    }
                } else {
                    // No group = no menus
                    $userMenus = collect();
                }
            } else {
                $userMenus = collect();
            }
        @endphp

        @foreach($userMenus as $menu)
            @if($menu->children->isEmpty())
                <!-- Level 1: No Children -->
                @php
                    $url0 = $menu->route ? (Str::startsWith($menu->route, 'http') ? $menu->route : route($menu->route)) : '#';
                @endphp
                <a href="{{ $url0 }}" {!! Str::startsWith($menu->route ?? '', 'http') ? 'target="_blank"' : '' !!}
                   class="group w-full flex items-center px-4 py-3 rounded-xl transition-all duration-300 ease-out {{ $menu->route && request()->routeIs($menu->route) ? 'bg-gradient-to-r from-primary/10 to-transparent border-l-[3px] border-primary text-base-content shadow-md' : 'border-l-[3px] border-transparent text-base-content/70 hover:bg-base-content/5 hover:text-base-content' }}">
                    @if($menu->icon) {!! $menu->icon !!} @else <div class="w-5 h-5 flex-shrink-0"></div> @endif
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-medium whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1" x-cloak>{{ $menu->name }}</span>
                </a>
            @else
                <!-- Level 1: Has Children -->
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                            class="group w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-300 ease-out border-l-[3px] border-transparent text-base-content/70 hover:bg-base-content/5 hover:text-base-content">
                        <div class="flex items-center overflow-hidden">
                            @if($menu->icon) {!! $menu->icon !!} @else <div class="w-5 h-5 flex-shrink-0"></div> @endif
                            <span x-show="sidebarOpen" class="ml-3 font-medium whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1" x-cloak>{{ $menu->name }}</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" x-show="sidebarOpen" :class="open ? 'rotate-180 text-primary' : 'text-base-content/40'" class="w-4 h-4 flex-shrink-0 transition-transform duration-300 ease-in-out group-hover:text-primary/80" x-cloak><path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" /></svg>
                    </button>
                    <ul x-show="open && sidebarOpen" class="mt-1.5 space-y-1 ml-4 relative before:absolute before:inset-y-0 before:left-[9px] before:w-[1px] before:bg-base-content/10" x-cloak>
                        @foreach($menu->children as $child1)
                            @if($child1->children->isEmpty())
                                <!-- Level 2: No Children -->
                                <li>
                                    @php
                                        $url1 = $child1->route ? (Str::startsWith($child1->route, 'http') ? $child1->route : route($child1->route)) : '#';
                                    @endphp
                                    <a href="{{ $url1 }}" {!! Str::startsWith($child1->route ?? '', 'http') ? 'target="_blank"' : '' !!} class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm {{ $child1->route && request()->routeIs($child1->route) ? 'bg-primary/10 text-primary font-medium' : 'text-base-content/60 hover:text-base-content hover:bg-base-content/5' }}">
                                        <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 {{ $child1->route && request()->routeIs($child1->route) ? 'scale-150 bg-primary' : 'bg-base-content/20 group-hover:bg-primary' }}"></div>
                                        <span class="whitespace-nowrap">{{ $child1->name }}</span>
                                    </a>
                                </li>
                            @else
                                <!-- Level 2: Has Children -->
                                <li x-data="{ open1: false }">
                                    <button @click="open1 = !open1" class="w-full group flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm text-secondary/70 hover:text-secondary hover:bg-base-content/5">
                                        <div class="flex items-center">
                                            <div class="w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300 group-hover:scale-150 bg-secondary/30 group-hover:bg-secondary"></div>
                                            <span class="whitespace-nowrap">{{ $child1->name }}</span>
                                        </div>
                                        <svg :class="open1 ? 'rotate-180 text-secondary' : 'text-secondary/60'" class="w-3.5 h-3.5 transition-transform duration-300 ease-in-out group-hover:text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <ul x-show="open1" class="mt-1 space-y-1 ml-5 relative before:absolute before:inset-y-0 before:left-1 before:w-[1px] before:bg-secondary/20" x-cloak>
                                        @foreach($child1->children as $child2)
                                            @if($child2->children->isEmpty())
                                                <!-- Level 3: No Children -->
                                                <li>
                                                    @php
                                                        $url2 = $child2->route ? (Str::startsWith($child2->route, 'http') ? $child2->route : route($child2->route)) : '#';
                                                    @endphp
                                                    <a href="{{ $url2 }}" {!! Str::startsWith($child2->route ?? '', 'http') ? 'target="_blank"' : '' !!} class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-base-content/5 hover:translate-x-1 text-sm {{ $child2->route && request()->routeIs($child2->route) ? 'text-accent font-medium bg-accent/10' : 'text-accent/70 hover:text-accent' }}">
                                                        <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 {{ $child2->route && request()->routeIs($child2->route) ? 'scale-150 bg-accent' : 'bg-accent/30 group-hover:bg-accent' }}"></div>
                                                        <span class="whitespace-nowrap">{{ $child2->name }}</span>
                                                    </a>
                                                </li>
                                            @else
                                                <!-- Level 3: Has Children -->
                                                <li x-data="{ open2: false }">
                                                    <button @click="open2 = !open2" class="group w-full flex items-center justify-between px-4 py-2 rounded-xl transition-all duration-300 hover:bg-base-content/5 text-sm text-accent/70 hover:text-accent">
                                                        <div class="flex items-center">
                                                            <div class="w-1 h-1 rounded-full mr-3 transition-transform duration-300 group-hover:scale-150 bg-accent/30 group-hover:bg-accent"></div>
                                                            <span class="whitespace-nowrap transition-transform duration-300 group-hover:translate-x-1">{{ $child2->name }}</span>
                                                        </div>
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" :class="open2 ? 'rotate-180 text-accent' : 'text-accent/60'" class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-300"><path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" /></svg>
                                                    </button>
                                                    <ul x-show="open2" class="mt-1 space-y-1 ml-6 relative before:absolute before:inset-y-0 before:-left-1 before:w-[1px] before:bg-accent/20" x-cloak>
                                                        @foreach($child2->children as $child3)
                                                            <!-- Level 4: Leaf -->
                                                            <li>
                                                                @php
                                                                    $url3 = $child3->route ? (Str::startsWith($child3->route, 'http') ? $child3->route : route($child3->route)) : '#';
                                                                @endphp
                                                                <a href="{{ $url3 }}" {!! Str::startsWith($child3->route ?? '', 'http') ? 'target="_blank"' : '' !!} class="group block px-4 py-1.5 rounded-lg hover:bg-base-content/5 hover:translate-x-1 transition-all duration-200 text-xs {{ $child3->route && request()->routeIs($child3->route) ? 'text-accent/90 font-bold' : 'text-base-content/60 hover:text-accent/80' }}"><span class="whitespace-nowrap">{{ $child3->name }}</span></a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach
        
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


