<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class FrontendController extends Controller
{
    /**
     * Handle requests to frontend.
     *
     * @return  View
     */
    public function frontend(): View
    {
        // TODO proper select application to run
        return view('admin');
    }
}
