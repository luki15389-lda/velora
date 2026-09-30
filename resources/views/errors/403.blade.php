<x-layouts.minimal title="403">
<section class="mx-auto max-w-lg px-4 py-28 text-center w-full">
    <svg class="h-14 w-14 mx-auto text-ink-400" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24">
        <rect x="5" y="11" width="14" height="9" rx="2" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M8 11V7a4 4 0 0 1 8 0v4" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <p class="font-editorial text-6xl mt-6">403</p>
    <h1 class="mt-4 text-xl">You do not have access to this page</h1>
    <p class="mt-3 text-ink-400">Your account role does not cover this area. Sign in with an account that does.</p>
    <a href="{{ route('home') }}" class="btn-primary mt-8">Back to the store</a>
</section>
</x-layouts.minimal>