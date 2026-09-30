<x-layouts.minimal title="404">
<section class="mx-auto max-w-lg px-4 py-28 text-center w-full">
    <svg class="h-14 w-14 mx-auto text-ink-400" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="9" stroke-linecap="round" stroke-linejoin="round" />
        <polygon points="15.5,8.5 13.3,13.3 8.5,15.5 10.7,10.7" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <p class="font-editorial text-6xl mt-6">404</p>
    <h1 class="mt-4 text-xl">This page wandered off</h1>
    <p class="mt-3 text-ink-400">The link may be old, or the product may have been retired. Try the store front.</p>
    <a href="{{ route('home') }}" class="btn-primary mt-8">Back to the store</a>
</section>
</x-layouts.minimal>