<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private function invoiceFor(User $user, array $overrides = []): Invoice
    {
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name'    => 'Acme Co',
            'email'   => 'billing@acme.test',
        ]);

        return Invoice::query()->create(array_merge([
            'user_id'        => $user->id,
            'client_id'      => $client->id,
            'invoice_number' => 'INV-0001',
            'invoice_date'   => now()->toDateString(),
            'currency'       => 'USD',
            'status'         => Invoice::STATUS_PENDING,
            'total'          => 150000,
        ], $overrides));
    }

    private function paymentFor(User $user, Invoice $invoice, array $overrides = []): Payment
    {
        return Payment::query()->create(array_merge([
            'user_id'    => $user->id,
            'invoice_id' => $invoice->id,
            'amount'     => $invoice->total,
            'paid_on'    => now()->toDateString(),
            'method'     => Payment::METHOD_CASH,
        ], $overrides));
    }

    public function test_marking_invoice_paid_records_a_payment_and_settles_it(): void
    {
        $user    = User::factory()->create();
        $invoice = $this->invoiceFor($user);

        $response = $this->actingAs($user)->post(route('invoices.pay', $invoice), [
            'paid_on'   => now()->toDateString(),
            'method'    => Payment::METHOD_BANK_TRANSFER,
            'reference' => 'TXN-9001',
        ]);

        $response->assertRedirect(route('invoices.show', $invoice));

        $this->assertDatabaseHas('payments', [
            'user_id'    => $user->id,
            'invoice_id' => $invoice->id,
            'amount'     => 150000,
            'method'     => Payment::METHOD_BANK_TRANSFER,
            'reference'  => 'TXN-9001',
        ]);

        $this->assertSame(Invoice::STATUS_PAID, $invoice->fresh()->status);
    }

    public function test_mark_paid_validates_date_and_method(): void
    {
        $user    = User::factory()->create();
        $invoice = $this->invoiceFor($user);

        // Future payment date is rejected.
        $this->actingAs($user)
            ->post(route('invoices.pay', $invoice), [
                'paid_on' => now()->addDay()->toDateString(),
                'method'  => Payment::METHOD_CASH,
            ])
            ->assertSessionHasErrors('paid_on');

        // Unknown method is rejected.
        $this->actingAs($user)
            ->post(route('invoices.pay', $invoice), [
                'paid_on' => now()->toDateString(),
                'method'  => 'bitcoin',
            ])
            ->assertSessionHasErrors('method');

        $this->assertDatabaseCount('payments', 0);
        $this->assertSame(Invoice::STATUS_PENDING, $invoice->fresh()->status);
    }

    public function test_marking_an_already_paid_invoice_does_nothing(): void
    {
        $user    = User::factory()->create();
        $invoice = $this->invoiceFor($user, ['status' => Invoice::STATUS_PAID]);
        $this->paymentFor($user, $invoice);

        $response = $this->actingAs($user)->post(route('invoices.pay', $invoice), [
            'paid_on' => now()->toDateString(),
            'method'  => Payment::METHOD_CARD,
        ]);

        $response->assertRedirect(route('invoices.show', $invoice));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('payments', 1);
        $this->assertSame(Invoice::STATUS_PAID, $invoice->fresh()->status);
    }

    public function test_mark_paid_rejects_another_users_invoice(): void
    {
        $owner   = User::factory()->create();
        $invoice = $this->invoiceFor($owner);
        $other   = User::factory()->create();

        $this->actingAs($other)
            ->post(route('invoices.pay', $invoice), [
                'paid_on' => now()->toDateString(),
                'method'  => Payment::METHOD_CASH,
            ])
            ->assertStatus(403);

        $this->assertDatabaseCount('payments', 0);
        $this->assertSame(Invoice::STATUS_PENDING, $invoice->fresh()->status);
    }

    public function test_undoing_a_payment_deletes_it_and_reopens_the_invoice(): void
    {
        $user    = User::factory()->create();
        $invoice = $this->invoiceFor($user, ['status' => Invoice::STATUS_PAID]);
        $payment = $this->paymentFor($user, $invoice);

        $response = $this->actingAs($user)->delete(route('payments.destroy', $payment));

        $response->assertRedirect(route('invoices.show', $invoice));

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
        $this->assertSame(Invoice::STATUS_PENDING, $invoice->fresh()->status);
    }

    public function test_undo_rejects_another_users_payment(): void
    {
        $owner   = User::factory()->create();
        $invoice = $this->invoiceFor($owner, ['status' => Invoice::STATUS_PAID]);
        $payment = $this->paymentFor($owner, $invoice);
        $other   = User::factory()->create();

        $this->actingAs($other)
            ->delete(route('payments.destroy', $payment))
            ->assertStatus(403);

        $this->assertDatabaseHas('payments', ['id' => $payment->id]);
        $this->assertSame(Invoice::STATUS_PAID, $invoice->fresh()->status);
    }

    public function test_payments_index_is_scoped_to_user_and_selected_currency(): void
    {
        $user = User::factory()->create();

        $usdInvoice = $this->invoiceFor($user, [
            'invoice_number' => 'INV-USD',
            'currency'       => 'USD',
            'status'         => Invoice::STATUS_PAID,
        ]);
        $this->paymentFor($user, $usdInvoice, ['reference' => 'usd-ref']);

        $pkrInvoice = $this->invoiceFor($user, [
            'invoice_number' => 'INV-PKR',
            'currency'       => 'PKR',
            'status'         => Invoice::STATUS_PAID,
        ]);
        $this->paymentFor($user, $pkrInvoice);

        // Another user's payment must never appear.
        $other      = User::factory()->create();
        $otherInv   = $this->invoiceFor($other, [
            'invoice_number' => 'INV-OTHER',
            'currency'       => 'USD',
            'status'         => Invoice::STATUS_PAID,
        ]);
        $this->paymentFor($other, $otherInv);

        $html = $this->actingAs($user)
            ->get(route('payments.index', ['currency' => 'USD']))
            ->assertStatus(200)
            ->getContent();

        $this->assertStringContainsString('INV-USD', $html);
        $this->assertStringNotContainsString('INV-PKR', $html);
        $this->assertStringNotContainsString('INV-OTHER', $html);

        // Summary cards are rendered for the currency.
        $this->assertStringContainsString('Total Received', $html);
    }

    public function test_payments_index_filters_by_method(): void
    {
        $user    = User::factory()->create();
        $invoice = $this->invoiceFor($user, [
            'invoice_number' => 'INV-CASH',
            'status'         => Invoice::STATUS_PAID,
        ]);
        $this->paymentFor($user, $invoice, ['method' => Payment::METHOD_CASH]);

        $cardInvoice = $this->invoiceFor($user, [
            'invoice_number' => 'INV-CARD',
            'status'         => Invoice::STATUS_PAID,
        ]);
        $this->paymentFor($user, $cardInvoice, ['method' => Payment::METHOD_CARD]);

        $html = $this->actingAs($user)
            ->get(route('payments.index', ['method' => Payment::METHOD_CARD]))
            ->assertStatus(200)
            ->getContent();

        $this->assertStringContainsString('INV-CARD', $html);
        $this->assertStringNotContainsString('INV-CASH', $html);
    }

    public function test_invoice_show_renders_mark_paid_modal_and_payment_ledger(): void
    {
        $user = User::factory()->create();

        // Pending invoice → "Mark as paid" button + modal (but no ledger yet).
        $pending = $this->invoiceFor($user, ['invoice_number' => 'INV-PEND']);
        $html = $this->actingAs($user)
            ->get(route('invoices.show', $pending))
            ->assertStatus(200)
            ->getContent();
        $this->assertStringContainsString('data-bs-target="#markPaidModal"', $html);
        $this->assertStringContainsString('id="markPaidModal"', $html);
        $this->assertStringContainsString('name="method"', $html);
        $this->assertStringContainsString('name="paid_on"', $html);

        // Paid invoice → no modal, but the payment ledger + undo action render.
        $paid = $this->invoiceFor($user, [
            'invoice_number' => 'INV-PAID',
            'status'         => Invoice::STATUS_PAID,
        ]);
        $this->paymentFor($user, $paid, ['method' => Payment::METHOD_CASH, 'reference' => 'chq-77']);

        $paidHtml = $this->actingAs($user)
            ->get(route('invoices.show', $paid))
            ->assertStatus(200)
            ->getContent();
        $this->assertStringNotContainsString('id="markPaidModal"', $paidHtml);
        $this->assertStringContainsString('bi-cash-coin', $paidHtml);
        $this->assertStringContainsString('Cash', $paidHtml);
        $this->assertStringContainsString('chq-77', $paidHtml);
        $this->assertStringContainsString('$1,500.00', $paidHtml);
        $this->assertStringContainsString('Undo this payment', $paidHtml);
    }
}
