<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
    aside[data-theme="neon-dark"] * {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }
    /* Scrollbar */
    .sidebar-scroll::-webkit-scrollbar { width: 3px; }
    .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
    .sidebar-scroll::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.1);
        border-radius: 10px;
    }
    .sidebar-scroll:hover::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.2);
    }

    /* Icon */
    .sidebar-icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .sidebar-icon-wrapper svg,
    .sidebar-icon-wrapper i {
        width: 1rem !important;
        height: 1rem !important;
        font-size: 1rem !important;
        flex-shrink: 0 !important;
        margin: 0 !important;
        transition: all 0.2s ease;
    }

    /* Nav Item Base */
    .sidebar-nav-item {
        position: relative;
        transition: all 0.15s ease;
        font-weight: 300;
    }

    /* Active State */
    .sidebar-nav-item.active {
        background-color: rgba(255,255,255,0.08) !important;
        color: #fff !important;
        font-weight: 400 !important;
    }
    .sidebar-nav-item.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 20%;
        height: 60%;
        width: 3px;
        border-radius: 0 4px 4px 0;
        background: oklch(var(--p));
        box-shadow: 0 0 8px oklch(var(--p) / 0.5);
    }

    /* Hover State */
    .sidebar-nav-item:not(.active):hover {
        background-color: rgba(255,255,255,0.05) !important;
        color: #fff !important;
    }

    /* Active Bullet for sub-items */
    .sidebar-bullet-active {
        background-color: oklch(var(--p)) !important;
        box-shadow: 0 0 6px oklch(var(--p) / 0.6);
        transform: scale(1.4);
    }

    /* Tree line */
    .sidebar-tree-line {
        position: relative;
    }
    .sidebar-tree-line::before {
        content: '';
        position: absolute;
        left: 9px;
        top: 0;
        bottom: 0;
        width: 1px;
        background: rgba(255,255,255,0.07);
    }
</style>

