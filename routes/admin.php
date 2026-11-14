<?php

use App\Admin\Http\Controllers\AccountController;
use App\Admin\Http\Controllers\ProfileController;
use App\Admin\Http\Controllers\ArticleCategoryController;
use App\Admin\Http\Controllers\ArticleController;
use App\Admin\Http\Controllers\LanguageController;
use App\Admin\Http\Controllers\ProductCategoryController;
use App\Admin\Http\Controllers\ProductController;
use App\Admin\Http\Controllers\ProductTagController;
use App\Admin\Http\Controllers\BrandController;
use App\Admin\Http\Controllers\CacheController;
use App\Admin\Http\Controllers\DashboardController;
use App\Admin\Http\Controllers\LoginController;
use App\Admin\Http\Controllers\RoleController;
use App\Admin\Http\Controllers\TagController;
use App\Admin\Http\Controllers\RolePermissionController;
use App\Admin\Http\Controllers\MediaController;
use App\Admin\Http\Controllers\MediaTrashController;
use App\Admin\Http\Middleware\CheckPermission;
use App\Admin\Http\Middleware\EnsureAuthenticated;
use Illuminate\Support\Facades\Route;

// Tiền tố "admin" và tên "admin." đã được AdminServiceProvider gắn sẵn.

// Chưa cần đăng nhập
Route::get('login', [LoginController::class, 'create'])->name('login');          // /admin/login
Route::post('login', [LoginController::class, 'store'])->name('login.store');

// Phải đăng nhập, và có quyền tương ứng với tên route (xem CheckPermission)
Route::middleware([EnsureAuthenticated::class, CheckPermission::class])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');   // /admin
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');  // /admin/logout

    // Hồ sơ cá nhân (chỉ cần đăng nhập, không cần quyền module)
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('brands', BrandController::class)->except('show');
    Route::resource('article-categories', ArticleCategoryController::class)->except('show');
    Route::resource('articles', ArticleController::class)->except('show');
    Route::resource('tags', TagController::class)->except('show');
    Route::resource('product-categories', ProductCategoryController::class)->except('show');
    Route::resource('products', ProductController::class)->except('show');
    Route::resource('product-tags', ProductTagController::class)->except('show');
    Route::resource('accounts', AccountController::class)->except('show');
    Route::resource('roles', RoleController::class)->except('show');

    // Bộ nhớ đệm: xem trang, xóa cache web, xóa cache quản trị (quyền cache.view / cache.clear-web / cache.clear-admin)
    Route::get('cache', [CacheController::class, 'index'])->name('cache.index');
    Route::post('cache/clear-web', [CacheController::class, 'clearWeb'])->name('cache.clear-web');
    Route::post('cache/clear-admin', [CacheController::class, 'clearAdmin'])->name('cache.clear-admin');

    // Ngôn ngữ của website (không có xóa; quyền languages.view/create/update/default)
    Route::resource('languages', LanguageController::class)->except(['show', 'destroy']);
    Route::patch('languages/{language}/quick', [LanguageController::class, 'quick'])->name('languages.quick');
    Route::patch('languages/{language}/default', [LanguageController::class, 'makeDefault'])->name('languages.default');

    // Sửa nhanh ngay trên trang danh sách (logic chung ở AdminController::quickUpdate)
    Route::patch('brands/{brand}/quick', [BrandController::class, 'quick'])->name('brands.quick');
    Route::patch('article-categories/{article_category}/quick', [ArticleCategoryController::class, 'quick'])->name('article-categories.quick');
    Route::patch('articles/{article}/quick', [ArticleController::class, 'quick'])->name('articles.quick');
    Route::patch('tags/{tag}/quick', [TagController::class, 'quick'])->name('tags.quick');
    Route::patch('product-categories/{product_category}/quick', [ProductCategoryController::class, 'quick'])->name('product-categories.quick');
    Route::patch('products/{product}/quick', [ProductController::class, 'quick'])->name('products.quick');
    Route::patch('product-tags/{product_tag}/quick', [ProductTagController::class, 'quick'])->name('product-tags.quick');
    Route::patch('accounts/{account}/quick', [AccountController::class, 'quick'])->name('accounts.quick');

    // Thùng rác của thư viện (xem, khôi phục, xóa vĩnh viễn, dọn sạch)
    Route::get('medias/trash', [MediaTrashController::class, 'index'])->name('medias.trash.index');
    Route::post('medias/trash/restore', [MediaTrashController::class, 'restore'])->name('medias.trash.restore');
    Route::delete('medias/trash/empty', [MediaTrashController::class, 'empty'])->name('medias.trash.empty');
    Route::delete('medias/trash', [MediaTrashController::class, 'destroy'])->name('medias.trash.destroy');

    // Thư viện ảnh: 1 trang duy nhất, xóa (một hoặc nhiều ảnh) dùng chung route destroy
    Route::resource('medias', MediaController::class)
        ->only(['index', 'store', 'update'])
        ->parameters(['medias' => 'media']);
    Route::delete('medias', [MediaController::class, 'destroy'])->name('medias.destroy');

    // Phân quyền cho vai trò (tên route => quyền roles.permissions.update)
    Route::get('roles/{role}/permissions', [RolePermissionController::class, 'edit'])->name('roles.permissions.edit');
    Route::put('roles/{role}/permissions', [RolePermissionController::class, 'update'])->name('roles.permissions.update');
});