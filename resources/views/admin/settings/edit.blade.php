<x-layouts.admin title="Settings">
    <h1 class="font-editorial text-2xl mb-1">Store settings</h1>
    <p class="text-sm text-ink-400 mb-6">
        The WhatsApp number here is where "Place order" sends customers to confirm payment, and where the
        "Message customer" button on an order sends you back. Changing it takes effect immediately.
    </p>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-xl bg-white border border-sand-300 p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label for="store_whatsapp" class="block text-sm mb-1">WhatsApp number</label>
            <input id="store_whatsapp" name="store.whatsapp" type="text" required
                   value="{{ old('store.whatsapp', $settings['store.whatsapp']) }}"
                   placeholder="08xxxxxxxxxx or 62xxxxxxxxxx" class="field">
            <p class="mt-1 text-xs text-ink-400">Must be a number that actually has WhatsApp — this is what customers message to pay.</p>
            @error('store.whatsapp')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="store_phone" class="block text-sm mb-1">Display phone (optional, shown on the Contact page)</label>
            <input id="store_phone" name="store.phone" type="text"
                   value="{{ old('store.phone', $settings['store.phone']) }}" class="field">
            @error('store.phone')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <hr class="border-sand-200">

        <div>
            <label for="store_name" class="block text-sm mb-1">Store name</label>
            <input id="store_name" name="store.name" type="text" required
                   value="{{ old('store.name', $settings['store.name']) }}" class="field">
            @error('store.name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="store_tagline" class="block text-sm mb-1">Tagline</label>
            <input id="store_tagline" name="store.tagline" type="text"
                   value="{{ old('store.tagline', $settings['store.tagline']) }}" class="field">
        </div>

        <div>
            <label for="store_email" class="block text-sm mb-1">Contact email</label>
            <input id="store_email" name="store.email" type="email" required
                   value="{{ old('store.email', $settings['store.email']) }}" class="field">
            @error('store.email')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="store_address" class="block text-sm mb-1">Address</label>
            <textarea id="store_address" name="store.address" rows="3" class="field">{{ old('store.address', $settings['store.address']) }}</textarea>
        </div>

        <button type="submit" class="btn-primary">Save changes</button>
    </form>
</x-layouts.admin>