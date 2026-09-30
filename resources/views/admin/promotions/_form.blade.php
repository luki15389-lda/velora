@php($coupon = $coupon ?? null)
@php($isEdit = $coupon !== null)

<form method="POST" action="{{ $isEdit ? route('admin.promotions.update', $coupon) : route('admin.promotions.store') }}" class="max-w-2xl"
      x-data="{ type: '{{ old('type', $coupon->type->value ?? 'percentage') }}' }">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="bg-white border border-sand-300 p-6 space-y-5">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="code" class="block text-sm mb-1">Coupon code</label>
                <input id="code" name="code" type="text" required
                       value="{{ old('code', $coupon->code ?? '') }}" class="field uppercase" placeholder="VELORA10">
                @error('code')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="type" class="block text-sm mb-1">Type</label>
                <select id="type" name="type" x-model="type" class="field">
                    <option value="percentage">Percentage off</option>
                    <option value="fixed">Fixed amount off</option>
                    <option value="free_shipping">Free shipping</option>
                </select>
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm mb-1">Description (optional, shown to no one but you)</label>
            <input id="description" name="description" type="text"
                   value="{{ old('description', $coupon->description ?? '') }}" class="field">
        </div>

        <div>
            <label for="category_id" class="block text-sm mb-1">Applies to</label>
            <select id="category_id" name="category_id" class="field">
                <option value="">Everything in the store</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected(old('category_id', $coupon->category_id ?? '') == $department->id)>
                        {{ $department->name }} only
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-ink-400">Restrict this code to carts that include something from one department.</p>
        </div>

        <div x-show="type !== 'free_shipping'">
            <label for="value" class="block text-sm mb-1">
                <span x-text="type === 'percentage' ? 'Percentage (%)' : 'Amount (Rp)'"></span>
            </label>
            <input id="value" name="value" type="number" min="0" step="0.01"
                   value="{{ old('value', $coupon->value ?? '') }}" class="field">
            @error('value')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="minimum_purchase" class="block text-sm mb-1">Minimum purchase (Rp)</label>
                <input id="minimum_purchase" name="minimum_purchase" type="number" min="0"
                       value="{{ old('minimum_purchase', $coupon->minimum_purchase ?? 0) }}" class="field">
            </div>

            <div x-show="type === 'percentage'">
                <label for="maximum_discount" class="block text-sm mb-1">Maximum discount (Rp, optional)</label>
                <input id="maximum_discount" name="maximum_discount" type="number" min="0"
                       value="{{ old('maximum_discount', $coupon->maximum_discount ?? '') }}" class="field">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="usage_limit" class="block text-sm mb-1">Total usage limit (optional)</label>
                <input id="usage_limit" name="usage_limit" type="number" min="1"
                       value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}" placeholder="Unlimited" class="field">
            </div>

            <div>
                <label for="usage_limit_per_user" class="block text-sm mb-1">Limit per customer</label>
                <input id="usage_limit_per_user" name="usage_limit_per_user" type="number" min="1" required
                       value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user ?? 1) }}" class="field">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="starts_at" class="block text-sm mb-1">Starts (optional)</label>
                <input id="starts_at" name="starts_at" type="date"
                       value="{{ old('starts_at', $coupon?->starts_at?->format('Y-m-d')) }}" class="field">
            </div>
            <div>
                <label for="ends_at" class="block text-sm mb-1">Ends (optional)</label>
                <input id="ends_at" name="ends_at" type="date"
                       value="{{ old('ends_at', $coupon?->ends_at?->format('Y-m-d')) }}" class="field">
                @error('ends_at')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $coupon->is_active ?? true)) class="border-sand-300 text-ink-900 focus:ring-0">
            Active
        </label>
    </div>

    <div class="flex gap-3 mt-6">
        <button type="submit" class="btn-primary">{{ $isEdit ? 'Save changes' : 'Create coupon' }}</button>
        <a href="{{ route('admin.promotions.index') }}" class="btn-ghost">Cancel</a>
    </div>
</form>
