<x-layouts.app title="Contact">
    <section class="bg-sand-200">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-16 sm:py-20 text-center">
            <p class="text-xs tracking-[0.3em] text-ink-400 uppercase">Get in touch</p>
            <h1 class="mt-4 font-editorial text-4xl sm:text-5xl">We answer within one business day</h1>
            <p class="mt-4 max-w-xl mx-auto text-ink-400">
                Order questions, sizing help, wholesale enquiries — whatever it is, send it below
                or reach us directly.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid gap-10 lg:grid-cols-[1fr_1.3fr]">
            {{-- Contact info --}}
            <div class="space-y-6">
                @foreach ([
                    ['icon' => 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.007 1.872l-7.5 5.25a2.25 2.25 0 0 1-2.486 0l-7.5-5.25A2.25 2.25 0 0 1 2.25 6.993V6.75', 'label' => 'Email', 'value' => setting('store.email'), 'href' => 'mailto:'.setting('store.email')],
                    ['icon' => 'M2.25 6.75c0 8.284 6.716 15 15 15h1.5a1.5 1.5 0 0 0 1.5-1.5v-2.379a1.5 1.5 0 0 0-1.06-1.436l-3.884-1.11a1.5 1.5 0 0 0-1.549.395l-1.09 1.09a12.03 12.03 0 0 1-5.657-5.656l1.09-1.09a1.5 1.5 0 0 0 .395-1.55L7.485 3.31A1.5 1.5 0 0 0 6.049 2.25H3.75a1.5 1.5 0 0 0-1.5 1.5v3Z', 'label' => 'WhatsApp / Phone', 'value' => setting('store.phone'), 'href' => 'https://wa.me/'.preg_replace('/\D+/', '', setting('store.whatsapp', ''))],
                    ['icon' => 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z', 'label' => 'Store', 'value' => setting('store.address'), 'href' => null],
                ] as $item)
                    <div class="flex gap-4">
                        <div class="h-11 w-11 shrink-0 rounded-full bg-sand-200 flex items-center justify-center">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-ink-400">{{ $item['label'] }}</p>
                            @if ($item['href'])
                                <a href="{{ $item['href'] }}" class="text-sm hover:underline" target="_blank" rel="noopener">{{ $item['value'] }}</a>
                            @else
                                <p class="text-sm">{{ $item['value'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="pt-4 border-t border-sand-300">
                    <p class="text-xs text-ink-400">Hours</p>
                    <p class="text-sm mt-1">Monday – Saturday, 9am – 6pm WIB</p>
                    <p class="text-sm text-ink-400">Closed on national holidays</p>
                </div>
            </div>

            {{-- Form --}}
            <div class="bg-white border border-sand-300 p-6 sm:p-8">
                @if (session('status'))
                    <div class="mb-6 border border-ink-900 bg-sand-100 px-4 py-3 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.send') }}" class="space-y-4">
                    @csrf

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm mb-1">Name</label>
                            <input id="name" name="name" type="text" required value="{{ old('name') }}" class="field">
                            @error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm mb-1">Email</label>
                            <input id="email" name="email" type="email" required value="{{ old('email') }}" class="field">
                            @error('email')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="subject" class="block text-sm mb-1">Subject (optional)</label>
                        <input id="subject" name="subject" type="text" value="{{ old('subject') }}" class="field">
                    </div>

                    <div>
                        <label for="message" class="block text-sm mb-1">Message</label>
                        <textarea id="message" name="message" rows="5" required class="field">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="btn-primary w-full sm:w-auto">Send message</button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.app>
