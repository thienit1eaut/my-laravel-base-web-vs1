<?php

namespace App\Admin\Http\Requests;

use App\Enums\MediaType;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class MediaUploadRequest extends FormRequest
{
    // Tầng 3: kiểm tra quyền ngay trong request
    public function authorize(): bool
    {
        return Gate::allows('medias.create');
    }

    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'max:20'],
            'files.*' => [
                'bail',
                'file',
                // Loại tệp xác định từ nội dung thật của tệp, rồi kiểm tra dung lượng theo từng loại
                function (string $attribute, mixed $value, Closure $fail) {
                    $name = $value->getClientOriginalName();
                    $type = MediaType::fromMime($value->getMimeType());

                    if ($type === null) {
                        $fail("{$name}: định dạng không được hỗ trợ.");

                        return;
                    }

                    if ($value->getSize() > $type->maxKb() * 1024) {
                        $fail(sprintf('%s: %s tối đa %s MB.', $name, $type->label(), round($type->maxKb() / 1024)));
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'files.required' => 'Hãy chọn ít nhất một tệp.',
            'files.max' => 'Mỗi lần chỉ tải lên tối đa 20 tệp.',
            'files.*.file' => 'Tải lên thất bại (có thể tệp vượt giới hạn upload_max_filesize / post_max_size của máy chủ).',
        ];
    }
}