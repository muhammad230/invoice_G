<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Static marketing / info pages reachable without signing in.
 * Each method just returns its view; all copy lives in the Blade templates.
 */
class PageController extends Controller
{
    public function features(): View
    {
        return view('pages.features');
    }

    public function pricing(): View
    {
        return view('pages.pricing');
    }

    public function help(): View
    {
        return view('pages.help');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function terms(): View
    {
        return view('pages.terms');
    }
}
