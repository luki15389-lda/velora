<a href="{{ route('cart') }}" wire:navigate class="relative p-1 hover:text-ink-400 transition-colors" aria-label="Bag">
    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M6 7.5h12l-1 12.25a1.5 1.5 0 0 1-1.5 1.25h-9A1.5 1.5 0 0 1 5 19.75L6 7.5Z" />
        <path stroke-linecap="round" d="M9 7.5V6a3 3 0 0 1 6 0v1.5" />
    </svg>

    @if ($count > 0)
        <span class="absolute -top-1 -right-1 min-w-4 h-4 px-1 rounded-full bg-ink-900 text-white text-[10px] leading-4 text-center">
            {{ $count > 99 ? '99+' : $count }}
        </span>
    @endif
</a>
