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
    
    /* Bulletproof Icon Sizer */
    .sidebar-icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .sidebar-icon-wrapper svg {
        width: 20px !important;
        height: 20px !important;
        flex-shrink: 0 !important;
        margin: 0 !important;
    }
    .sidebar-icon-wrapper i {
        font-size: 20px !important;
        line-height: 1 !important;
        width: 20px !important;
        text-align: center !important;
        flex-shrink: 0 !important;
        margin: 0 !important;
    }
</style>

<aside 
    x-data="{ 
        isPinned: localStorage.getItem('sidebarPinned') === 'true',
        hoverTimeout: null,
        leaveTimeout: null
    }"
    x-init="sidebarOpen = isPinned"
    @mouseenter="clearTimeout(leaveTimeout); hoverTimeout = setTimeout(() => { sidebarOpen = true }, 150)"
    @mouseleave="clearTimeout(hoverTimeout); if(!isPinned) { leaveTimeout = setTimeout(() => { sidebarOpen = false }, 150) }"
    :class="sidebarOpen ? 'w-64' : 'w-[70px] md:w-20'" 
    class="bg-base-300 text-base-content transition-[width] duration-500 ease-in-out flex-shrink-0 flex flex-col shadow-2xl relative z-40 border-r border-base-content/5 h-full">
    
    <!-- Enterprise Header -->
    <div class="flex items-center justify-center h-16 border-b border-base-content/10 relative overflow-hidden bg-gradient-to-b from-base-content/5 to-transparent shrink-0">
        <a href="#" class="flex items-center justify-center w-full h-full gap-2 transition-transform duration-300 relative group cursor-default focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-inset">
            <!-- Pure SVG Icon -->
            <div class="w-[34px] h-[34px] shrink-0 relative flex items-center justify-center transition-transform duration-500 group-hover:scale-105">
                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-sm">
                    <!-- Outer Hexagon Frame -->
                    <g class="text-base-content/80 group-hover:text-base-content transition-colors duration-300">
                        <polygon points="50,6 88,28 88,72 50,94 12,72 12,28" stroke="currentColor" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <!-- Inner Geometric S -->
                    <g class="text-primary transition-colors duration-300">
                        <path d="M 68 32 L 42 32 L 32 46 L 68 54 L 58 68 L 32 68" stroke="currentColor" stroke-width="10" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                </svg>
            </div>
            
            <!-- Text -->
            <div x-show="sidebarOpen"
                 x-transition:enter="transition ease-out duration-300 delay-75"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 -translate-x-4"
                 class="flex items-center whitespace-nowrap relative -top-[1px]"
                 x-cloak>
                <span class="text-[22px] font-extrabold tracking-tight text-base-content leading-none">
                    SISO<span class="text-primary">.</span>
                </span>
            </div>
        </a>
    </div>
    
    <!-- Navigation -->
    <nav aria-label="Sidebar Navigation" class="flex-1 overflow-y-auto overflow-x-hidden sidebar-scroll py-6 px-3 space-y-1.5">
        
        @php
            $normalizeIcon = function($html) {
                if (!$html) return '';
                // 1. Tambahkan viewBox jika tidak ada agar SVG tidak terpotong (crop) saat di-resize
                if (stripos($html, '<svg') !== false && stripos($html, 'viewBox') === false) {
                    preg_match('/width=["\']?(\d+)/i', $html, $wMatch);
                    preg_match('/height=["\']?(\d+)/i', $html, $hMatch);
                    $w = $wMatch[1] ?? 24;
                    $h = $hMatch[1] ?? 24;
                    $html = preg_replace('/<svg/i', '<svg viewBox="0 0 ' . $w . ' ' . $h . '"', $html, 1);
                }
                // 2. Hapus class Tailwind bawaan (width, height, margin) yang bikin icon miring/kegedean
                $html = preg_replace('/\b(w-\d+|h-\d+|m-\d+|mr-\d+|ml-\d+|mt-\d+|mb-\d+|w-\[[^\]]+\]|h-\[[^\]]+\])\b/', '', $html);
                // 3. Hapus attribute width & height bawaan agar CSS sidebar bisa ambil alih
                $html = preg_replace('/\b(width|height)=["\'][^"\']*["\']/i', '', $html);
                return $html;
            };

            $isActiveMenu = function($menuItem) use (&$isActiveMenu) {
                if ($menuItem->route && request()->routeIs($menuItem->route)) return true;
                if ($menuItem->children) {
                    foreach ($menuItem->children as $child) {
                        if ($isActiveMenu($child)) return true;
                    }
                }
                return false;
            };

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
                    $isActive0 = $menu->route && request()->routeIs($menu->route);
                @endphp
                <a href="{{ $url0 }}" {!! Str::startsWith($menu->route ?? '', 'http') ? 'target="_blank"' : '' !!}
                   :title="!sidebarOpen ? '{{ $menu->name }}' : ''"
                   @if($isActive0) aria-current="page" @endif
                   class="group w-full flex items-center px-4 py-3 rounded-xl transition-all duration-300 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-base-300 {{ $isActive0 ? 'bg-gradient-to-r from-primary/10 to-transparent border-l-[3px] border-primary text-base-content shadow-md' : 'border-l-[3px] border-transparent text-base-content/70 hover:bg-base-content/5 hover:text-base-content' }}">
                    <div class="w-6 h-6 flex items-center justify-center shrink-0 sidebar-icon-wrapper">
                        @if($menu->icon) {!! $normalizeIcon($menu->icon) !!} @endif
                    </div>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-medium truncate flex-1 min-w-0 transition-transform duration-300 group-hover:translate-x-1" x-cloak>{{ $menu->name }}</span>
                </a>
            @else
                <!-- Level 1: Has Children -->
                <div x-data="{ open: {{ $isActiveMenu($menu) ? 'true' : 'false' }} }">
                    <button @click="if(!sidebarOpen) { sidebarOpen = true; } isPinned = true; localStorage.setItem('sidebarPinned', 'true'); open = !open"
                            :title="!sidebarOpen ? '{{ $menu->name }}' : ''"
                            :aria-expanded="open"
                            class="group w-full flex items-center px-4 py-3 rounded-xl transition-all duration-300 ease-out border-l-[3px] focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-base-300 {{ $isActiveMenu($menu) ? 'border-primary/50 text-primary font-medium bg-primary/5' : 'border-transparent text-base-content/70 hover:bg-base-content/5 hover:text-base-content' }}">
                        <div class="w-6 h-6 flex items-center justify-center shrink-0 sidebar-icon-wrapper">
                            @if($menu->icon) {!! $normalizeIcon($menu->icon) !!} @endif
                        </div>
                        <span x-show="sidebarOpen" class="ml-3 font-medium truncate flex-1 min-w-0 text-left transition-transform duration-300 group-hover:translate-x-1" x-cloak>{{ $menu->name }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" x-show="sidebarOpen" :class="open ? 'rotate-180 text-primary' : '{{ $isActiveMenu($menu) ? 'text-primary' : 'text-base-content/40' }}'" class="w-4 h-4 ml-2 shrink-0 transition-transform duration-300 ease-in-out group-hover:text-primary/80" x-cloak><path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" /></svg>
                    </button>
                    <ul x-show="open && sidebarOpen" 
                        x-transition:enter="transition-all ease-out duration-300 origin-top"
                        x-transition:enter-start="opacity-0 -translate-y-2 scale-y-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
                        x-transition:leave="transition-all ease-in duration-200 origin-top"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-y-100"
                        x-transition:leave-end="opacity-0 -translate-y-2 scale-y-95"
                        class="mt-1.5 space-y-1 ml-4 relative before:absolute before:inset-y-0 before:left-[9px] before:w-[1px] before:bg-base-content/10" x-cloak>
                        @foreach($menu->children as $child1)
                            @if($child1->children->isEmpty())
                                <!-- Level 2: No Children -->
                                <li>
                                    @php
                                        $url1 = $child1->route ? (Str::startsWith($child1->route, 'http') ? $child1->route : route($child1->route)) : '#';
                                        $isActive1 = $child1->route && request()->routeIs($child1->route);
                                    @endphp
                                    <a href="{{ $url1 }}" {!! Str::startsWith($child1->route ?? '', 'http') ? 'target="_blank"' : '' !!} 
                                       :title="!sidebarOpen ? '{{ $child1->name }}' : ''"
                                       @if($isActive1) aria-current="page" @endif
                                       class="group flex items-center px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-base-300 {{ $isActive1 ? 'bg-primary/10 text-primary font-medium' : 'text-base-content/60 hover:text-base-content hover:bg-base-content/5' }}">
                                        <div class="w-1.5 h-1.5 rounded-full mr-3 shrink-0 transition-all duration-300 group-hover:scale-150 {{ $isActive1 ? 'scale-150 bg-primary' : 'bg-base-content/20 group-hover:bg-primary' }}"></div>
                                        <span class="truncate flex-1 min-w-0">{{ $child1->name }}</span>
                                    </a>
                                </li>
                            @else
                                <!-- Level 2: Has Children -->
                                <li x-data="{ open1: {{ $isActiveMenu($child1) ? 'true' : 'false' }} }">
                                    <button @click="isPinned = true; localStorage.setItem('sidebarPinned', 'true'); open1 = !open1" 
                                            :title="!sidebarOpen ? '{{ $child1->name }}' : ''"
                                            :aria-expanded="open1"
                                            class="w-full group flex items-center justify-between px-4 py-2.5 rounded-xl transition-all duration-300 hover:translate-x-1 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-base-300 {{ $isActiveMenu($child1) ? 'text-primary font-medium' : 'text-base-content/70 hover:text-base-content hover:bg-base-content/5' }}">
                                        <div class="flex items-center overflow-hidden flex-1 min-w-0 mr-2">
                                            <div class="w-1.5 h-1.5 rounded-full mr-3 shrink-0 transition-all duration-300 group-hover:scale-150 {{ $isActiveMenu($child1) ? 'bg-primary' : 'bg-base-content/20 group-hover:bg-primary' }}"></div>
                                            <span class="truncate flex-1 min-w-0 text-left">{{ $child1->name }}</span>
                                        </div>
                                        <svg :class="open1 ? 'rotate-180 text-base-content' : '{{ $isActiveMenu($child1) ? 'text-primary' : 'text-base-content/40' }}'" class="w-3.5 h-3.5 shrink-0 transition-transform duration-300 ease-in-out group-hover:text-base-content" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <ul x-show="open1" 
                                        x-transition:enter="transition-all ease-out duration-300 origin-top"
                                        x-transition:enter-start="opacity-0 -translate-y-1 scale-y-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
                                        x-transition:leave="transition-all ease-in duration-200 origin-top"
                                        x-transition:leave-start="opacity-100 translate-y-0 scale-y-100"
                                        x-transition:leave-end="opacity-0 -translate-y-1 scale-y-95"
                                        class="mt-1 space-y-1 ml-5 relative before:absolute before:inset-y-0 before:left-1 before:w-[1px] before:bg-base-content/10" x-cloak>
                                        @foreach($child1->children as $child2)
                                            @if($child2->children->isEmpty())
                                                <!-- Level 3: No Children -->
                                                <li>
                                                    @php
                                                        $url2 = $child2->route ? (Str::startsWith($child2->route, 'http') ? $child2->route : route($child2->route)) : '#';
                                                        $isActive2 = $child2->route && request()->routeIs($child2->route);
                                                    @endphp
                                                    <a href="{{ $url2 }}" {!! Str::startsWith($child2->route ?? '', 'http') ? 'target="_blank"' : '' !!} 
                                                       :title="!sidebarOpen ? '{{ $child2->name }}' : ''"
                                                       @if($isActive2) aria-current="page" @endif
                                                       class="group flex items-center px-4 py-2 rounded-xl transition-all duration-300 hover:bg-base-content/5 hover:translate-x-1 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-base-300 {{ $isActive2 ? 'text-primary font-medium bg-primary/10' : 'text-base-content/60 hover:text-primary' }}">
                                                        <div class="w-1 h-1 rounded-full mr-3 shrink-0 transition-transform duration-300 group-hover:scale-150 {{ $isActive2 ? 'scale-150 bg-primary' : 'bg-base-content/20 group-hover:bg-primary' }}"></div>
                                                        <span class="truncate flex-1 min-w-0">{{ $child2->name }}</span>
                                                    </a>
                                                </li>
                                            @else
                                                <!-- Level 3: Has Children -->
                                                <li x-data="{ open2: {{ $isActiveMenu($child2) ? 'true' : 'false' }} }">
                                                    <button @click="isPinned = true; localStorage.setItem('sidebarPinned', 'true'); open2 = !open2" 
                                                            :title="!sidebarOpen ? '{{ $child2->name }}' : ''"
                                                            :aria-expanded="open2"
                                                            class="group w-full flex items-center justify-between px-4 py-2 rounded-xl transition-all duration-300 hover:bg-base-content/5 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-base-300 {{ $isActiveMenu($child2) ? 'text-primary font-medium' : 'text-base-content/70 hover:text-primary' }}">
                                                        <div class="flex items-center overflow-hidden flex-1 min-w-0 mr-2">
                                                            <div class="w-1 h-1 rounded-full mr-3 shrink-0 transition-transform duration-300 group-hover:scale-150 {{ $isActiveMenu($child2) ? 'bg-primary' : 'bg-base-content/20 group-hover:bg-primary' }}"></div>
                                                            <span class="truncate flex-1 min-w-0 text-left transition-transform duration-300 group-hover:translate-x-1">{{ $child2->name }}</span>
                                                        </div>
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" :class="open2 ? 'rotate-180 text-primary' : '{{ $isActiveMenu($child2) ? 'text-primary' : 'text-base-content/40' }}'" class="w-3.5 h-3.5 shrink-0 transition-transform duration-300"><path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" /></svg>
                                                    </button>
                                                    <ul x-show="open2" 
                                                        x-transition:enter="transition-all ease-out duration-300 origin-top"
                                                        x-transition:enter-start="opacity-0 -translate-y-1 scale-y-95"
                                                        x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
                                                        x-transition:leave="transition-all ease-in duration-200 origin-top"
                                                        x-transition:leave-start="opacity-100 translate-y-0 scale-y-100"
                                                        x-transition:leave-end="opacity-0 -translate-y-1 scale-y-95"
                                                        class="mt-1 space-y-1 ml-6 relative before:absolute before:inset-y-0 before:-left-1 before:w-[1px] before:bg-base-content/10" x-cloak>
                                                        @foreach($child2->children as $child3)
                                                            <!-- Level 4: Leaf -->
                                                            <li>
                                                                @php
                                                                    $url3 = $child3->route ? (Str::startsWith($child3->route, 'http') ? $child3->route : route($child3->route)) : '#';
                                                                    $isActive3 = $child3->route && request()->routeIs($child3->route);
                                                                @endphp
                                                                <a href="{{ $url3 }}" {!! Str::startsWith($child3->route ?? '', 'http') ? 'target="_blank"' : '' !!} 
                                                                   :title="!sidebarOpen ? '{{ $child3->name }}' : ''"
                                                                   @if($isActive3) aria-current="page" @endif
                                                                   class="group block px-4 py-1.5 rounded-lg hover:bg-base-content/5 hover:translate-x-1 transition-all duration-200 text-xs truncate focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-base-300 {{ $isActive3 ? 'text-primary font-bold' : 'text-base-content/60 hover:text-primary' }}">
                                                                    {{ $child3->name }}
                                                                </a>
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
    <div class="h-16 flex items-center justify-center bg-base-300 border-t border-base-content/10 z-10 relative shrink-0 px-4">
        <button 
            @click="isPinned = !isPinned; localStorage.setItem('sidebarPinned', isPinned)"
            class="btn btn-ghost btn-sm w-full flex items-center justify-center gap-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-base-300"
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


