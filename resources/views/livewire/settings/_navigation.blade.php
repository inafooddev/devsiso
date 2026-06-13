<!-- Tabular Navigation untuk Settings -->
<div role="tablist" class="tabs tabs-boxed bg-base-200/50 p-1 w-fit rounded-xl border border-base-300">
    <a role="tab" href="{{ route('users.index') }}" class="tab tab-sm md:tab-md font-semibold transition-all {{ request()->routeIs('users.index') ? 'tab-active bg-base-100 shadow-sm text-base-content rounded-lg' : 'text-base-content/60 hover:text-base-content' }}">
        <x-heroicon-s-users class="w-4 h-4 mr-2" /> Pengguna
    </a>
    <a role="tab" href="{{ route('roles.index') }}" class="tab tab-sm md:tab-md font-semibold transition-all {{ request()->routeIs('roles.index') ? 'tab-active bg-base-100 shadow-sm text-base-content rounded-lg' : 'text-base-content/60 hover:text-base-content' }}">
        <x-heroicon-s-shield-check class="w-4 h-4 mr-2" /> Role (Peran)
    </a>
    <a role="tab" href="{{ route('access-groups.index') }}" class="tab tab-sm md:tab-md font-semibold transition-all {{ request()->routeIs('access-groups.index') ? 'tab-active bg-base-100 shadow-sm text-base-content rounded-lg' : 'text-base-content/60 hover:text-base-content' }}">
        <x-heroicon-s-view-columns class="w-4 h-4 mr-2" /> View Menu (Akses Grup)
    </a>
    <a role="tab" href="{{ route('menus.index') }}" class="tab tab-sm md:tab-md font-semibold transition-all {{ request()->routeIs('menus.index') ? 'tab-active bg-base-100 shadow-sm text-base-content rounded-lg' : 'text-base-content/60 hover:text-base-content' }}">
        <x-heroicon-s-squares-2x2 class="w-4 h-4 mr-2" /> Manajemen Menu
    </a>
</div>
