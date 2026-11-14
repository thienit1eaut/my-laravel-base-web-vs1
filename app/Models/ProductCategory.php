<?php

namespace App\Models;

use App\Models\Concerns\HasCategoryTree;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSlug;
use App\Support\PageCache;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    use HasCategoryTree;
    use HasSeoMeta;
    use HasSlug;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'image_id',
        'meta_title', 'meta_description', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => PageCache::flush());
        static::deleted(fn () => PageCache::flush());
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn () => route('products.category', $this->slug));
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->image ? ($this->image->variantUrl('thumb_400') ?? $this->image->url) : null
        );
    }
}
