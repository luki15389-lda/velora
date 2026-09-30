<button type="button" wire:click="toggle" wire:loading.attr="disabled"
        class="p-2 border border-sand-300 hover:border-ink-900 transition-colors"
        aria-pressed="{{ $saved ? 'true' : 'false' }}"
        aria-label="{{ $saved ? 'Remove from wishlist' : 'Save to wishlist' }}">
    <svg class="h-5 w-5 {{ $saved ? 'fill-ink-900' : 'fill-none' }}"
         stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 20s-7-4.4-7-9.2A4 4 0 0 1 12 7.8 4 4 0 0 1 19 10.8C19 15.6 12 20 12 20Z" />
    </svg>
</button>
