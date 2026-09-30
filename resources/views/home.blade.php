<x-layouts.app>
<x-disclaimer-modal />
{{-- Hero --}}
    <section class="relative bg-sand-200">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10 sm:py-14 grid gap-10 lg:grid-cols-2 lg:items-start">
            <div>
                <p class="text-sm text-ink-400">New season, {{ now()->year }}</p>
                <h1 class="mt-4 font-editorial text-4xl sm:text-5xl lg:text-6xl leading-[1.1]">
                    Clothes that stay in rotation long after the season ends.
                </h1>
                <p class="mt-6 max-w-md text-ink-400 leading-relaxed">
                    Cotton, linen and leather, cut in small runs. Everything here is made to be worn
                    through, not put away.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('shop') }}" wire:navigate class="btn-primary">Shop new arrivals</a>
                    <a href="{{ route('about') }}" class="btn-outline">Our approach</a>
                </div>
            </div>

            @if ($newArrivals->isNotEmpty())
                <div class="grid grid-cols-2 gap-4">
                    @foreach ($newArrivals->take(2) as $product)
                        <div class="aspect-[3/4] overflow-hidden bg-sand-300">
                            @if ($product->primaryImage)
                                <img src="{{ $product->primaryImage->url }}" alt="{{ $product->primaryImage->alt }}"
                                     class="h-full w-full object-cover">
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Departments --}}
    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
        <x-section-heading title="Shop by department" />
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($departments as $department)
                <a href="{{ route('categories.show', $department) }}" wire:navigate class="card p-6 border border-sand-300 hover:border-ink-900 transition-colors">
                    <p class="text-lg font-medium">{{ $department->name }}</p>
                    <p class="mt-2 text-sm text-ink-400">
                        {{ $department->children->pluck('name')->take(3)->implode(' · ') ?: 'Explore' }}
                    </p>
                </a>
            @endforeach
        </div>
    </section>

    @foreach ([
        ['title' => 'New arrivals', 'items' => $newArrivals, 'link' => route('shop', ['sort' => 'newest'])],
        ['title' => 'Best sellers', 'items' => $bestSellers, 'link' => route('shop', ['sort' => 'best_selling'])],
        ['title' => "Men's collection", 'items' => $menCollection, 'link' => route('shop', ['gender' => 'men'])],
        ['title' => "Women's collection", 'items' => $womenCollection, 'link' => route('shop', ['gender' => 'women'])],
    ] as $block)
        @continue($block['items']->isEmpty())

        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
            <x-section-heading :title="$block['title']" :link="$block['link']" />
            <div class="grid gap-x-5 gap-y-10 grid-cols-2 lg:grid-cols-4">
                @foreach ($block['items']->take(4) as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endforeach

    {{-- Promotional banner --}}
    <section class="bg-ink-900 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 sm:flex sm:items-center sm:justify-between gap-8">
            <div>
                <h2 class="font-editorial text-3xl">Free shipping over {{ rupiah((int) setting('shipping.free_threshold', 500000)) }}</h2>
                <p class="mt-3 text-white/70 max-w-lg">
                    Applies to every order across Indonesia. Use code GRATISONGKIR at checkout if you are under the threshold.
                </p>
            </div>
            <a href="{{ route('register') }}" class="mt-6 sm:mt-0 btn bg-clay-400 text-ink-900 hover:bg-white">Create an account</a>
        </div>
    </section>

    {{-- Reviews --}}
    @if ($testimonials->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
            <x-section-heading title="What customers say" />
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($testimonials as $review)
                    <figure class="card p-6 border border-sand-300">
                        <p class="text-clay-500 text-sm">{{ str_repeat('★', $review->rating) }}</p>
                        <blockquote class="mt-3 font-editorial text-lg leading-relaxed">{{ $review->comment }}</blockquote>
                        <figcaption class="mt-4 text-sm text-ink-400">
                            {{ $review->user->name }} — {{ $review->product->name }}
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Newsletter --}}
    <section class="border-t border-sand-300">
        <div class="mx-auto max-w-2xl px-4 py-16 text-center">
            <h2 class="font-editorial text-2xl">One email a month, no more than that</h2>
            <p class="mt-3 text-ink-400">New drops, restocks and the occasional archive sale.</p>
            <form class="mt-6 flex flex-col sm:flex-row gap-3" onsubmit="event.preventDefault()">
                <label for="newsletter-email" class="sr-only">Email address</label>
                <input id="newsletter-email" type="email" required placeholder="you@example.com" class="field">
                <button type="submit" class="btn-primary whitespace-nowrap">Subscribe</button>
            </form>
        </div>
    </section>
</x-layouts.app>