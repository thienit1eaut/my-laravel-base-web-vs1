<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSlug;
use App\Support\PageCache;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
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
        // Thẻ đổi thì các trang web đã cache phải làm mới
        static::saved(fn () => PageCache::flush());
        static::deleted(fn () => PageCache::flush());
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_tag', 'tag_id', 'article_id');
    }

    /** Đường dẫn công khai của thẻ trên web. */
    protected function url(): Attribute
    {
        return Attribute::get(fn () => route('articles.tag', $this->slug));
    }
}
