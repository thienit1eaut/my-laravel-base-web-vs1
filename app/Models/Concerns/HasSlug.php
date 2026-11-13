<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Tự tạo slug duy nhất từ cột "name" khi slug còn trống.
 * Dùng chung cho Thương hiệu, Danh mục, Sản phẩm, Tin tức...
 */
trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            if (blank($model->slug)) {
                $model->slug = static::uniqueSlug($model->name, $model->getKey());
            }
        });
    }

    protected static function uniqueSlug(string $name, mixed $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'muc';
        $slug = $base;
        $i = 2;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}