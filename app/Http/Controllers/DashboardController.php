<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the authenticated user's dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Placeholder stats — wired to the correct shape so real data
        // can be plugged in later without changing the view.
        $stats = [
            'total'   => 0,
            'paid'    => 0,
            'pending' => 0,
            'overdue' => 0,
        ];

        // Time-based greeting (Good morning / afternoon / evening).
        $hour    = (int) now()->format('G');
        $greet   = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

        return view('dashboard', [
            'user'       => $user,
            'stats'      => $stats,
            'greeting'   => $greet,
        ]);
    }
}
