<?php

namespace App\Admin\Http\Middleware;

use App\Admin\Services\PermissionChecker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /** Tên hành động của route => hành động quyền */
    private const ABILITIES = [
        'index' => 'view',
        'show' => 'view',
        'create' => 'create',
        'store' => 'create',
        'edit' => 'update',
        'update' => 'update',
        'destroy' => 'delete',
    ];

    public function __construct(private PermissionChecker $checker)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $name = $request->route()?->getName();

        // Route chỉ cần đăng nhập (tổng quan, đăng xuất...)
        if ($name !== null && in_array($name, config('permissions.authenticated_only', []), true)) {
            return $next($request);
        }

        $code = $name ? $this->permissionFor($name) : null;

        // Mặc định từ chối: route chưa khai báo quyền thì bị chặn, kể cả với quản trị viên
        if ($code === null || ! $this->checker->declared($code)) {
            abort(403, 'Route chưa khai báo quyền'.($code ? " ({$code})" : '').
                '. Hãy khai báo trong config/permissions.php rồi chạy PermissionSeeder.');
        }

        if (! $this->checker->allows($request->user(), $code)) {
            abort(403);
        }

        return $next($request);
    }

    /**
     * admin.brands.store            => brands.create
     * admin.roles.permissions.edit  => roles.permissions.update
     * admin.products.images.store   => products.images.create
     * admin.orders.export           => orders.export (hành động riêng giữ nguyên)
     */
    private function permissionFor(string $routeName): ?string
    {
        $segments = explode('.', $routeName);

        // Tên route phải có dạng admin.<module>[.<chức năng con>].<hành động>
        if (array_shift($segments) !== 'admin' || count($segments) < 2) {
            return null;
        }

        $method = array_pop($segments);
        $segments[] = self::ABILITIES[$method] ?? $method;

        return implode('.', $segments);
    }
}