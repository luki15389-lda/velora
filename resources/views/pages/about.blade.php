<x-layouts.app title="About">
    {{-- Hero --}}
    <section class="bg-sand-200">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-20 sm:py-28 text-center">
            <p class="text-xs tracking-[0.3em] text-ink-400 uppercase">Our story</p>
            <h1 class="mt-4 font-editorial text-4xl sm:text-5xl leading-[1.15]">
                Clothing built to be worn for years, not one season.
            </h1>
            <p class="mt-6 max-w-2xl mx-auto text-ink-400 leading-relaxed">
                VELORA started in a Jakarta workroom in 2019 with four shirts and one question:
                why does clothing that fits well cost so much, and clothing that costs little
                fall apart after a season?
            </p>
        </div>
    </section>

    {{-- Stats --}}
    <section class="border-b border-sand-300">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-2 sm:grid-cols-4 gap-8 text-center">
            @foreach ([
                ['2019', 'Founded in Jakarta'],
                ['40+', 'Mills and workshops we work with directly'],
                ['120k', 'Pieces made, in small runs'],
                ['30 days', 'To return anything, no questions'],
            ] as [$stat, $label])
                <div>
                    <p class="font-editorial text-3xl">{{ $stat }}</p>
                    <p class="mt-1 text-xs text-ink-400">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Story --}}
    <section class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid gap-12 sm:grid-cols-[8rem_1fr]">
            <p class="font-editorial text-6xl text-sand-300 leading-none">01</p>
            <div>
                <h2 class="font-editorial text-2xl">Where it started</h2>
                <p class="mt-4 text-ink-600 leading-relaxed">
                    The first VELORA run was four oversize t-shirts, cut from combed cotton
                    sourced from a mill we could actually visit. We sold them out of a rented
                    studio in Kebayoran Baru, one fitting at a time. What people kept coming
                    back for wasn't the design — it was that the shirt from six months ago still
                    looked like the shirt they bought.
                </p>
            </div>
        </div>

        <div class="grid gap-12 sm:grid-cols-[8rem_1fr] mt-16">
            <p class="font-editorial text-6xl text-sand-300 leading-none">02</p>
            <div>
                <h2 class="font-editorial text-2xl">How we work now</h2>
                <p class="mt-4 text-ink-600 leading-relaxed">
                    We still work the same way, just at a larger scale. Every fabric is chosen
                    for how it holds up after twenty washes, not how it photographs on day one.
                    We run small batches instead of forecasting a season's demand months out —
                    it costs more per piece, but it means nothing sits marked down in a warehouse,
                    and it means we can fix a fit issue in the next run instead of the next year.
                </p>
            </div>
        </div>

        <div class="grid gap-12 sm:grid-cols-[8rem_1fr] mt-16">
            <p class="font-editorial text-6xl text-sand-300 leading-none">03</p>
            <div>
                <h2 class="font-editorial text-2xl">Where we're going</h2>
                <p class="mt-4 text-ink-600 leading-relaxed">
                    Underwear, shoes and bags joined the range because customers kept asking
                    what else we'd apply the same standard to. The answer is: anything, as long
                    as we can stand behind the material and the maker. That's the only filter
                    a new category has to pass.
                </p>
            </div>
        </div>
    </section>

    {{-- Values --}}
    <section class="bg-white border-y border-sand-300">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-20">
            <h2 class="font-editorial text-3xl text-center">What we hold ourselves to</h2>

            <div class="mt-12 grid gap-10 sm:grid-cols-3">
                @foreach ([
                    [
                        'icon' => 'M12 3v18m9-9H3',
                        'title' => 'Made in small runs',
                        'body' => 'We produce what we can sell without discounting, not what a forecast says we should.',
                    ],
                    [
                        'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                        'title' => 'Sourced, not just sold',
                        'body' => "Every fabric supplier on our list is one we've visited in person, more than once.",
                    ],
                    [
                        'icon' => 'M16.5 7.5h-9v9h9v-9Z M7.5 3v4.5M16.5 3v4.5M3 7.5h18M3 16.5h18',
                        'title' => '30-day returns',
                        'body' => "If it doesn't fit or wear the way we said it would, send it back. No fine print.",
                    ],
                ] as $value)
                    <div class="text-center">
                        <div class="mx-auto h-12 w-12 rounded-full bg-sand-100 flex items-center justify-center">
                            <svg class="h-5 w-5 text-ink-900" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $value['icon'] }}" />
                            </svg>
                        </div>
                        <h3 class="mt-4 font-medium">{{ $value['title'] }}</h3>
                        <p class="mt-2 text-sm text-ink-400 leading-relaxed">{{ $value['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-ink-900 text-white">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-16 text-center">
            <h2 class="font-editorial text-3xl">See it for yourself</h2>
            <p class="mt-3 text-white/60 max-w-lg mx-auto">
                Everything we've described above is easiest to judge in person, but the shop is
                the next best thing.
            </p>
            <a href="{{ route('shop') }}" wire:navigate class="btn mt-8 bg-clay-400 text-ink-900 hover:bg-white">Browse the shop</a>
        </div>
    </section>
</x-layouts.app>
