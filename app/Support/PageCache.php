<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Cache nguyên trang HTML của phần web cho khách (xem CachePublicPage).
 * Mỗi lần dữ liệu đổi (bài viết, danh mục) chỉ cần tăng "phiên bản" là mọi trang cũ tự hết hiệu lực.
 */
class PageCache
{
    private const VERSION_KEY = 'web.page_cache_version';

    public static function enabled(): bool
    {
        return (bool) config('page-cache.enabled');
    }

    public static function key(string $uri): string
    {
        return 'web.page.'.static::version().'.'.sha1($uri);
    }

    public static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    /** Làm mới toàn bộ trang đã cache. */
    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, static::version() + 1);
    }
}
