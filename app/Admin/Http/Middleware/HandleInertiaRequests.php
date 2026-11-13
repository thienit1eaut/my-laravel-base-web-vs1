<?php

namespace App\Admin\Http\Middleware;

use App\Admin\Services\PermissionChecker;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Root template của phần quản trị: resources/views/admin.blade.php
     */
    protected $rootView = 'admin';

    /**
     * Dữ liệu dùng chung cho mọi trang admin (đọc ở Vue qua usePage().props).
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appName' => config('app.name'),
            'auth' => [
                // Chỉ chia sẻ các trường an toàn, không bao giờ gửi password
                'user' => $request->user() ? [
                    ...$request->user()->only(['id', 'name', 'username', 'email']),
                    'image_url' => $request->user()->image_url,
                ] : null,
                // Chỉ để ẩn/hiện giao diện; việc chặn thật sự nằm ở phía server
                'permissions' => fn () => app(PermissionChecker::class)->codesFor($request->user()),
            ],
            // Thông báo một lần sau khi thêm/sửa/xóa
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}