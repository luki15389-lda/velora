@php
    $nav = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'grid'],
        ['label' => 'Orders', 'route' => 'admin.orders.index', 'match' => 'admin.orders.*', 'icon' => 'box'],
        ['label' => 'Products', 'route' => 'admin.products.index', 'match' => 'admin.products.*', 'icon' => 'shirt'],
        ['label' => 'Categories', 'route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'icon' => 'tag'],
        ['label' => 'Promotions', 'route' => 'admin.promotions.index', 'match' => 'admin.promotions.*', 'icon' => 'percent'],
        ['label' => 'Settings', 'route' => 'admin.settings.edit', 'match' => 'admin.settings.*', 'icon' => 'gear'],
    ];

    $icons = [
        'grid' => 'M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z',
        'shirt' => 'M8 4 5 7v3h2.5V19h9V10H19V7l-3-3-2 2h-2L8 4Z',
        'tag' => 'M4 4h7l9 9-7 7-9-9V4Zm3.5 3.5h.01',
        'percent' => 'M19 5 5 19M7.5 7.5a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm9 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z',
        'box' => 'M21 8l-9-5-9 5 9 5 9-5Zm0 0v8l-9 5m9-13-9 5m0 8-9-5V8m9 13v-8M3 8l9 5',
        'gear' => 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm8.4 4a7.4 7.4 0 0 0-.13-1.36l2.1-1.64-2-3.46-2.48 1a7.5 7.5 0 0 0-2.36-1.36L15 2h-4l-.53 2.18a7.5 7.5 0 0 0-2.36 1.36l-2.48-1-2 3.46 2.1 1.64A7.4 7.4 0 0 0 5.6 12c0 .46.04.92.13 1.36l-2.1 1.64 2 3.46 2.48-1a7.5 7.5 0 0 0 2.36 1.36L11 22h4l.53-2.18a7.5 7.5 0 0 0 2.36-1.36l2.48 1 2-3.46-2.1-1.64c.09-.44.13-.9.13-1.36Z',
    ];
@endphp

<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-ink-900 text-white flex flex-col transition-transform duration-200 lg:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

    <div class="h-16 flex items-center justify-between px-6 border-b border-white/10">
        <a href="{{ route('admin.dashboard') }}" class="text-lg font-semibold tracking-[0.3em]">VELORA</a>
        <button type="button" class="lg:hidden text-white/60" @click="sidebarOpen = false" aria-label="Close menu">✕</button>
    </div>

    <nav class="flex-1 px-3 py-6 space-y-1">
        @foreach ($nav as $item)
            @php($active = request()->routeIs($item['match']))
            <a href="{{ route($item['route']) }}"
               class="flex items-center gap-3 px-3 py-2.5 text-sm rounded transition-colors
                      {{ $active ? 'bg-white text-ink-900 font-medium' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon']] }}" />
                </svg>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="px-6 py-5 border-t border-white/10 text-xs text-white/40">
        Signed in as<br><span class="text-white/70">{{ auth()->user()->email }}</span>
    </div>
</aside>