<?php

namespace App\Web\Http\Middleware;

use App\Support\PageCache;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lưu cache HTML của các trang công khai (danh sách/chi tiết bài viết) để lần sau trả về ngay,
 * không phải truy vấn database và dựng lại giao diện. Chỉ cache khi bật PAGE_CACHE_ENABLED=true.
 * Chỉ cache phản hồi 200 của yêu cầu GET; trang lỗi 404 không bao giờ được cache.
 */
class CachePublicPage
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! PageCache::enabled() || ! $request->isMethod('GET')) {
            return $next($request);
        }

        $key = PageCache::key($request->getRequestUri());

        if (is_string($html = Cache::get($key))) {
            return response($html)->header('X-Page-Cache', 'HIT');
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200) {
            Cache::put($key, $response->getContent(), now()->addSeconds((int) config('page-cache.ttl')));
            $response->headers->set('X-Page-Cache', 'MISS');
        }

        return $response;
    }
}
