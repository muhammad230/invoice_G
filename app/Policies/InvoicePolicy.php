<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return (int) $invoice->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Ability to email an invoice / reminder to the client.
     * Only the invoice owner may send it.
     */
    public function send(User $user, Invoice $invoice): bool
    {
        return (int) $invoice->user_id === (int) $user->id;
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return (int) $invoice->user_id === (int) $user->id;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return (int) $invoice->user_id === (int) $user->id;
    }
}
