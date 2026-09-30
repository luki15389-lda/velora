@props(['title', 'link' => null, 'linkLabel' => 'View all'])

<div class="flex items-end justify-between gap-4 mb-8">
    <h2 class="font-editorial text-2xl sm:text-3xl">{{ $title }}</h2>
    @if ($link)
        <a href="{{ $link }}" class="text-sm text-ink-400 hover:text-ink-900 whitespace-nowrap">{{ $linkLabel }}</a>
    @endif
</div>
