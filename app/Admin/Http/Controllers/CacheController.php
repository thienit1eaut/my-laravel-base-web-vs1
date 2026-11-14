<?php

namespace App\Admin\Http\Controllers;

use App\Models\ArticleCategory;
use App\Support\PageCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Trang "Bộ nhớ đệm": xóa cache trang web (HTML bài viết/danh mục cho khách)
 * và cache dữ liệu dùng trong quản trị (cây danh mục).
 */
class CacheController extends AdminController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:cache.view', only: ['index']),
            new Middleware('can:cache.clear-web', only: ['clearWeb']),
            new Middleware('can:cache.clear-admin', only: ['clearAdmin']),
        ];
    }

    public function index(): Response
    {
        return Inertia::render('Cache/Index', [
            'pageCacheEnabled' => PageCache::enabled(),
            'title' => 'Bộ nhớ đệm',
            'breadcrumbs' => [['label' => 'Hệ thống'], ['label' => 'Bộ nhớ đệm']],
        ]);
    }

    // Làm mới mọi trang web đã cache (danh sách/chi tiết bài viết, danh mục)
    public function clearWeb(): RedirectResponse
    {
        PageCache::flush();

        return back()->with('success', 'Đã xóa cache trang web.');
    }

    // Xóa cache dữ liệu dùng trong quản trị (cây danh mục cha/con)
    public function clearAdmin(): RedirectResponse
    {
        ArticleCategory::forgetParentMap();

        return back()->with('success', 'Đã xóa cache quản trị.');
    }
}
