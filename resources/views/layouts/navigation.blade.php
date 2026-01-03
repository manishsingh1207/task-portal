<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>

    <x-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')">
        {{ __('Task Portal') }}
    </x-nav-link>
</div>