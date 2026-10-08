<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceIndexTest extends TestCase
{
    use RefreshDatabase;

    /** Create a user with $count pending invoices (INV-0001 … INV-00NN). */
    private function userWithInvoices(int $count): User
    {
        $user = User::factory()->create();

        $client = Client::query()->create([
            'user_id' => $user->id,
            'name'    => 'Acme Co',
            'email'   => 'billing@acme.test',
        ]);

        foreach (range(1, $count) as $i) {
            Invoice::query()->create([
                'user_id'        => $user->id,
                'client_id'      => $client->id,
                'invoice_number' => sprintf('INV-%04d', $i),
                'invoice_date'   => now()->toDateString(),
                'status'         => 'pending',
                'total'          => 150000,
            ]);
        }

        return $user;
    }

    public function test_pagination_renders_bootstrap_markup_and_slices_pages(): void
    {
        $user = $this->userWithInvoices(11);

        $html = $this->actingAs($user)->get('/invoices')->assertStatus(200)->getContent();

        // Bootstrap pagination classes (the default Tailwind markup had no
        // styling in this Bootstrap-themed app).
        $this->assertStringContainsString('class="pagination', $html);
        $this->assertStringContainsString('page-item', $html);
        $this->assertStringContainsString('page-link', $html);
        $this->assertStringContainsString('page=2', $html);

        // 11 invoices → 10 on page 1, the 11th on page 2.
        // (Each row contains "Duplicate invoice INV-XXXX?" in its confirm dialog.)
        $this->assertSame(10, substr_count($html, 'Duplicate invoice '), 'page 1 must list exactly 10 invoices');

        $page2 = $this->actingAs($user)->get('/invoices?page=2')->assertStatus(200)->getContent();
        $this->assertSame(1, substr_count($page2, 'Duplicate invoice '), 'page 2 must list the remaining invoice');
    }

    public function test_pagination_preserves_filters_in_query_string(): void
    {
        // 11 invoices → pagination links render, so we can inspect their hrefs.
        $user = $this->userWithInvoices(11);

        $html = $this->actingAs($user)->get('/invoices?status=pending&q=INV')->assertStatus(200)->getContent();

        $this->assertStringContainsString('status=pending', $html);
        $this->assertStringContainsString('q=INV', $html);

        // The active filter is reflected in the select, the search box keeps its value.
        $this->assertMatchesRegularExpression('/<option value="pending"[^>]*selected/', $html);
        $this->assertStringContainsString('value="INV"', $html);
    }

    public function test_index_renders_responsive_filter_and_table_markup(): void
    {
        $user = $this->userWithInvoices(1);

        $html = $this->actingAs($user)->get('/invoices')->assertStatus(200)->getContent();

        // Content-sized wrapping filter bar (replaces fractional grid columns
        // that overflowed at md/lg) with a full-width action group on phones.
        $this->assertStringContainsString('filter-field--search', $html);
        $this->assertStringContainsString('filter-field', $html);
        $this->assertStringContainsString('filter-actions', $html);

        // Compact list table + mobile column/label behaviour.
        $this->assertStringContainsString('list-table', $html);
        $this->assertStringContainsString('d-none d-md-table-cell', $html);
        $this->assertStringContainsString('d-none d-sm-inline', $html);

        // Filter labels use the shared form-label styling.
        $this->assertStringContainsString('class="form-label"', $html);
    }

    public function test_flash_alerts_render_exactly_once(): void
    {
        $user = $this->userWithInvoices(1);

        $html = $this->actingAs($user)
            ->withSession(['success' => 'Invoice created.'])
            ->get('/invoices')
            ->assertStatus(200)
            ->getContent();

        // The layout already includes partials.alerts — the page must not
        // include it a second time (the message used to render twice).
        $this->assertSame(1, substr_count($html, 'alert-success'), 'flash alert must render exactly once');
    }

    public function test_empty_state_renders_with_filters_intact(): void
    {
        $user = User::factory()->create();

        $html = $this->actingAs($user)->get('/invoices')->assertStatus(200)->getContent();

        $this->assertStringContainsString('No invoices yet', $html);
        $this->assertStringContainsString('filter-actions', $html);
        $this->assertStringContainsString('filter-field--search', $html);
    }
}
