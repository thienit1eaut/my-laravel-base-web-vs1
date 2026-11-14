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

class Article extends Model
{
    use HasSeoMeta;
    use HasSlug;

    protected $fillable = [
        'category_id', 'title', 'slug', 'excerpt', 'content', 'image_id',
        'meta_title', 'meta_description', 'is_active', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    // HasSlug tạo slug từ cột "name"; bài viết dùng "title"
    protected static function slugSourceColumn(): string
    {
        return 'title';
    }

    protected static function booted(): void
    {
        // Bài đổi thì các trang web đã cache phải làm mới
        static::saved(fn () => PageCache::flush());
        static::deleted(fn () => PageCache::flush());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }

    /** Mọi danh mục của bài (gồm cả danh mục chính), qua bảng trung gian article_category_article. */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ArticleCategory::class, 'article_category_article', 'article_id', 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tag', 'article_id', 'tag_id');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    /** Bài đang hiển thị ngoài web: chỉ cần trạng thái đang bật. */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Bài thuộc một trong các danh mục này (đọc từ bảng trung gian, không trùng bài).
     * Dùng IN + truy vấn con thay cho JOIN nên không phải khử trùng và tận dụng chỉ mục category_id.
     *
     * @param  list<int>  $categoryIds
     */
    public function scopeInCategories(Builder $query, array $categoryIds): Builder
    {
        return $query->whereIn(
            $query->qualifyColumn('id'),
            DB::table('article_category_article')->select('article_id')->whereIn('category_id', $categoryIds)
        );
    }

    /** Bài có gắn thẻ này (đọc từ bảng trung gian, dùng IN + truy vấn con như inCategories). */
    public function scopeWithTag(Builder $query, int $tagId): Builder
    {
        return $query->whereIn(
            $query->qualifyColumn('id'),
            DB::table('article_tag')->select('article_id')->where('tag_id', $tagId)
        );
    }

    /** Lý do bài chưa hiển thị ngoài web (null = đang công khai). */
    public function publicIssue(): ?string
    {
        return $this->is_active ? null : 'Bài viết đang ẩn';
    }

    public function getIsPublicAttribute(): bool
    {
        return $this->publicIssue() === null;
    }

    /** Đường dẫn công khai của bài viết trên web. */
    protected function url(): Attribute
    {
        return Attribute::get(fn () => route('articles.show', $this->slug));
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->image ? ($this->image->variantUrl('thumb_800') ?? $this->image->url) : null
        );
    }
}
