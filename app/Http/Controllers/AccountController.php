<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdateAccountPasswordRequest;
use App\Http\Requests\UpdateAccountProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Self-service account management. Every action operates on the currently
 * authenticated user only, so a user can never read or touch another's data.
 */
class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * GET /account — profile, password and danger-zone cards.
     */
    public function edit(): View
    {
        return view('account.index', ['user' => auth()->user()]);
    }

    /**
     * PUT /account/profile — update name + email. Changing the email
     * requires the current password (enforced by the Form Request).
     */
    public function updateProfile(UpdateAccountProfileRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        session()->flash('success', 'Your profile has been updated.');

        return redirect()->route('account.edit');
    }

    /**
     * PUT /account/password — set a new password, then log out the user's
     * other sessions so a stolen session cannot keep using the old one.
     */
    public function updatePassword(UpdateAccountPasswordRequest $request): RedirectResponse
    {
        $user    = $request->user();
        $newPass = $request->validated('password');

        // The "hashed" cast stores the bcrypt hash automatically.
        $user->update(['password' => $newPass]);

        // Invalidate other devices (checks the password we just set).
        Auth::logoutOtherDevices($newPass);

        session()->flash('success', 'Your password has been changed.');

        return redirect()->route('account.edit');
    }

    /**
     * DELETE /account — permanently remove the user and everything they own.
     * Runs in a transaction so we never leave orphaned rows behind.
     */
    public function destroy(DeleteAccountRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Log out first: SessionGuard::logout() cycles the remember token via
        // $user->save(). Doing this after the delete would re-insert the row
        // (the in-memory model's exists flag is false), so log out while the
        // user still exists.
        Auth::logout();

        DB::transaction(function () use ($user) {
            // Delete stored logo files (public disk) before the rows vanish.
            $logoPath = $user->businessProfile?->logo_path;
            if ($logoPath) {
                try {
                    Storage::disk('public')->delete($logoPath);
                } catch (\Throwable) {
                    // A missing file should never block account deletion.
                }
            }

            // Children before parents. Invoice items and payments cascade
            // from invoices, but we delete explicitly for clarity.
            $user->payments()->delete();
            $user->invoices()->delete();
            $user->clients()->delete();
            $user->products()->delete();
            $user->businessProfile()->delete();
            $user->delete();
        });

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Your account and all associated data have been deleted.');
    }
}
