<?php

namespace App\Admin\Http\Requests;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows($this->route('brand') ? 'brands.update' : 'brands.create');
    }

    public function rules(): array
    {
        // Khi sửa: là model Brand đang sửa (để bỏ qua chính nó khi kiểm tra trùng). Khi thêm: null.
        $brand = $this->route('brand');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('brands', 'name')->ignore($brand)],
            'slug' => [
                'nullable', 'string', 'max:160',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('brands', 'slug')->ignore($brand),
            ],
            'image_id' => [
                'nullable', 'integer',
                Rule::exists('medias', 'id')->where('type', 'image')->whereNull('deleted_at'),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
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