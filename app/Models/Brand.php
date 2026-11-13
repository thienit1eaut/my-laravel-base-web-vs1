<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Brand extends Model
{
    use HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_id',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Ảnh trong thùng rác tự bị loại khỏi quan hệ này (Media dùng xóa mềm)
    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    /** Dùng: $brand->image_url (thumbnail 400 nếu có, không thì ảnh gốc; null nếu chưa có ảnh). */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->image ? ($this->image->variantUrl('thumb_400') ?? $this->image->url) : null
        );
    }
}