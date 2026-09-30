<div class="border border-sand-300 bg-white p-5">
    @if ($submitted)
        <p class="text-sm text-ink-400">Review submitted. Thanks for taking the time.</p>
    @else
        <p class="text-sm font-medium">Review {{ $item->product_name }}</p>

        <div class="mt-4">
            <p class="text-sm mb-2">Rating</p>
            <div class="flex gap-1">
                @for ($star = 1; $star <= 5; $star++)
                    <button type="button" wire:click="$set('rating', {{ $star }})"
                            class="text-2xl leading-none {{ $rating >= $star ? 'text-clay-500' : 'text-sand-300' }}"
                            aria-label="{{ $star }} star{{ $star > 1 ? 's' : '' }}">★</button>
                @endfor
            </div>
            @error('rating')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div class="mt-4">
            <label for="comment-{{ $item->id }}" class="block text-sm mb-1">Your review</label>
            <textarea id="comment-{{ $item->id }}" wire:model="comment" rows="4" class="field"
                      placeholder="How does it fit? How does the fabric hold up?"></textarea>
            @error('comment')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div class="mt-4">
            <label for="photo-{{ $item->id }}" class="block text-sm mb-1">Photo (optional)</label>
            <input id="photo-{{ $item->id }}" type="file" wire:model="photo" accept="image/*" class="text-sm">
            <div wire:loading wire:target="photo" class="mt-1 text-xs text-ink-400">Uploading…</div>
            @error('photo')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <button type="button" wire:click="save" wire:loading.attr="disabled" class="btn-primary mt-5">Submit review</button>
    @endif
</div>
