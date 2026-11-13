<?php

namespace App\Admin\Services;

use App\Enums\MediaType;
use App\Models\Media;

class MediaPresenter
{
    public static function make(Media $media): array
    {
        $isImage = $media->type === MediaType::Image;

        // Danh sách URL để sao chép: gốc, WebP, thumbnail
        $urls = [['label' => 'Tệp gốc', 'url' => $media->url]];

        foreach (['webp_path' => 'Bản WebP', 'thumb_800' => 'Thumbnail 800', 'thumb_400' => 'Thumbnail 400'] as $column => $label) {
            if ($url = $media->variantUrl($column)) {
                $urls[] = ['label' => $label, 'url' => $url];
            }
        }

        return [
            'id' => $media->id,
            'name' => $media->name,
            'title' => $media->title,
            'alt' => $media->alt,
            'type' => $media->type->value,
            'type_label' => $media->type->label(),
            'extension' => $media->extension ?: pathinfo($media->path, PATHINFO_EXTENSION),
            'url' => $media->url,
            // Ảnh nhỏ cho lưới/bảng: ưu tiên thumbnail 400, chưa có thì dùng ảnh gốc
            'thumb_url' => $isImage ? ($media->variantUrl('thumb_400') ?? $media->url) : null,
            'urls' => $urls,
            'mime' => $media->mime,
            'size_label' => \Illuminate\Support\Number::fileSize($media->size, precision: 1),
            'width' => $media->width,
            'height' => $media->height,
            'uploaded_by' => $media->uploader?->name,
            'created_at' => $media->created_at->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y'),
            'deleted_at' => $media->deleted_at?->timezone('Asia/Ho_Chi_Minh')->format('H:i d/m/Y'),
        ];
    }
}