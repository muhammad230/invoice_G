<?php

namespace Tests\Feature;

use App\Mail\InvoiceMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class InvoiceEmailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create an invoice (with a client that has an email) owned by $user.
     */
    private function invoiceFor(User $user, array $overrides = []): Invoice
    {
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name' => 'Acme Co',
            'email' => 'client@acme.test',
        ]);

        return Invoice::query()->create(array_merge([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => 'INV-0001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'currency' => 'USD',
            'status' => Invoice::STATUS_PENDING,
            'total' => 150000,
            'business_name' => 'Ledger Studio',
        ], $overrides));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => InvoiceEmail::TYPE_INVOICE,
            'to' => 'client@acme.test',
            'subject' => 'Invoice INV-0001 from Ledger Studio',
            'message' => 'Please find attached invoice INV-0001.',
        ], $overrides);
    }

    public function test_sending_creates_a_log_row_and_stamps_sent_at(): void
    {
        Mail::fake();
        Queue::fake();

        $user = User::factory()->create(['email' => 'owner@ledger.test']);
        $invoice = $this->invoiceFor($user);

        $response = $this->actingAs($user)
            ->from(route('invoices.show', $invoice))
            ->post(route('invoices.send', $invoice), $this->payload());

        $response->assertRedirect(route('invoices.show', $invoice));

        $this->assertDatabaseHas('invoice_emails', [
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'to_email' => 'client@acme.test',
            'type' => InvoiceEmail::TYPE_INVOICE,
            'status' => InvoiceEmail::STATUS_SENT,
        ]);

        $this->assertNotNull($invoice->fresh()->sent_at);
        Mail::assertQueued(InvoiceMail::class);
    }

    public function test_pdf_is_attached_and_reply_to_points_at_the_business(): void
    {
        Mail::fake();
        Queue::fake();

        $user = User::factory()->create(['email' => 'owner@ledger.test']);
        $invoice = $this->invoiceFor($user);

        $this->actingAs($user)
            ->from(route('invoices.show', $invoice))
            ->post(route('invoices.send', $invoice), $this->payload());

        Mail::assertQueued(InvoiceMail::class, function (InvoiceMail $mail) {
            $attachments = $mail->attachments();

            return count($attachments) === 1
                && $attachments[0]->as === 'invoice-INV-0001.pdf'
                && $attachments[0]->mime === 'application/pdf'
                && $mail->hasReplyTo('owner@ledger.test');
        });
    }

    public function test_invalid_email_is_rejected_and_nothing_is_logged(): void
    {
        Mail::fake();
        Queue::fake();

        $user = User::factory()->create();
        $invoice = $this->invoiceFor($user);

        $this->actingAs($user)
            ->from(route('invoices.show', $invoice))
            ->post(route('invoices.send', $invoice), $this->payload(['to' => 'not-an-email']))
            ->assertSessionHasErrors('to');

        $this->assertDatabaseCount('invoice_emails', 0);
        $this->assertNull($invoice->fresh()->sent_at);
        Mail::assertNothingQueued();
    }

    public function test_header_injection_in_to_and_subject_is_rejected(): void
    {
        Mail::fake();
        Queue::fake();

        $user = User::factory()->create();
        $invoice = $this->invoiceFor($user);

        $this->actingAs($user)
            ->from(route('invoices.show', $invoice))
            ->post(route('invoices.send', $invoice), $this->payload([
                'to' => "client@acme.test\r\nBcc: attacker@evil.test",
            ]))
            ->assertSessionHasErrors('to');

        $this->actingAs($user)
            ->from(route('invoices.show', $invoice))
            ->post(route('invoices.send', $invoice), $this->payload([
                'subject' => "Hello\r\nBcc: attacker@evil.test",
            ]))
            ->assertSessionHasErrors('subject');

        $this->assertDatabaseCount('invoice_emails', 0);
        Mail::assertNothingQueued();
    }

    public function test_paid_invoice_is_refused(): void
    {
        Mail::fake();
        Queue::fake();

        $user = User::factory()->create();
        $invoice = $this->invoiceFor($user, ['status' => Invoice::STATUS_PAID]);

        $this->actingAs($user)
            ->from(route('invoices.show', $invoice))
            ->post(route('invoices.send', $invoice), $this->payload())
            ->assertRedirect(route('invoices.show', $invoice))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('invoice_emails', 0);
        Mail::assertNothingQueued();
    }

    public function test_another_user_cannot_send_an_invoice(): void
    {
        Mail::fake();
        Queue::fake();

        $owner = User::factory()->create();
        $invoice = $this->invoiceFor($owner);
        $other = User::factory()->create();

        $this->actingAs($other)
            ->post(route('invoices.send', $invoice), $this->payload())
            ->assertForbidden();

        $this->assertDatabaseCount('invoice_emails', 0);
        Mail::assertNothingQueued();
    }

    public function test_reminder_is_logged_with_the_reminder_type(): void
    {
        Mail::fake();
        Queue::fake();

        $user = User::factory()->create();
        $invoice = $this->invoiceFor($user);

        $this->actingAs($user)
            ->from(route('invoices.show', $invoice))
            ->post(route('invoices.send', $invoice), $this->payload([
                'type' => InvoiceEmail::TYPE_REMINDER,
                'subject' => 'Reminder: Invoice INV-0001 is due',
            ]));

        $this->assertDatabaseHas('invoice_emails', [
            'invoice_id' => $invoice->id,
            'type' => InvoiceEmail::TYPE_REMINDER,
            'status' => InvoiceEmail::STATUS_SENT,
        ]);
    }

    public function test_send_me_a_copy_sends_a_second_email_to_the_user(): void
    {
        Mail::fake();
        Queue::fake();

        $user = User::factory()->create(['email' => 'owner@ledger.test']);
        $invoice = $this->invoiceFor($user);

        $this->actingAs($user)
            ->from(route('invoices.show', $invoice))
            ->post(route('invoices.send', $invoice), $this->payload(['send_copy' => '1']));

        Mail::assertQueued(InvoiceMail::class, fn (InvoiceMail $mail) => $mail->hasTo('client@acme.test'));
        Mail::assertQueued(InvoiceMail::class, fn (InvoiceMail $mail) => $mail->hasTo('owner@ledger.test'));
    }

    public function test_second_send_keeps_the_original_sent_at(): void
    {
        Mail::fake();
        Queue::fake();

        $user = User::factory()->create();
        $invoice = $this->invoiceFor($user);

        $this->actingAs($user)
            ->from(route('invoices.show', $invoice))
            ->post(route('invoices.send', $invoice), $this->payload());

        $firstSentAt = $invoice->fresh()->sent_at;

        // Move time forward and send again.
        $this->travel(2)->hours();

        $this->actingAs($user)
            ->from(route('invoices.show', $invoice))
            ->post(route('invoices.send', $invoice), $this->payload());

        $this->assertSame(2, $invoice->fresh()->emails()->count());
        $this->assertTrue($firstSentAt->equalTo($invoice->fresh()->sent_at));
    }

    public function test_show_page_shows_the_sent_badge_and_email_history(): void
    {
        Mail::fake();
        Queue::fake();

        $user = User::factory()->create();
        $invoice = $this->invoiceFor($user);

        $this->actingAs($user)
            ->from(route('invoices.show', $invoice))
            ->post(route('invoices.send', $invoice), $this->payload());

        $this->actingAs($user)
            ->get(route('invoices.show', $invoice))
            ->assertOk()
            ->assertSee('Sent on')
            ->assertSee('Email history')
            ->assertSee('client@acme.test');
    }
}
