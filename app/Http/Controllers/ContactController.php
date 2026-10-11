<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Show the public contact form.
     */
    public function show(): View
    {
        return view('pages.contact');
    }

    /**
     * Store a contact message. Email delivery is intentionally not wired up
     * yet — submissions are saved to the database for now.
     */
    public function send(StoreContactMessageRequest $request): RedirectResponse
    {
        // Honeypot tripped: pretend everything was fine but store nothing, so
        // spam bots get no signal that they were caught.
        if ($request->isSpam()) {
            return back()->with('success', 'Thanks! We will get back to you soon.');
        }

        $validated = $request->validated();

        ContactMessage::create([
            'name'    => $validated['name'],
            'email'   => $validated['email'],
            'message' => $validated['message'],
            'ip'      => $request->ip(),
        ]);

        return back()->with('success', 'Thanks! We will get back to you soon.');
    }
}
