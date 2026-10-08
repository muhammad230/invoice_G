<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfDownloadTest extends TestCase
{
    use RefreshDatabase;

    private function invoiceFor(User $user): Invoice
    {
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name'    => 'Acme Co',
            'email'   => 'billing@acme.test',
        ]);

        return Invoice::query()->create([
            'user_id'        => $user->id,
            'client_id'      => $client->id,
            'invoice_number' => 'INV-0001',
            'invoice_date'   => now()->toDateString(),
            'status'         => 'pending',
            'total'          => 150000,
        ]);
    }

    public function test_pdf_route_returns_inline_pdf(): void
    {
        $user    = User::factory()->create();
        $invoice = $this->invoiceFor($user);

        $response = $this->actingAs($user)->get("/invoices/{$invoice->id}/pdf");

        $response->assertStatus(200);
        $this->assertStringContainsString('pdf', (string) $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
        // Inline (not attachment) — the list links open it in a new tab.
        $this->assertStringContainsString(
            'inline',
            (string) $response->headers->get('Content-Disposition')
        );
    }

    public function test_pdf_route_rejects_other_users_invoices(): void
    {
        $owner  = User::factory()->create();
        $invoice = $this->invoiceFor($owner);
        $other  = User::factory()->create();

        $this->actingAs($other)->get("/invoices/{$invoice->id}/pdf")->assertStatus(403);
    }
}
