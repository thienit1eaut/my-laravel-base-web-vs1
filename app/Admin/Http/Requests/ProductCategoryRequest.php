<?php

namespace App\Admin\Http\Requests;

use App\Rules\UniqueSlug;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows($this->route('product_category') ? 'product-categories.update' : 'product-categories.create');
    }

    protected function prepareForValidation(): void
    {
        if (filled($this->slug)) {
            $this->merge(['slug' => Str::slug($this->slug)]);
        }
    }

    public function rules(): array
    {
        $category = $this->route('product_category');

        return [
            'parent_id' => [
                'nullable', 'integer', Rule::exists('product_categories', 'id'),
                // Không chọn chính nó hoặc danh mục con của nó làm cha (tránh vòng lặp)
                function (string $attribute, mixed $value, Closure $fail) use ($category) {
                    if ($category && ((int) $value === $category->id || in_array((int) $value, $category->descendantIds(), true))) {
                        $fail('Danh mục cha không hợp lệ: không thể chọn chính nó hoặc danh mục con của nó.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:150', Rule::unique('product_categories', 'name')->ignore($category)],
            'slug' => [
                'nullable', 'string', 'max:160',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                new UniqueSlug('product_categories', $category?->getKey()),
            ],
            'image_id' => [
                'nullable', 'integer',
                Rule::exists('medias', 'id')->where('type', 'image')->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'meta_key' => ['nullable', 'string', 'max:255'],
            'no_index' => ['required', 'boolean'],
            'no_follow' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Đường dẫn chỉ gồm chữ thường không dấu, số và dấu gạch ngang.',
        ];
    }
}
