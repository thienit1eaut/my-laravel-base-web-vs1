<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSlug;
use App\Support\PageCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasSeoMeta;
    use HasSlug;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'sku', 'price', 'sale_price', 'stock',
        'short_description', 'content', 'specs', 'image_id',
        'meta_title', 'meta_description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'specs' => 'array',
            'price' => 'integer',
            'sale_price' => 'integer',
            'stock' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => PageCache::flush());
        static::deleted(fn () => PageCache::flush());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'product_category_product', 'product_id', 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ProductTag::class, 'product_product_tag', 'product_id', 'tag_id');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    /** Thư viện ảnh, theo thứ tự hiển thị. */
    public function gallery(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'product_media', 'product_id', 'media_id')
            ->withPivot('sort_order')
            ->orderBy('product_media.sort_order');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @param  list<int>  $categoryIds */
    public function scopeInCategories(Builder $query, array $categoryIds): Builder
    {
        return $query->whereIn(
            $query->qualifyColumn('id'),
            DB::table('product_category_product')->select('product_id')->whereIn('category_id', $categoryIds)
        );
    }

    public function scopeWithTag(Builder $query, int $tagId): Builder
    {
        return $query->whereIn(
            $query->qualifyColumn('id'),
            DB::table('product_product_tag')->select('product_id')->where('tag_id', $tagId)
        );
    }

    public function publicIssue(): ?string
    {
        return $this->is_active ? null : 'Sản phẩm đang ẩn';
    }

    public function getIsPublicAttribute(): bool
    {
        return $this->publicIssue() === null;
    }

    /** Giá đang bán: giá khuyến mãi nếu có và thấp hơn giá gốc. */
    public function getFinalPriceAttribute(): int
    {
        return $this->sale_price !== null && $this->sale_price < $this->price ? $this->sale_price : $this->price;
    }

    public function getOnSaleAttribute(): bool
    {
        return $this->final_price < $this->price;
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn () => route('products.show', $this->slug));
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->image ? ($this->image->variantUrl('thumb_800') ?? $this->image->url) : null
        );
    }
}
