<div class="navbar bg-base-200 sticky top-0 z-20 border-b border-primary/20 shadow-md backdrop-blur-sm">
    {{-- Left: Hamburger (mobile) + Page Title --}}
    <div class="navbar-start gap-2">
        <label for="sidebar-drawer" class="btn btn-ghost btn-sm lg:hidden">
            <x-heroicon-s-bars-3 class="w-5 h-5" />
        </label>
        <h1 class="text-xl font-bold text-base-content">{{ $title }}</h1>
    </div>

    {{-- Right: Theme Toggle + User Dropdown --}}
    <div class="navbar-end gap-1">

        {{-- Theme Toggle --}}
        <label class="swap swap-rotate btn btn-ghost btn-sm" title="Toggle theme">
            <input type="checkbox" @change="toggleTheme()" :checked="!isDark" />
            {{-- Sun icon: shown when neon-light is active --}}
            <x-heroicon-s-sun class="swap-on w-5 h-5 text-warning" />
            {{-- Moon icon: shown when neon-dark is active --}}
            <x-heroicon-s-moon class="swap-off w-5 h-5 text-primary" />
        </label>

        {{-- User Dropdown --}}
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost flex items-center gap-2 px-3">
                <img
                    src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=6272a4&color=fff"
                    alt="User Avatar"
                    class="w-8 h-8 rounded-full"
                >
                <div class="hidden md:block text-left">
                    <p class="text-sm font-semibold leading-tight">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-base-content/60 leading-tight">{{ auth()->user()->userid ?? '' }}</p>
                </div>
                <x-heroicon-s-chevron-down class="w-4 h-4 text-base-content/50" />
            </div>

            <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box shadow-lg border border-base-300 w-52 p-2 mt-1 z-[1]">
                <li>
                    <a href="#" class="flex items-center gap-3">
                        <x-heroicon-o-user class="w-4 h-4 text-base-content/60" />
                        Profile
                    </a>
                </li>
                <li>
                    <a href="#" class="flex items-center gap-3">
                        <x-heroicon-s-cog-6-tooth class="w-4 h-4 text-base-content/60" />
                        Settings
                    </a>
                </li>
                <div class="divider my-1"></div>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-3 text-error w-full">
                            <x-heroicon-s-arrow-right-on-rectangle class="w-4 h-4" />
                            Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>