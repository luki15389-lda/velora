<footer class="mt-24 border-t border-sand-300 bg-sand-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-14 grid gap-10 md:grid-cols-4">
        <div class="md:col-span-2">
            <p class="text-lg font-semibold tracking-[0.35em]">VELORA</p>
            <p class="mt-4 max-w-sm font-editorial text-ink-400 leading-relaxed">
                {{ setting('store.tagline', 'Considered clothing for every day') }}. Designed in Jakarta,
                made in small runs so nothing sits in a warehouse for a season.
            </p>
        </div>

        <div class="text-sm space-y-2">
            <p class="font-medium">Help</p>
            <a href="{{ route('faq') }}" class="block text-ink-400 hover:text-ink-900">FAQ</a>
            <a href="{{ route('contact') }}" class="block text-ink-400 hover:text-ink-900">Contact</a>
            <a href="{{ route('about') }}" class="block text-ink-400 hover:text-ink-900">About</a>
        </div>

        <div class="text-sm space-y-2">
            <p class="font-medium">Reach us</p>
            <p class="text-ink-400">{{ setting('store.email', 'hello@velora.test') }}</p>
            <p class="text-ink-400">{{ setting('store.phone') }}</p>
            <p class="text-ink-400">{{ setting('store.address') }}</p>
        </div>
    </div>

    <div class="border-t border-sand-300 py-6 text-center text-xs text-ink-400">
        &copy; {{ now()->year }} VELORA. All rights reserved.
    </div>
</footer>
