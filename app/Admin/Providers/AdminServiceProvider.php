<?php

namespace App\Admin\Providers;

use App\Admin\Http\Middleware\HandleInertiaRequests;
use App\Admin\Services\PermissionChecker;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Một đối tượng cho mỗi request: quyền chỉ được nạp từ database một lần
        $this->app->scoped(PermissionChecker::class);
    }

    public function boot(): void
    {
        // Mọi kiểm tra dạng module.action (can:brands.view, Gate::authorize('products.images.create')...)
        // đều đi qua PermissionChecker. Ability không có dấu chấm (policy) để Laravel xử lý như thường.
        Gate::before(function (User $user, string $ability) {
            if (! str_contains($ability, '.')) {
                return null;
            }

            return app(PermissionChecker::class)->allows($user, $ability);
        });

        Route::middleware(['web', HandleInertiaRequests::class])
            ->prefix('admin')
            ->name('admin.')
            ->group(function () {
                $this->loadRoutesFrom(base_path('routes/admin.php'));
            });
    }
}