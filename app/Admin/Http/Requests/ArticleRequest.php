<?php

namespace App\Admin\Http\Requests;

use App\Rules\UniqueSlug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows($this->route('article') ? 'articles.update' : 'articles.create');
    }

    protected function prepareForValidation(): void
    {
        if (filled($this->slug)) {
            $this->merge(['slug' => Str::slug($this->slug)]);
        }
    }

    public function rules(): array
    {
        $article = $this->route('article');

        return [
            'category_id' => ['required', 'integer', Rule::exists('article_categories', 'id')],
            // Danh mục phụ (ngoài danh mục chính); danh mục chính tự được thêm vào khi lưu
            'extra_category_ids' => ['nullable', 'array', 'max:30'],
            'extra_category_ids.*' => ['integer', 'distinct', Rule::exists('article_categories', 'id')],
            // Thẻ: danh sách tên; tên chưa có thì tự tạo thẻ mới (nếu có quyền tags.create)
            'tags' => ['nullable', 'array', 'max:20'],
            'tags.*' => ['string', 'max:100', 'not_regex:/,/'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                new UniqueSlug('articles', $article?->getKey()),
            ],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string', 'max:200000'],
            'image_id' => [
                'nullable', 'integer',
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
            'category_id.required' => 'Vui lòng chọn danh mục.',
        ];
    }
}
