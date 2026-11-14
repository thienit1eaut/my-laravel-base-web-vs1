<?php

namespace App\Admin\Http\Requests;

use App\Rules\UniqueSlug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows($this->route('product') ? 'products.update' : 'products.create');
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if (filled($this->slug)) {
            $merge['slug'] = Str::slug($this->slug);
        }

        // Ô để trống => null (không lưu chuỗi rỗng vào cột duy nhất/số)
        foreach (['sku', 'sale_price'] as $field) {
            if (! filled($this->input($field))) {
                $merge[$field] = null;
            }
        }

        // Bỏ các dòng thông số trống hoàn toàn
        if (is_array($this->specs)) {
            $merge['specs'] = collect($this->specs)
                ->filter(fn ($row) => is_array($row) && (filled($row['name'] ?? null) || filled($row['value'] ?? null)))
                ->values()
                ->all();
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'category_id' => ['required', 'integer', Rule::exists('product_categories', 'id')],
            'extra_category_ids' => ['nullable', 'array', 'max:30'],
            'extra_category_ids.*' => ['integer', 'distinct', Rule::exists('product_categories', 'id')],
            'brand_id' => ['nullable', 'integer', Rule::exists('brands', 'id')],
            'tags' => ['nullable', 'array', 'max:20'],
            'tags.*' => ['string', 'max:100', 'not_regex:/,/'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                new UniqueSlug('products', $product?->getKey()),
            ],
            'sku' => ['nullable', 'string', 'max:60', Rule::unique('products', 'sku')->ignore($product)],
            'price' => ['required', 'integer', 'min:0', 'max:9999999999'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'lt:price'],
            'stock' => ['required', 'integer', 'min:0', 'max:4000000000'],
            'short_description' => ['nullable', 'string', 'max:2000'],
            'content' => ['nullable', 'string', 'max:200000'],
            'specs' => ['nullable', 'array', 'max:50'],
            'specs.*.name' => ['required', 'string', 'max:100'],
            'specs.*.value' => ['required', 'string', 'max:500'],
            'image_id' => [
                'nullable', 'integer',
                Rule::exists('medias', 'id')->where('type', 'image')->whereNull('deleted_at'),
            ],
            'gallery_ids' => ['nullable', 'array', 'max:20'],
            'gallery_ids.*' => [
                'integer', 'distinct',
                Rule::exists('medias', 'id')->where('type', 'image')->whereNull('deleted_at'),
            ],
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'meta_key' => ['nullable', 'string', 'max:255'],
            'no_index' => ['required', 'boolean'],
            'no_follow' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Đường dẫn chỉ gồm chữ thường không dấu, số và dấu gạch ngang.',
            'category_id.required' => 'Vui lòng chọn danh mục chính.',
            'sale_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
            'specs.*.name.required' => 'Mỗi thông số cần có tên.',
            'specs.*.value.required' => 'Mỗi thông số cần có giá trị.',
        ];
    }
}
