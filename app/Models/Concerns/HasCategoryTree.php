<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * Danh mục cha/con (cột parent_id trỏ về chính bảng). Dùng chung cho Danh mục bài viết, Danh mục sản phẩm...
 * Cây danh mục được lưu cache (khóa "<tên bảng>.parent_map") và tự xóa khi thêm/sửa/xóa danh mục.
 */
trait HasCategoryTree
{
    protected static function bootHasCategoryTree(): void
    {
        static::saved(fn () => static::forgetParentMap());
        static::deleted(fn () => static::forgetParentMap());
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    /**
     * Id của mọi danh mục con, cháu... (không gồm chính nó). Dùng để chặn chọn nhầm danh mục con làm cha
     * và để lấy dữ liệu của cả nhánh danh mục.
     *
     * @return list<int>
     */
    public function descendantIds(): array
    {
        $found = [];
        $queue = [(int) $this->getKey()];
        $parents = static::parentMap();

        while ($queue) {
            $current = array_shift($queue);

            foreach ($parents as $id => $parentId) {
                if ($parentId !== null && (int) $parentId === $current && ! in_array((int) $id, $found, true)) {
                    $found[] = (int) $id;
                    $queue[] = (int) $id;
                }
            }
        }

        return $found;
    }

    /**
     * Id các danh mục cha, ông... của một danh mục (không gồm chính nó), từ gần đến xa.
     *
     * @return list<int>
     */
    public static function ancestorIds(int $id): array
    {
        $parents = static::parentMap();
        $found = [];

        while (($parentId = $parents[$id] ?? null) !== null && ! in_array((int) $parentId, $found, true)) {
            $found[] = $id = (int) $parentId;
        }

        return $found;
    }

    /**
     * Bản đồ [id => parent_id] của mọi danh mục, lưu cache vì bảng nhỏ mà được gọi ở nhiều trang.
     *
     * @return array<int, int|null>
     */
    public static function parentMap(): array
    {
        return Cache::rememberForever(static::parentMapCacheKey(), fn () => static::query()->pluck('parent_id', 'id')->all());
    }

    public static function forgetParentMap(): void
    {
        Cache::forget(static::parentMapCacheKey());
    }

    protected static function parentMapCacheKey(): string
    {
        return (new static)->getTable().'.parent_map';
    }

    /**
     * Danh sách chọn danh mục: tên kèm đường dẫn cha (vd "Tin tức › Báo chí"), sắp theo tên.
     *
     * @param  list<int>  $excludeIds
     * @return list<array{id:int,name:string}>
     */
    public static function pathOptions(array $excludeIds = []): array
    {
        $all = static::get(['id', 'name', 'parent_id'])->keyBy('id');

        $label = function ($category) use ($all, &$label): string {
            $parent = $category->parent_id ? $all->get($category->parent_id) : null;

            return $parent ? $label($parent).' › '.$category->name : $category->name;
        };

        return $all
            ->reject(fn ($category) => in_array($category->id, $excludeIds, true))
            ->map(fn ($category) => ['id' => $category->id, 'name' => $label($category)])
            ->sortBy('name')
            ->values()
            ->all();
    }
}
