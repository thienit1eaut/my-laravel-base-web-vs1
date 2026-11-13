<?php

use App\Admin\Http\Controllers\AccountController;
use App\Admin\Http\Controllers\ProfileController;
use App\Admin\Http\Controllers\BrandController;
use App\Admin\Http\Controllers\DashboardController;
use App\Admin\Http\Controllers\LoginController;
use App\Admin\Http\Controllers\RoleController;
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
    Route::resource('accounts', AccountController::class)->except('show');
    Route::resource('roles', RoleController::class)->except('show');

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