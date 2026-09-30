@php
    $isEdit = isset($product);

    // On a validation failure, old('variants') has what the admin just typed
    // (including their in-progress stock edit) — show that back to them
    // instead of silently reloading the database's old values, which used to
    // look exactly like "I saved and nothing happened."
    $oldVariants = old('variants');

    $initialRows = $oldVariants
        ? collect($oldVariants)->values()
        : ($isEdit
            ? $product->variants->map(fn ($v) => [
                'id' => $v->id,
                'color_hex' => $v->color?->hex ?? '', 'color_name' => $v->color?->name ?? '',
                'size_id' => $v->size_id ?? '',
                'stock' => $v->stock, 'price' => $v->price ?? '', 'sale_price' => $v->sale_price ?? '',
            ])->values()
            : collect());

    $initialImages = $isEdit
        ? $product->images->sortBy('position')->map(fn ($i) => [
            'id' => $i->id, 'url' => $i->url,
            'color_hex' => $i->color?->hex ?? '', 'color_name' => $i->color?->name ?? '',
            'is_primary' => $i->is_primary,
        ])->values()
        : collect();
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}"
      enctype="multipart/form-data"
      x-data="productForm({
          initialRows: @js($initialRows),
          sizesByGroup: @js($sizeGroups->mapWithKeys(fn ($sizes, $group) => [$group => $sizes->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values()])),
          initialSizeGroup: @js(old('size_group', $product->size_group ?? 'apparel')),
          initialImages: @js($initialImages),
      })">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Main column --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Basic info --}}
            <section class="bg-white border border-sand-300 p-6">
                <h2 class="font-medium mb-5">Basic information</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm mb-1">Product name</label>
                        <input id="name" name="name" type="text" required
                               value="{{ old('name', $product->name ?? '') }}" class="field">
                        @error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="sku" class="block text-sm mb-1">SKU</label>
                        <input id="sku" name="sku" type="text" required
                               value="{{ old('sku', $product->sku ?? '') }}" class="field uppercase">
                        @error('sku')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm mb-1">Category</label>
                        <select id="category_id" name="category_id" required class="field">
                            <option value="">Select a category</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id ?? null) == $cat->id)>
                                    {{ $cat->parent ? $cat->parent->name.' / ' : '' }}{{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="gender" class="block text-sm mb-1">Gender</label>
                        <select id="gender" name="gender" required class="field">
                            @foreach (['men' => "Men's", 'women' => "Women's", 'unisex' => 'Unisex'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('gender', $product->gender->value ?? 'unisex') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="size_group" class="block text-sm mb-1">Size type</label>
                        <select id="size_group" name="size_group" x-model="sizeGroup" class="field">
                            @foreach (['apparel' => 'Apparel (XS–XXL)', 'bottoms' => 'Bottoms (waist size)', 'shoes' => 'Shoes (EU size)', 'bra' => 'Bra (band/cup)', 'onesize' => 'One size'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-ink-400">Controls which sizes appear in the generator below.</p>
                    </div>

                    <div>
                        <label for="material" class="block text-sm mb-1">Material</label>
                        <input id="material" name="material" type="text"
                               value="{{ old('material', $product->material ?? '') }}" class="field">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="block text-sm mb-1">Description</label>
                        <textarea id="description" name="description" rows="4" class="field">{{ old('description', $product->description ?? '') }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="care_instructions" class="block text-sm mb-1">Care instructions</label>
                        <textarea id="care_instructions" name="care_instructions" rows="2" class="field">{{ old('care_instructions', $product->care_instructions ?? '') }}</textarea>
                    </div>
                </div>
            </section>

            {{-- Images --}}
            <section class="bg-white border border-sand-300 p-6">
                <h2 class="font-medium mb-1">Photos</h2>
                <p class="text-xs text-ink-400 mb-5">Tag each photo with a colour so it swaps in when a shopper picks that colourway.</p>

                <template x-if="images.length">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                        <template x-for="image in images" :key="image.key">
                            <div x-show="!image.remove" class="space-y-2">
                                <div class="aspect-[4/5] bg-sand-200 overflow-hidden relative">
                                    <img :src="image.url" class="h-full w-full object-cover" alt="">
                                    <button type="button" @click="removeExistingImage(image.key)"
                                            class="absolute top-1 right-1 h-6 w-6 bg-white/90 text-xs hover:bg-white">✕</button>
                                </div>
                                <input type="hidden" :name="`remove_image_ids[${image.key}]`" :value="image.remove ? image.id : ''">
                                <div class="flex items-center gap-1.5">
                                    <input type="color" :name="`image_color_hex[${image.key}]`" x-model="image.color_hex"
                                           title="Pick the exact colour from the photo (use the eyedropper)"
                                           class="h-7 w-8 shrink-0 border border-sand-300 p-0 bg-transparent">
                                    <input type="text" :name="`image_color_name[${image.key}]`" x-model="image.color_name"
                                           placeholder="No colour tag" class="field py-1 text-xs flex-1">
                                </div>
                                <label class="flex items-center gap-1.5 text-xs">
                                    <input type="radio" name="primary_image_id" :value="image.id" x-model="primaryImageId">
                                    Cover photo
                                </label>
                            </div>
                        </template>
                    </div>
                </template>

                <div class="space-y-3">
                    <template x-for="img in newImages" :key="img.key">
                        <div class="flex items-center gap-3 border border-sand-300 p-3">
                            <input type="file" :name="`images[]`" accept="image/*" class="text-xs flex-1">
                            <input type="color" :name="`image_color_hex[]`" x-model="img.color_hex"
                                   title="Pick the exact colour from the photo (use the eyedropper)"
                                   class="h-7 w-8 shrink-0 border border-sand-300 p-0 bg-transparent">
                            <input type="text" :name="`image_color_name[]`" x-model="img.color_name"
                                   placeholder="No colour tag" class="field py-1.5 text-xs w-32">
                            <button type="button" @click="removeImageSlot(img.key)" class="text-xs text-ink-400 hover:text-red-700">Remove</button>
                        </div>
                    </template>

                    <button type="button" @click="addImageSlot" class="btn-outline py-2 px-4 text-sm">+ Add photo</button>
                </div>
                @error('images.*')<p class="mt-2 text-xs text-red-700">{{ $message }}</p>@enderror
            </section>

            {{-- Variants --}}
            <section class="bg-white border border-sand-300 p-6">
                <h2 class="font-medium mb-1">Colours, sizes and stock</h2>
                <p class="text-xs text-ink-400 mb-5">
                    Every colour/size combination gets its own stock count. The Colour choices below come straight
                    from the colours you tagged on the photos above — tag a photo with a colour first, then add a row for it here.
                </p>

                <div x-show="visibleRowCount === 0" class="text-sm text-ink-400 py-6 text-center border border-dashed border-sand-300">
                    No variants yet. Use "+ Add single row" below.
                </div>

                <div x-show="visibleRowCount > 0" class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-xs text-ink-400">
                            <tr>
                                <th class="py-2 pr-3">Colour</th>
                                <th class="py-2 pr-3">Size</th>
                                <th class="py-2 pr-3">Stock</th>
                                <th class="py-2 pr-3">Price override</th>
                                <th class="py-2 pr-3">Sale price override</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="row in rows" :key="row.key">
                                <tr x-show="!row.remove" class="border-t border-sand-200">
                                    <td class="py-2 pr-3">
                                        <input type="hidden" :name="`variants[${row.key}][id]`" :value="row.id ?? ''">
                                        <select :name="`variants[${row.key}][color_hex]`" x-model="row.color_hex" class="field py-1.5">
                                            <option value="">—</option>
                                            <template x-for="color in taggedColors" :key="color.hex">
                                                <option :value="color.hex" x-text="color.name"></option>
                                            </template>
                                        </select>
                                        <p x-show="taggedColors.length === 0" class="mt-1 text-[11px] text-ink-400">Tag a photo with a colour above first.</p>
                                    </td>
                                    <td class="py-2 pr-3">
                                        <select :name="`variants[${row.key}][size_id]`" x-model="row.size_id" class="field py-1.5">
                                            <option value="">—</option>
                                            <template x-for="size in availableSizes" :key="size.id">
                                                <option :value="size.id" x-text="size.name"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-2 pr-3">
                                        <input type="number" min="0" :name="`variants[${row.key}][stock]`" x-model.number="row.stock" class="field py-1.5 w-20">
                                    </td>
                                    <td class="py-2 pr-3">
                                        <input type="number" min="0" :name="`variants[${row.key}][price]`" x-model="row.price" placeholder="—" class="field py-1.5 w-28">
                                    </td>
                                    <td class="py-2 pr-3">
                                        <input type="number" min="0" :name="`variants[${row.key}][sale_price]`" x-model="row.sale_price" placeholder="—" class="field py-1.5 w-28">
                                    </td>
                                    <td class="py-2">
                                        <input type="hidden" :name="`variants[${row.key}][remove]`" :value="row.remove ? 1 : 0">
                                        <button type="button" @click="removeRow(row.key)" class="text-xs text-ink-400 hover:text-red-700">Remove</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <button type="button" @click="addRow" class="btn-outline py-2 px-4 text-sm mt-4">+ Add single row</button>
                @error('variants')<p class="mt-3 text-xs text-red-700">{{ $message }}</p>@enderror
            </section>
        </div>

        {{-- Side column --}}
        <div class="space-y-6">
            <section class="bg-white border border-sand-300 p-6">
                <h2 class="font-medium mb-5">Pricing & status</h2>

                <div class="space-y-4">
                    <div>
                        <label for="price" class="block text-sm mb-1">Price (Rp)</label>
                        <input id="price" name="price" type="number" min="0" required
                               value="{{ old('price', $product->price ?? '') }}" class="field">
                        @error('price')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="sale_price" class="block text-sm mb-1">Sale price (optional)</label>
                        <input id="sale_price" name="sale_price" type="number" min="0"
                               value="{{ old('sale_price', $product->sale_price ?? '') }}" class="field">
                        @error('sale_price')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm mb-1">Status</label>
                        <select id="status" name="status" class="field">
                            @foreach (['draft' => 'Draft', 'active' => 'Active', 'archived' => 'Archived'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $product->status->value ?? 'draft') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-ink-400">Only "Active" products show on the storefront.</p>
                    </div>

                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="featured" value="1"
                               @checked(old('featured', $product->featured ?? false)) class="border-sand-300 text-ink-900 focus:ring-0">
                        Feature on homepage
                    </label>
                </div>
            </section>

            <div class="flex flex-col gap-3">
                <button type="submit" class="btn-primary w-full">{{ $isEdit ? 'Save changes' : 'Create product' }}</button>
                <a href="{{ route('admin.products.index') }}" class="btn-ghost w-full text-center">Cancel</a>
            </div>

            @if ($isEdit)
                <div class="bg-white border border-sand-300 p-6 text-xs text-ink-400 space-y-2">
                    <p>Created {{ $product->created_at->translatedFormat('d M Y') }}</p>
                    <p>{{ $product->sold_count }} sold · {{ number_format($product->rating_average, 1) }}★ ({{ $product->rating_count }})</p>
                    <p>Total stock across variants: {{ $product->total_stock }}</p>
                </div>
            @endif
        </div>
    </div>
</form>

{{--
    Deliberately OUTSIDE the <form> above. A <form> nested inside another
    <form> is invalid HTML — the browser closes the outer form early, right
    where this one used to sit, which merged this form's @method('DELETE')
    field into the same form as the "Save changes" button. Since duplicate
    field names keep the last value on submit, clicking "Save changes" was
    silently being sent as a DELETE request, removing the product instead of
    saving the edits.
--}}
@if ($isEdit)
    <div class="grid gap-6 lg:grid-cols-3 mt-6">
        <div class="hidden lg:block lg:col-span-2"></div>
        <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
              onsubmit="return confirm('Delete this product? It will be hidden everywhere, but past orders keep their record.')">
            @csrf @method('DELETE')
            <button type="submit" class="w-full text-center text-sm text-red-700 hover:underline py-2">Delete product</button>
        </form>
    </div>
@endif