<header x-data="{ mobile: false, search: false }" class="sticky top-0 z-50 bg-sand-100/95 backdrop-blur border-b border-sand-300">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between gap-6">
            <button type="button" class="lg:hidden p-2 -ml-2" @click="mobile = !mobile" aria-label="Open menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                </svg>
            </button>

            <a href="{{ route('home') }}" wire:navigate class="text-xl font-semibold tracking-[0.35em]">VELORA</a>

            @php
                $navLinks = [
                    ['label' => 'Home', 'route' => 'home', 'match' => 'home'],
                    ['label' => 'Shop', 'route' => 'shop', 'match' => 'shop,categories.show,products.show'],
                    ['label' => 'About', 'route' => 'about', 'match' => 'about'],
                    ['label' => 'Contact', 'route' => 'contact', 'match' => 'contact'],
                ];
            @endphp

            <nav class="hidden lg:flex items-center gap-8 text-sm">
                @foreach ($navLinks as $link)
                    @php($isActive = request()->routeIs(explode(',', $link['match'])))
                    <a href="{{ route($link['route']) }}" wire:navigate
                       class="relative py-1 transition-colors {{ $isActive ? 'text-ink-900' : 'text-ink-600 hover:text-ink-400' }}">
                        {{ $link['label'] }}
                        <span class="absolute -bottom-1 left-0 right-0 h-px bg-ink-900 transition-opacity {{ $isActive ? 'opacity-100' : 'opacity-0' }}"></span>
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-3">
                <button type="button" aria-label="Search" @click="search = !search" class="p-1 hover:text-ink-400 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="6.5" /><path stroke-linecap="round" d="m16 16 4.5 4.5" />
                    </svg>
                </button>

                @auth
                    <a href="{{ route('wishlist') }}" wire:navigate class="p-1 hover:text-ink-400" aria-label="Wishlist">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 20s-7-4.4-7-9.2A4 4 0 0 1 12 7.8 4 4 0 0 1 19 10.8C19 15.6 12 20 12 20Z" />
                        </svg>
                    </a>
                @endauth

                <livewire:cart.cart-counter />

                @auth
                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open"
                                class="h-8 w-8 rounded-full bg-ink-900 text-white text-xs">{{ auth()->user()->initials }}</button>

                        <div x-show="open" @click.outside="open = false" x-transition
                             class="absolute right-0 mt-2 w-48 bg-white border border-sand-300 py-2 text-sm shadow-lg">
                            @if (auth()->user()->canAccessBackoffice())
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 hover:bg-sand-100">Dashboard</a>
                            @endif
                            <a href="{{ route('orders.index') }}" wire:navigate class="block px-4 py-2 hover:bg-sand-100">My orders</a>
                            <a href="{{ route('wishlist') }}" wire:navigate class="block px-4 py-2 hover:bg-sand-100">Wishlist</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-sand-100 text-ink-400">Sign out</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" wire:navigate class="text-sm hover:text-ink-400">Sign in</a>
                @endauth
            </div>
        </div>

        <form x-show="search" x-collapse action="{{ route('shop') }}" method="GET" class="pb-4">
            <label for="navbar-search" class="sr-only">Search products</label>
            <input id="navbar-search" name="q" type="search" placeholder="Search by name, SKU or category"
                   class="field" x-ref="searchInput">
        </form>
    </div>

    <nav x-show="mobile" x-collapse class="lg:hidden border-t border-sand-300 px-4 py-4 space-y-3 text-sm">
        <a href="{{ route('home') }}" wire:navigate class="block py-1">Home</a>
        <a href="{{ route('shop') }}" wire:navigate class="block py-1">Shop</a>
        <a href="{{ route('about') }}" wire:navigate class="block py-1">About</a>
        <a href="{{ route('contact') }}" wire:navigate class="block py-1">Contact</a>
    </nav>
</header>
