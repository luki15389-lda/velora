{{-- Listens to both session flashes and Livewire's dispatched "notify" events. --}}
<div x-data="{
        items: [],
        push(message, tone = 'info') {
            const id = Date.now() + Math.random();
            this.items.push({ id, message, tone });
            setTimeout(() => this.items = this.items.filter(i => i.id !== id), 4000);
        }
     }"
     x-on:notify.window="push($event.detail.message, $event.detail.tone || 'info')"
     @if (session('status')) x-init="push(@js(session('status')), 'success')" @endif
     @if (session('error')) x-init="push(@js(session('error')), 'error')" @endif
     @if (isset($errors) && $errors->any())
         x-init="@js($errors->all())
             .forEach((message) => push(message, 'error'))"
     @endif
     class="fixed bottom-6 right-4 z-[60] flex flex-col gap-2 w-[min(22rem,calc(100vw-2rem))]"
     aria-live="polite">
    <template x-for="item in items" :key="item.id">
        <div x-transition.opacity
             class="px-4 py-3 text-sm shadow-lg border"
             :class="{
                'bg-ink-900 text-white border-ink-900': item.tone === 'success',
                'bg-red-50 text-red-900 border-red-200': item.tone === 'error',
                'bg-white text-ink-900 border-sand-300': item.tone === 'info'
             }"
             x-text="item.message"></div>
    </template>
</div>