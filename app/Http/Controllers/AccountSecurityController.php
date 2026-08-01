<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AccountSecurityController extends Controller
{
    public function __invoke(): View
    {
        return view('account.security');
    }
}
