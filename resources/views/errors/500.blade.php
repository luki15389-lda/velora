<x-layouts.minimal title="500">
<section class="mx-auto max-w-lg px-4 py-28 text-center w-full">
    <svg class="h-14 w-14 mx-auto text-ink-400" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24">
        <path d="M12 3.5 21 20H3L12 3.5Z" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M12 10v4M12 17h.01" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <p class="font-editorial text-6xl mt-6">500</p>
    <h1 class="mt-4 text-xl">Something broke on our side</h1>
    <p class="mt-3 text-ink-400">The error has been logged. Try again in a moment.</p>
    <a href="{{ route('home') }}" class="btn-primary mt-8">Back to the store</a>
</section>
</x-layouts.minimal>