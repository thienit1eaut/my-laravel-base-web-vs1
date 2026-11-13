<?php

namespace App\Web\Http\Controllers;

use Illuminate\Contracts\View\View;

class HomeController
{
    public function index(): View
    {
        return view('web.home');
    }
}