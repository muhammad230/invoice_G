<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPageTest extends TestCase
{
    use RefreshDatabase;

    /** Create a user with one client. Returns [$user, $client]. */
    private function userWithClient(string $clientName = 'Acme Co'): array
    {
        $user = User::factory()->create();

        $client = Client::query()->create([
            'user_id' => $user->id,
            'name'    => $clientName,
            'email'   => 'billing@acme.test',
        ]);

        return [$user, $client];
    }

    private function invoiceFor(User $user, Client $client, array $overrides = []): Invoice
    {
        return Invoice::query()->create(array_merge([
            'user_id'        => $user->id,
            'client_id'      => $client->id,
            'invoice_number' => 'INV-0001',
            'invoice_date'   => now()->toDateString(),
            'currency'       => 'USD',
            'status'         => Invoice::STATUS_PENDING,
            'subtotal'       => 150000,
            'tax_percent'    => 0,
            'tax_amount'     => 0,
            'discount'       => 0,
            'total'          => 150000,
        ], $overrides));
    }

    private function itemFor(Invoice $invoice, string $description, int $totalCents): InvoiceItem
    {
        return InvoiceItem::query()->create([
            'invoice_id'  => $invoice->id,
            'description' => $description,
            'quantity'    => 1,
            'price'       => $totalCents,
            'total'       => $totalCents,
        ]);
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

    public function test_reports_page_renders_cards_filters_and_chart_canvases(): void
    {
        [$user, $client] = $this->userWithClient();
        $invoice = $this->invoiceFor($user, $client);
        $this->paymentFor($user, $invoice);

        $html = $this->actingAs($user)
            ->get('/reports')
            ->assertStatus(200)
            ->getContent();

        // Cards present + currency selector + export link + filter form.
        $this->assertStringContainsString('Total Revenue', $html);
        $this->assertStringContainsString('Pending', $html);
        $this->assertStringContainsString('Overdue', $html);
        $this->assertStringContainsString('Invoices Created', $html);
        $this->assertStringContainsString('Average Invoice', $html);
        $this->assertStringContainsString('dash-currency-btn', $html);
        $this->assertStringContainsString('Export CSV', $html);
        $this->assertStringContainsString('name="range"', $html);

        // Chart canvases + the empty-state overlays are rendered.
        foreach (['revenueMonthChart', 'paidUnpaidChart', 'topClientsChart', 'serviceChart'] as $id) {
            $this->assertStringContainsString('id="' . $id . '"', $html);
        }
        $this->assertStringContainsString('data-empty-for', $html);
        $this->assertStringContainsString('window.REPORT_FILTERS', $html);
    }

    public function test_revenue_by_month_endpoint_is_scoped_to_user_and_currency(): void
    {
        [$user, $client] = $this->userWithClient();
        $usdInvoice = $this->invoiceFor($user, $client, ['total' => 300000]);
        $this->paymentFor($user, $usdInvoice, ['amount' => 300000]);

        $pkrInvoice = $this->invoiceFor($user, $client, [
            'invoice_number' => 'INV-PKR',
            'currency'       => 'PKR',
            'total'          => 500000,
        ]);
        $this->paymentFor($user, $pkrInvoice, ['amount' => 500000]);

        // Another user also has USD revenue this month — must be ignored.
        [$other] = $this->userWithClient('Other Co');
        $otherInvoice = $this->invoiceFor($other, $other->clients()->first(), ['total' => 900000]);
        $this->paymentFor($other, $otherInvoice, ['amount' => 900000]);

        $json = $this->actingAs($user)
            ->getJson('/reports/chart/revenue-by-month?range=this_month&currency=USD')
            ->assertStatus(200)
            ->json();

        $this->assertSame('USD', $json['currency']);
        $this->assertSame('$', $json['symbol']);
        $this->assertFalse($json['empty']);
        $this->assertSame(3000.0, (float) array_sum($json['data']), 'must sum the user\'s USD revenue only');
    }

    public function test_paid_unpaid_counts_sum_to_invoice_count_for_currency(): void
    {
        [$user, $client] = $this->userWithClient();

        // 1 paid + 1 pending + 1 overdue (all USD).
        $this->invoiceFor($user, $client, [
            'invoice_number' => 'INV-1', 'status' => Invoice::STATUS_PAID,
        ]);
        $this->invoiceFor($user, $client, [
            'invoice_number' => 'INV-2', 'status' => Invoice::STATUS_PENDING,
        ]);
        $this->invoiceFor($user, $client, [
            'invoice_number' => 'INV-3', 'status' => Invoice::STATUS_PENDING,
            'due_date'       => now()->subDay()->toDateString(),
        ]);

        $json = $this->actingAs($user)
            ->getJson('/reports/chart/paid-unpaid?currency=USD')
            ->assertStatus(200)
            ->json();

        $this->assertSame([1, 1, 1], $json['data'], 'paid, pending, overdue');
        $this->assertSame(3, $json['total']);

        // Guarantee the invariant from the rule set.
        $this->assertSame(
            $json['total'],
            $json['data'][0] + $json['data'][1] + $json['data'][2],
            'paid + pending + overdue must equal the invoice count'
        );
    }

    public function test_top_clients_and_service_endpoints_are_currency_scoped(): void
    {
        [$user, $client] = $this->userWithClient('Injection Client');
        $invoice = $this->invoiceFor($user, $client, ['status' => Invoice::STATUS_PAID]);
        $this->itemFor($invoice, 'Consulting', 100000);
        $this->itemFor($invoice, 'Design', 50000);
        $this->paymentFor($user, $invoice);

        $pkrInvoice = $this->invoiceFor($user, $client, [
            'invoice_number' => 'INV-PKR',
            'currency'       => 'PKR',
            'status'         => Invoice::STATUS_PAID,
        ]);
        $this->itemFor($pkrInvoice, 'PKR-only service', 999999);
        $this->paymentFor($user, $pkrInvoice, ['amount' => 999999]);

        // USD: top client is the only USD one, services exclude the PKR one.
        $clients = $this->actingAs($user)
            ->getJson('/reports/chart/top-clients?range=this_month&currency=USD')
            ->assertStatus(200)
            ->json();

        $this->assertSame(['Injection Client'], $clients['labels']);
        $this->assertSame(1500.0, (float) $clients['data'][0]);

        $services = $this->actingAs($user)
            ->getJson('/reports/chart/revenue-by-service?range=this_month&currency=USD')
            ->assertStatus(200)
            ->json();

        $this->assertContains('Consulting', $services['labels']);
        $this->assertNotContains('PKR-only service', $services['labels']);
        $this->assertSame(1000.0, (float) $services['data'][0]);
        $this->assertSame(500.0, (float) $services['data'][1]);
    }

    public function test_csv_export_converts_minor_units_and_escapes_formula_cells(): void
    {
        [$user, $client] = $this->userWithClient('=SUM(A1:A2)');
        $invoice = $this->invoiceFor($user, $client, [
            'invoice_number' => '=1+1',
            'invoice_date'   => now()->toDateString(),
            'due_date'       => now()->addDays(30)->toDateString(),
            'subtotal'       => 150000,
            'tax_percent'    => 8,
            'tax_amount'     => 12000,
            'discount'       => 5000,
            'total'          => 157000,
            'status'         => Invoice::STATUS_PENDING,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.export', ['currency' => 'USD', 'range' => 'this_month']));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename=', $response->headers->get('Content-Disposition'));

        $csv = $response->streamedContent();

        // UTF-8 BOM so Excel reads the accents correctly.
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);

        // Header + row with decimals; the only row is this invoice.
        $this->assertStringContainsString('"Invoice","Client","Date","Due date"', $csv);
        $this->assertStringContainsString('1500.00', $csv);
        $this->assertStringContainsString('120.00', $csv);
        $this->assertStringContainsString('50.00', $csv);
        $this->assertStringContainsString('1570.00', $csv);
        $this->assertStringContainsString('"pending"', $csv);

        // Formula injection is neutralised with a leading apostrophe.
        $this->assertStringContainsString('"\'=1+1"', $csv);
        $this->assertStringContainsString('"\'=SUM(A1:A2)"', $csv);
    }

    public function test_csv_export_respects_custom_range_and_excludes_other_users(): void
    {
        [$user, $client] = $this->userWithClient();
        $inRange = $this->invoiceFor($user, $client, ['invoice_number' => 'INV-NOW']);
        $old = $this->invoiceFor($user, $client, [
            'invoice_number' => 'INV-OLD',
            'invoice_date'   => now()->subDays(400)->toDateString(),
        ]);

        // Another user's invoice in the same currency + window.
        [$other] = $this->userWithClient('Other Co');
        $this->invoiceFor($other, $other->clients()->first(), ['invoice_number' => 'INV-OTHER']);

        // Default this_month: old + other invoices are excluded.
        $csv = $this->actingAs($user)
            ->get(route('reports.export', ['currency' => 'USD', 'range' => 'this_month']))
            ->streamedContent();

        $this->assertStringContainsString('INV-NOW', $csv);
        $this->assertStringNotContainsString('INV-OLD', $csv);
        $this->assertStringNotContainsString('INV-OTHER', $csv);

        // Custom range covering the old invoice includes it.
        $custom = $this->actingAs($user)
            ->get(route('reports.export', [
                'currency' => 'USD',
                'range'    => 'custom',
                'from'     => now()->subDays(410)->toDateString(),
                'to'       => now()->toDateString(),
            ]))
            ->streamedContent();

        $this->assertStringContainsString('INV-OLD', $custom);
        $this->assertStringContainsString('INV-NOW', $custom);
        $this->assertStringNotContainsString('INV-OTHER', $custom);
    }

    public function test_reports_are_off_limits_to_guests(): void
    {
        // Unauthenticated visitors are redirected to login (authorization gate).
        $this->get('/reports')->assertRedirect(route('login'));
        $this->get(route('reports.export'))->assertRedirect(route('login'));
        // JSON endpoints respond 401 (instead of redirecting) for guests.
        $this->getJson('/reports/chart/revenue-by-month')->assertStatus(401);
    }
}