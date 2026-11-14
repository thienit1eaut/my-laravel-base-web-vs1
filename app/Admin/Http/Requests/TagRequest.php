<?php

namespace App\Admin\Http\Requests;

use App\Rules\UniqueSlug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows($this->route('tag') ? 'tags.update' : 'tags.create');
    }

    protected function prepareForValidation(): void
    {
        if (filled($this->slug)) {
            $this->merge(['slug' => Str::slug($this->slug)]);
        }
    }

    public function rules(): array
    {
        $tag = $this->route('tag');

        return [
            'name' => ['required', 'string', 'max:100', 'not_regex:/,/', Rule::unique('tags', 'name')->ignore($tag)],
            'slug' => [
                'nullable', 'string', 'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                new UniqueSlug('tags', $tag?->getKey()),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
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
            'name.not_regex' => 'Tên thẻ không được chứa dấu phẩy.',
        ];
    }
}
