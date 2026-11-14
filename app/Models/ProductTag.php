<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSlug;
use App\Support\PageCache;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductTag extends Model
{
    use HasSeoMeta;
    use HasSlug;

    protected $fillable = ['name', 'slug', 'description', 'meta_title', 'meta_description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => PageCache::flush());
        static::deleted(fn () => PageCache::flush());
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_product_tag', 'tag_id', 'product_id');
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn () => route('products.tag', $this->slug));
    }
}
