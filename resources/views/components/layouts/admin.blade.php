@props(['title' => null])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27%3E%3Crect width=%27100%27 height=%27100%27 fill=%27%231C1C1C%27/%3E%3Ctext x=%2750%27 y=%2768%27 font-family=%27Georgia,serif%27 font-size=%2760%27 fill=%27%23F7F4EF%27 text-anchor=%27middle%27%3EV%3C/text%3E%3C/svg%3E">
    <title>{{ $title ? $title.' — VELORA Admin' : 'VELORA Admin' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,300;6..72,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-sand-100 text-ink-900 font-sans antialiased overflow-x-hidden" x-data="{ sidebarOpen: false }">

    {{-- Mobile top bar --}}
    <div class="lg:hidden sticky top-0 z-40 flex items-center justify-between bg-ink-900 text-white px-4 h-14">
        <button type="button" @click="sidebarOpen = true" aria-label="Open menu">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
            </svg>
        </button>
        <span class="text-sm font-semibold tracking-[0.3em]">VELORA</span>
        <a href="{{ route('home') }}" class="text-xs text-white/60">View site</a>
    </div>

    {{-- Sidebar --}}
    <x-admin.sidebar />

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 bg-ink-900/50 z-40 lg:hidden"></div>

    {{-- Main --}}
    <div class="lg:pl-64">
        <header class="hidden lg:flex items-center justify-between border-b border-sand-300 bg-white px-8 h-16 sticky top-0 z-20">
            <h1 class="font-editorial text-xl">{{ $title ?? 'Dashboard' }}</h1>

            <div class="flex items-center gap-5 text-sm">
                <a href="{{ route('home') }}" class="text-ink-400 hover:text-ink-900">View storefront ↗</a>
                <div class="h-5 w-px bg-sand-300"></div>
                <span class="text-ink-400">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="text-ink-400 hover:text-ink-900">Sign out</button>
                </form>
            </div>
        </header>

        <x-toast />

        <main class="p-4 sm:p-6 lg:p-8">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
