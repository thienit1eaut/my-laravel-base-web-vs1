<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <x-inertia::head>
        <title>{{ config('app.name') }} - Hệ thống quản trị</title>
    </x-inertia::head>

    @vite(['resources/css/admin/app.css', 'resources/js/admin/app.js'])
</head>
<body class="antialiased">
    <x-inertia::app />
</body>
</html>