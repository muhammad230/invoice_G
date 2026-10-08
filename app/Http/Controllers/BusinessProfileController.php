<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBusinessProfileRequest;
use App\Models\BusinessProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BusinessProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * GET /settings/business — show the edit form for the user's profile.
     * Creates an empty BusinessProfile row if none exists yet.
     */
    public function edit(): View
    {
        $user = auth()->user();
        $profile = $user->businessProfile;

        if (!$profile) {
            $profile = BusinessProfile::make([
                'business_name' => $user->name,
                'email'         => $user->email,
            ]);
        }

        return view('settings.business-profile', compact('profile'));
    }

    /**
     * PUT /settings/business — save the business profile (logo on public disk).
     */
    public function update(UpdateBusinessProfileRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user      = auth()->user();

        $profile = $user->businessProfile;
        $isNew   = $profile === null;

        $data = [
            'business_name' => $validated['business_name'],
            'email'         => $validated['email']      ?? null,
            'phone'         => $validated['phone']      ?? null,
            'website'       => $validated['website']    ?? null,
            'address'       => $validated['address']    ?? null,
            'tax_number'    => $validated['tax_number'] ?? null,
        ];

        if ($isNew) {
            $profile = new BusinessProfile();
            $profile->user_id = $user->id;
            $profile->fill($data);
            $profile->save();
        } else {
            $profile->update($data);
        }

        if (!empty($validated['remove_logo']) && $profile->logo_path) {
            $this->deleteLogo($profile->logo_path);
            $profile->update(['logo_path' => null]);
        }

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            if ($profile->logo_path) {
                $this->deleteLogo($profile->logo_path);
            }

            $disk    = Storage::disk('public');
            $ext     = $request->file('logo')->getClientOriginalExtension();
            $ext     = strtolower($ext) === 'jpeg' ? 'jpg' : $ext;
            $folder  = 'business-logos';
            $name    = 'user-' . $user->id . '-' . Date::now()->timestamp . '.' . $ext;

            $storedPath = $request->file('logo')->storeAs($folder, $name, 'public');
            $profile->update(['logo_path' => $storedPath]);
        }

        session()->flash('success', 'Business profile saved.');

        return redirect()->route('settings.business-profile.edit');
    }

    protected function deleteLogo(string $path): void
    {
        try {
            $disk = Storage::disk('public');
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        } catch (\Throwable $e) {
        }
    }
}
