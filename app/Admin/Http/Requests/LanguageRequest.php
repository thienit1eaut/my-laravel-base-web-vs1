<?php

namespace App\Admin\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class LanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows($this->route('language') ? 'languages.update' : 'languages.create');
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['code', 'prefix'] as $field) {
            if (is_string($this->input($field))) {
                $merge[$field] = strtolower(trim($this->input($field)));
            }
        }

        if (! filled($this->input('flag'))) {
            $merge['flag'] = null;
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        $language = $this->route('language');

        $rules = [
            'prefix' => [
                'required', 'string', 'regex:/^[a-z]{2,5}$/',
                Rule::notIn(Language::RESERVED_PREFIXES),
                Rule::unique('languages', 'prefix')->ignore($language),
            ],
            'name' => ['required', 'string', 'max:60'],
            'flag' => ['nullable', Rule::in(array_keys(Language::FLAGS))],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ];

        // Mã ngôn ngữ đã gắn với các bản dịch nên chỉ nhập khi thêm mới, không đổi khi sửa
        if (! $language) {
            $rules['code'] = ['required', 'string', 'regex:/^[a-z]{2,3}(-[a-z0-9]{2,4})?$/', Rule::unique('languages', 'code')];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Mã ngôn ngữ gồm 2-3 chữ cái thường, ví dụ vi, en, ja (có thể thêm vùng như zh-cn).',
            'prefix.regex' => 'Tiền tố gồm 2-5 chữ cái thường không dấu, ví dụ vn, en, jp.',
            'prefix.not_in' => 'Tiền tố này trùng với đường dẫn của hệ thống.',
        ];
    }
}
