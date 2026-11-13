<?php

namespace App\Admin\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'serverTime' => now('Asia/Ho_Chi_Minh')->format('H:i:s d/m/Y'),
            'title' => 'Tổng quan',
            'breadcrumbs' => [],
        ]);
    }
}