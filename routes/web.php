<?php

use App\Web\Http\Controllers\ArticleController;
use App\Web\Http\Controllers\HomeController;
use App\Web\Http\Controllers\ProductController;
use App\Web\Http\Middleware\CachePublicPage;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Trang công khai của bài viết (cache nguyên trang khi bật PAGE_CACHE_ENABLED, xem config/page-cache.php)
Route::middleware(CachePublicPage::class)->group(function () {
    // Bài viết: /bai-viet, /bai-viet/{slug}; danh mục: /danh-muc-bai-viet/{slug}
    Route::get('bai-viet', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('danh-muc-bai-viet/{slug}', [ArticleController::class, 'category'])->name('articles.category');
    Route::get('the-bai-viet/{slug}', [ArticleController::class, 'tag'])->name('articles.tag');

    // Sản phẩm: /san-pham, /san-pham/{slug}; danh mục: /danh-muc-san-pham/{slug}; thẻ: /the-san-pham/{slug}
    Route::get('san-pham', [ProductController::class, 'index'])->name('products.index');
    Route::get('danh-muc-san-pham/{slug}', [ProductController::class, 'category'])->name('products.category');
    Route::get('the-san-pham/{slug}', [ProductController::class, 'tag'])->name('products.tag');
    Route::get('san-pham/{slug}', [ProductController::class, 'show'])->name('products.show');
});

// Link danh mục kiểu cũ: chuyển hướng vĩnh viễn (301) sang link mới
Route::redirect('bai-viet/danh-muc/{slug}', '/danh-muc-bai-viet/{slug}', 301);

Route::get('bai-viet/{slug}', [ArticleController::class, 'show'])->name('articles.show')
    ->middleware(CachePublicPage::class);
