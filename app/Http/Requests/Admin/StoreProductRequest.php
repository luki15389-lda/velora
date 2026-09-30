<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $product
            ? $this->user()->can('update', $product)
            : $this->user()->can('create', Product::class);
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:180'],
            'sku' => ['required', 'string', 'max:40', Rule::unique('products', 'sku')->ignore($productId)],
            'description' => ['nullable', 'string', 'max:5000'],
            'material' => ['nullable', 'string', 'max:120'],
            'care_instructions' => ['nullable', 'string', 'max:500'],
            'gender' => ['required', Rule::in(['men', 'women', 'unisex'])],
            'size_group' => ['required', Rule::in(['apparel', 'bottoms', 'shoes', 'bra', 'onesize'])],
            'price' => ['required', 'integer', 'min:0'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'lt:price'],
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
            'featured' => ['nullable', 'boolean'],

            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'image_color_hex' => ['nullable', 'array'],
            'image_color_hex.*' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'image_color_name' => ['nullable', 'array'],
            'image_color_name.*' => ['nullable', 'string', 'max:60'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['nullable', 'integer', 'exists:product_images,id'],
            'primary_image_id' => ['nullable', 'string'],

            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.color_hex' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'variants.*.size_id' => ['nullable', 'exists:sizes,id'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            'variants.*.price' => ['nullable', 'integer', 'min:0'],
            'variants.*.sale_price' => ['nullable', 'integer', 'min:0'],
            'variants.*.remove' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'sale_price.lt' => 'The sale price must be lower than the regular price.',
            'variants.min' => 'Add at least one colour/size combination with its stock.',
            'image_color_hex.*.regex' => 'That colour swatch looks invalid — try picking it again.',
            'variants.*.color_hex.regex' => 'That colour swatch looks invalid — try picking it again.',
        ];
    }

    /** Generates a slug candidate; the controller de-dupes it against the DB. */
    public function slugCandidate(): string
    {
        return Str::slug($this->string('name'));
    }
}