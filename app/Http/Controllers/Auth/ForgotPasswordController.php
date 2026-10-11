<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | Handles requesting a password reset link. Two behaviours are customised:
    |
    |  1. Requests are throttled to 3 per minute, per email address + IP.
    |  2. The response NEVER reveals whether an email address exists — the same
    |     "link sent" message is shown for known and unknown addresses.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Send a reset link to the given user (throttled, non-revealing).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // Throttle by IP + email so neither a single address nor a single
        // machine can spam reset emails.
        $key = 'password-reset:' . $request->ip() . '|'
            . Str::lower((string) $request->input('email'));

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('passwords.throttled')]);
        }

        RateLimiter::hit($key, 60);

        // Attempt the reset, but deliberately ignore the broker result so the
        // UI is identical whether or not the address is registered.
        $this->broker()->sendResetLink($this->credentials($request));

        return back()->with('status', __('passwords.sent'));
    }
}
