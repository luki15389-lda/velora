@php($isEdit = isset($category))

<form method="POST" action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="max-w-xl">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="bg-white border border-sand-300 p-6 space-y-4">
        <div>
            <label for="name" class="block text-sm mb-1">Name</label>
            <input id="name" name="name" type="text" required
                   value="{{ old('name', $category->name ?? '') }}" class="field">
            @error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="parent_id" class="block text-sm mb-1">Parent category</label>
            <select id="parent_id" name="parent_id" class="field">
                <option value="">— None (top-level department) —</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat['id'] }}" @selected(old('parent_id', $category->parent_id ?? '') == $cat['id'])>{{ $cat['label'] }}</option>
                @endforeach
            </select>
            @error('parent_id')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="description" class="block text-sm mb-1">Description (optional)</label>
            <textarea id="description" name="description" rows="3" class="field">{{ old('description', $category->description ?? '') }}</textarea>
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $category->is_active ?? true)) class="border-sand-300 text-ink-900 focus:ring-0">
            Visible on the storefront
        </label>
    </div>

    <div class="flex gap-3 mt-6">
        <button type="submit" class="btn-primary">{{ $isEdit ? 'Save changes' : 'Create category' }}</button>
        <a href="{{ route('admin.categories.index') }}" class="btn-ghost">Cancel</a>
    </div>
</form>
