<x-layouts.app title="FAQ">
<section class="mx-auto max-w-2xl px-4 py-20">
    <h1 class="font-editorial text-4xl">Frequently asked</h1>

    <div class="mt-10 divide-y divide-sand-300" x-data="{ open: null }">
        @foreach ([
            ['How long does delivery take?', 'Standard is 3–5 business days, express is 1–2, and same day covers Jakarta only.'],
            ['Can I return something?', 'Yes, within 30 days, unworn and with tags attached. Underwear is excluded for hygiene reasons.'],
            ['How do I track my order?', 'Every order page shows a live timeline, and a tracking number appears once the parcel leaves our warehouse.'],
            ['Which payments do you accept?', 'Bank transfer, QRIS, e-wallets and cash on delivery.'],
        ] as $index => [$question, $answer])
            <div class="py-5">
                <button type="button" class="flex w-full items-center justify-between text-left"
                        @click="open = open === {{ $index }} ? null : {{ $index }}">
                    <span class="font-medium">{{ $question }}</span>
                    <span class="text-ink-400" x-text="open === {{ $index }} ? '−' : '+'">+</span>
                </button>
                <p x-show="open === {{ $index }}" x-collapse class="mt-3 text-sm text-ink-400 leading-relaxed">{{ $answer }}</p>
            </div>
        @endforeach
    </div>
</section>
</x-layouts.app>
