<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * List of the authenticated user's clients (paginated 10 per page).
     */
    public function index(): View
    {
        $user = auth()->user();

        $clients = Client::where('user_id', $user->id)
            ->withCount('invoices')
            ->orderBy('name')
            ->paginate(10);

        return view('clients.index', compact('clients'));
    }

    /**
     * Show the create-client form.
     */
    public function create(): View
    {
        return view('clients.create', ['client' => new Client()]);
    }

    /**
     * Persist a new client owned by the current user.
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $client = Client::create([
            'user_id' => auth()->id(),
            'name'    => $validated['name'],
            'email'   => $validated['email']   ?? null,
            'phone'   => $validated['phone']   ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        session()->flash('success', "Client &ldquo;{$client->name}&rdquo; added.");

        // If user came from the invoice builder with a "return" URL, send them back.
        $redirectTo = $request->query('return', route('clients.index'));

        return redirect()->to($redirectTo)->with('new_client_id', $client->id);
    }

    /**
     * Show the edit-client form.
     */
    public function edit(Client $client): View
    {
        $this->authorize('update', $client);

        return view('clients.edit', compact('client'));
    }

    /**
     * Update an existing client.
     */
    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $validated = $request->validated();

        $client->update([
            'name'    => $validated['name'],
            'email'   => $validated['email']   ?? null,
            'phone'   => $validated['phone']   ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        session()->flash('success', "Client &ldquo;{$client->name}&rdquo; updated.");

        return redirect()->route('clients.index');
    }

    /**
     * Delete a client (and cascade-delete its invoices + items via DB FK).
     */
    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $name = $client->name;
        $client->delete();

        session()->flash('success', "Client &ldquo;{$name}&rdquo; deleted.");

        return redirect()->route('clients.index');
    }
}