<aside 
    data-theme="neon-dark"
    x-data="{ 
        isPinned: localStorage.getItem('sidebarPinned') === 'true',
        hoverTimeout: null,
        leaveTimeout: null
    }"
    x-init="sidebarOpen = isPinned"
    @mouseenter="clearTimeout(leaveTimeout); hoverTimeout = setTimeout(() => { sidebarOpen = true }, 150)"
    @mouseleave="clearTimeout(hoverTimeout); if(!isPinned) { leaveTimeout = setTimeout(() => { sidebarOpen = false }, 150) }"
    :class="sidebarOpen ? 'w-64' : 'w-[70px] md:w-20'" 
    style="background-color: #212631;"
    class="text-base-content transition-[width] duration-300 ease-in-out flex-shrink-0 flex flex-col shadow-2xl relative z-40 border-r border-white/5 h-full">
    
    <!-- Enterprise Header -->
    <div class="flex items-center justify-center h-16 border-b border-white/10 relative overflow-hidden shrink-0">
        <a href="#" class="flex items-center justify-center w-full h-full gap-2 transition-transform duration-300 relative group cursor-default focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-inset">
            <!-- Pure SVG Icon -->
            <div class="w-[34px] h-[34px] shrink-0 relative flex items-center justify-center transition-transform duration-500 group-hover:scale-105">
                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-sm">
                    <!-- Outer Hexagon Frame -->
                    <g class="text-base-content/80 group-hover:text-base-content transition-colors duration-300">
                        <polygon points="50,6 88,28 88,72 50,94 12,72 12,28" stroke="currentColor" stroke-width="7" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                    <!-- Inner Geometric S -->
                    <g class="text-white transition-colors duration-300">
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
                    SISO<span class="text-info">.</span>
                </span>
            </div>
        </a>
    </div>
    
    <!-- Navigation -->
    <nav aria-label="Sidebar Navigation" class="flex-1 overflow-y-auto overflow-x-hidden sidebar-scroll py-2 px-2 space-y-1">
        
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
                @if(empty($menu->icon) && empty($menu->route))
                    <!-- Group Header -->
                    <div x-show="sidebarOpen" class="px-1 pt-6 pb-2 text-[0.78125rem] font-semibold tracking-[0.15em] text-white/25 uppercase select-none" x-cloak>
                        {{ $menu->name }}
                    </div>
                    <div x-show="!sidebarOpen" class="flex justify-center pt-5 pb-1" x-cloak>
                        <div class="w-4 h-px bg-white/10 rounded"></div>
                    </div>
                @else
                    <!-- Level 1: No Children -->
                    @php
                        $url0 = $menu->route ? (Str::startsWith($menu->route, 'http') ? $menu->route : route($menu->route)) : '#';
                        $isActive0 = $menu->route && request()->routeIs($menu->route);
                    @endphp
                    <a href="{{ $url0 }}" {!! Str::startsWith($menu->route ?? '', 'http') ? 'target="_blank"' : '' !!}
                       :title="!sidebarOpen ? '{{ $menu->name }}' : ''"
                       @if($isActive0) aria-current="page" @endif
                       :class="sidebarOpen ? 'px-3 justify-start' : 'justify-center px-2'"
                       class="sidebar-nav-item {{ $isActive0 ? 'active' : '' }} group w-full flex items-center py-2 rounded-md text-[0.9375rem] focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-1 focus-visible:ring-offset-base-300 {{ $isActive0 ? 'text-white' : 'text-white' }}">
                        <div class="w-4 h-4 flex items-center justify-center shrink-0 sidebar-icon-wrapper">
                            @if($menu->icon) {!! $normalizeIcon($menu->icon) !!} @endif
                        </div>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.150ms class="ml-3 truncate flex-1 min-w-0 leading-snug" x-cloak>{{ $menu->name }}</span>
                    </a>
                @endif
            @else
                <!-- Level 1: Has Children -->
                <div x-data="{ open: {{ $isActiveMenu($menu) ? 'true' : 'false' }} }">
                    <button @click="if(!sidebarOpen) { sidebarOpen = true; } isPinned = true; localStorage.setItem('sidebarPinned', 'true'); open = !open"
                            :title="!sidebarOpen ? '{{ $menu->name }}' : ''"
                            :aria-expanded="open"
                            :class="sidebarOpen ? 'px-3 justify-start' : 'justify-center px-2'"
                            class="sidebar-nav-item {{ $isActiveMenu($menu) ? 'active' : '' }} group w-full flex items-center py-2 rounded-md text-[0.9375rem] focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-1 focus-visible:ring-offset-base-300 {{ $isActiveMenu($menu) ? 'text-white' : 'text-white' }}">
                        <div class="w-4 h-4 flex items-center justify-center shrink-0 sidebar-icon-wrapper">
                            @if($menu->icon) {!! $normalizeIcon($menu->icon) !!} @endif
                        </div>
                        <span x-show="sidebarOpen" class="ml-3 truncate flex-1 min-w-0 text-left leading-snug" x-cloak>{{ $menu->name }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" x-show="sidebarOpen" :class="open ? 'rotate-180 opacity-60' : 'opacity-40'" class="w-3 h-3 ml-1 shrink-0 transition-all duration-200 ease-in-out" x-cloak><path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" /></svg>
                    </button>
                    <ul x-show="open && sidebarOpen" 
                        x-transition:enter="transition-all ease-out duration-300 origin-top"
                        x-transition:enter-start="opacity-0 -translate-y-2 scale-y-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
                        x-transition:leave="transition-all ease-in duration-200 origin-top"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-y-100"
                        x-transition:leave-end="opacity-0 -translate-y-2 scale-y-95"
                        class="sidebar-tree-line mt-1 space-y-1" x-cloak>
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
                                       class="sidebar-nav-item {{ $isActive1 ? 'active' : '' }} group flex items-center pl-8 pr-3 py-1.5 rounded-md text-[0.875rem] focus:outline-none focus-visible:ring-2 focus-visible:ring-primary {{ $isActive1 ? 'text-white' : 'text-white/75' }}">
                                        <div class="w-1.5 h-1.5 rounded-full mr-2.5 shrink-0 transition-all duration-200 {{ $isActive1 ? 'sidebar-bullet-active bg-primary' : 'bg-white/20 group-hover:bg-white/50' }}"></div>
                                        <span class="truncate flex-1 min-w-0 leading-snug">{{ $child1->name }}</span>
                                    </a>
                                </li>
                            @else
                                <!-- Level 2: Has Children -->
                                <li x-data="{ open1: {{ $isActiveMenu($child1) ? 'true' : 'false' }} }">
                                    <button @click="isPinned = true; localStorage.setItem('sidebarPinned', 'true'); open1 = !open1" 
                                            :title="!sidebarOpen ? '{{ $child1->name }}' : ''"
                                            :aria-expanded="open1"
                                            class="sidebar-nav-item {{ $isActiveMenu($child1) ? 'active' : '' }} w-full group flex items-center justify-between pl-8 pr-3 py-1.5 rounded-md text-[0.875rem] focus:outline-none focus-visible:ring-2 focus-visible:ring-primary {{ $isActiveMenu($child1) ? 'text-white' : 'text-white/75' }}">
                                        <div class="flex items-center overflow-hidden flex-1 min-w-0 mr-2">
                                            <div class="w-1.5 h-1.5 rounded-full mr-2.5 shrink-0 transition-all duration-200 {{ $isActiveMenu($child1) ? 'sidebar-bullet-active bg-primary' : 'bg-white/20 group-hover:bg-white/50' }}"></div>
                                            <span class="truncate flex-1 min-w-0 text-left leading-snug">{{ $child1->name }}</span>
                                        </div>
                                        <svg :class="open1 ? 'rotate-180 opacity-60' : 'opacity-30'" class="w-3 h-3 shrink-0 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <ul x-show="open1" 
                                        x-transition:enter="transition-all ease-out duration-300 origin-top"
                                        x-transition:enter-start="opacity-0 -translate-y-1 scale-y-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
                                        x-transition:leave="transition-all ease-in duration-200 origin-top"
                                        x-transition:leave-start="opacity-100 translate-y-0 scale-y-100"
                                        x-transition:leave-end="opacity-0 -translate-y-1 scale-y-95"
                                        class="sidebar-tree-line mt-0.5 space-y-0.5" x-cloak>
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
                                                       class="sidebar-nav-item {{ $isActive2 ? 'active' : '' }} group flex items-center pl-8 pr-3 py-1.5 rounded-md text-[0.8125rem] focus:outline-none focus-visible:ring-2 focus-visible:ring-primary {{ $isActive2 ? 'text-white' : 'text-white/70' }}">
                                                        <div class="w-1 h-1 rounded-full mr-2.5 shrink-0 transition-all duration-200 {{ $isActive2 ? 'sidebar-bullet-active bg-primary scale-150' : 'bg-white/15 group-hover:bg-white/40' }}"></div>
                                                        <span class="truncate flex-1 min-w-0 leading-snug">{{ $child2->name }}</span>
                                                    </a>
                                                </li>
                                            @else
                                                <!-- Level 3: Has Children -->
                                                <li x-data="{ open2: {{ $isActiveMenu($child2) ? 'true' : 'false' }} }">
                                                    <button @click="isPinned = true; localStorage.setItem('sidebarPinned', 'true'); open2 = !open2" 
                                                            :title="!sidebarOpen ? '{{ $child2->name }}' : ''"
                                                            :aria-expanded="open2"
                                                            class="sidebar-nav-item {{ $isActiveMenu($child2) ? 'active' : '' }} group w-full flex items-center justify-between pl-8 pr-3 py-1.5 rounded-md text-[0.8125rem] focus:outline-none focus-visible:ring-2 focus-visible:ring-primary {{ $isActiveMenu($child2) ? 'text-white' : 'text-white/70' }}">
                                                        <div class="flex items-center overflow-hidden flex-1 min-w-0 mr-2">
                                                            <div class="w-1 h-1 rounded-full mr-2.5 shrink-0 transition-all duration-200 {{ $isActiveMenu($child2) ? 'sidebar-bullet-active bg-primary scale-150' : 'bg-white/15 group-hover:bg-white/40' }}"></div>
                                                            <span class="truncate flex-1 min-w-0 text-left leading-snug">{{ $child2->name }}</span>
                                                        </div>
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" :class="open2 ? 'rotate-180 opacity-60' : 'opacity-30'" class="w-3 h-3 shrink-0 transition-all duration-200"><path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" /></svg>
                                                    </button>
                                                    <ul x-show="open2" 
                                                        x-transition:enter="transition-all ease-out duration-300 origin-top"
                                                        x-transition:enter-start="opacity-0 -translate-y-1 scale-y-95"
                                                        x-transition:enter-end="opacity-100 translate-y-0 scale-y-100"
                                                        x-transition:leave="transition-all ease-in duration-200 origin-top"
                                                        x-transition:leave-start="opacity-100 translate-y-0 scale-y-100"
                                                        x-transition:leave-end="opacity-0 -translate-y-1 scale-y-95"
                                                        class="sidebar-tree-line mt-0.5 space-y-0.5" x-cloak>
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
                                                                   class="sidebar-nav-item {{ $isActive3 ? 'active' : '' }} group block pl-8 pr-3 py-1.5 rounded-md text-[0.75rem] truncate focus:outline-none focus-visible:ring-2 focus-visible:ring-primary {{ $isActive3 ? 'text-white' : 'text-white/65 hover:text-white' }}">
                                                                    <span class="leading-snug">{{ $child3->name }}</span>
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
    <div class="h-12 flex items-center border-t border-base-content/8 z-10 relative shrink-0" :class="sidebarOpen ? 'px-3 justify-between' : 'px-2 justify-center'">
        <span x-show="sidebarOpen" class="text-[0.6875rem] text-white/80 font-medium select-none" x-cloak>© 2026 SISO. All rights reserved.</span>
        <button 
            @click="isPinned = !isPinned; sidebarOpen = isPinned; localStorage.setItem('sidebarPinned', isPinned)"
            class="w-7 h-7 flex items-center justify-center rounded-md text-white/80 hover:text-white hover:bg-white/10 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            :title="isPinned ? 'Unpin Sidebar' : 'Pin Sidebar'"
        >
            <svg x-show="isPinned" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
            <svg x-show="!isPinned" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
</aside>


