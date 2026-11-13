<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Trang lỗi (403, 404, 419...) cho khu vực /admin, hiển thị bằng Vue
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            if (! $request->is('admin', 'admin/*') || $request->expectsJson()) {
                return $response;
            }

            $status = $response->getStatusCode();
            $debug = config('app.debug');

            // Khi debug, lỗi 500 để Laravel hiện chi tiết cho dev
            if (! in_array($status, [403, 404, 419], true) && ($debug || ! in_array($status, [500, 503], true))) {
                return $response;
            }

            $messages = [
                403 => 'Bạn không có quyền thực hiện thao tác này.',
                404 => 'Không tìm thấy trang hoặc dữ liệu yêu cầu.',
                419 => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.',
                500 => 'Hệ thống gặp lỗi. Vui lòng thử lại sau.',
                503 => 'Hệ thống đang bảo trì. Vui lòng quay lại sau.',
            ];

            // Khi debug: hiện lý do từ chối cụ thể (ví dụ route chưa khai báo quyền nào)
            $message = ($status === 403 && $debug && $e->getMessage() !== '')
                ? $e->getMessage()
                : $messages[$status];

            // Route không tồn tại thì middleware Inertia chưa chạy, nên đặt root view tại đây
            Inertia::setRootView('admin');

            return Inertia::render('Error', ['status' => $status, 'message' => $message])
                ->toResponse($request)
                ->setStatusCode($status);
        });
    })->create();