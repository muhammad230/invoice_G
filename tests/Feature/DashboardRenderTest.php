<?php

namespace Tests\Feature;

use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRenderTest extends TestCase
{
    use RefreshDatabase;

    /** Demo user from DemoSeeder. */
    private function demoUser(): \App\Models\User
    {
        return \App\Models\User::where('email', 'demo@invoiceflow.test')->firstOrFail();
    }

    public function test_dashboard_renders_with_seeded_data(): void
    {
        $this->seed(DemoSeeder::class);

        $response = $this->actingAs($this->demoUser())->get('/dashboard');

        $response->assertStatus(200);

        $html = $response->getContent();

        // Activity items must render real markup, not escaped entities.
        $this->assertStringNotContainsString('&lt;strong&gt;', $html);
        $this->assertStringContainsString('Recent Activity', $html);

        // Range switcher + charts present.
        $this->assertStringContainsString('dash-chart-btn', $html);
        $this->assertStringContainsString('id="lineChart"', $html);
        $this->assertStringContainsString('id="doughnutChart"', $html);

        // Stat cards.
        $this->assertStringContainsString('Total Invoices', $html);
        $this->assertStringContainsString('Pending Payments', $html);

        // Chart JSON data attributes must be valid JSON (no raw quotes breaking out).
        $this->assertSame(1, preg_match('/data-labels=\'(\[.*?\])\'/s', $html, $m));
        $this->assertNotNull(json_decode($m[1], true));

        // (1) Currency selector replaced the "multiple currencies" banner.
        $this->assertStringNotContainsString('You use multiple currencies', $html);
        $this->assertStringContainsString('dash-currency-btn', $html);
        // Only currencies the user actually has invoices in are listed.
        foreach (['USD', 'GBP', 'PKR', 'EUR'] as $code) {
            $this->assertStringContainsString('currency=' . $code, $html);
        }

        // (3) In-chart empty state instead of a flat line (default range has
        // no paid invoices for the default currency).
        $this->assertStringContainsString('id="chartOverlay"', $html);
        $this->assertStringContainsString('No paid invoices in this period', $html);
        $this->assertStringContainsString('data-currency="', $html);

        // (4) Recent activity: max 5 entries + "View all" link.
        $this->assertSame(5, substr_count($html, 'dash-activity__item'), 'activity must show at most 5 entries');
        $this->assertStringContainsString('View all', $html);

        // (6) Top bar: space-between row, right group (bell + user menu),
        // dropdown-menu-end, role text, logout form with CSRF.
        $this->assertStringContainsString('app-topbar__row', $html);
        $this->assertStringContainsString('app-topbar__right', $html);
        $this->assertStringContainsString('dropdown-menu-end', $html);
        $this->assertStringContainsString('Business Owner', $html);
        $this->assertStringContainsString('Log out', $html);
        $this->assertMatchesRegularExpression('/<form method="POST"[^>]*action="[^"]*logout"[^>]*>\s*<input type="hidden" name="_token"/', $html);

        // Topbar order: burger → logo → search → … → bell → demo-user menu,
        // with the account always pinned as the last (far right) item.
        $logoPos   = strpos($html, 'sidebar-brand__icon--sm');
        $searchPos = strpos($html, 'app-topbar__search');
        $bellPos   = strpos($html, 'app-topbar__iconbtn');
        $avatarPos = strpos($html, 'app-topbar__avatarbtn');
        $this->assertNotFalse($logoPos);
        $this->assertNotFalse($searchPos);
        $this->assertNotFalse($bellPos);
        $this->assertNotFalse($avatarPos);
        $this->assertTrue($logoPos < $searchPos, 'logo must come before the search box');
        $this->assertTrue($searchPos < $bellPos, 'bell must come after the search box');
        $this->assertTrue($bellPos < $avatarPos, 'demo-user menu must be the last item');
        $this->assertSame(1, substr_count($html, 'sidebar-brand__icon--sm'), 'exactly one topbar logo');
    }

    public function test_currency_selector_filters_stats_and_charts_to_one_currency(): void
    {
        $this->seed(DemoSeeder::class);
        $user = $this->demoUser();

        // DemoSeeder invoice counts per currency:
        // USD 2 (1 paid, 1 pending) · GBP 2 (1 paid, 1 overdue)
        // PKR 1 (pending)           · EUR 1 (overdue)
        $expectedDonut = [
            'USD' => [1, 1, 0],
            'GBP' => [1, 0, 1],
            'PKR' => [0, 1, 0],
            'EUR' => [0, 0, 1],
        ];

        foreach ($expectedDonut as $code => $counts) {
            $html = $this->actingAs($user)->get('/dashboard?currency=' . $code)->assertStatus(200)->getContent();

            // Active currency is reflected on the chart canvas…
            $this->assertStringContainsString('data-currency="' . $code . '"', $html);

            // …and the donut + legend are scoped to that currency only.
            $this->assertSame(
                1,
                preg_match('/id="doughnutChart"[^>]*data-values=\'(\[[^\]]*\])\'/s', $html, $match),
                "donut missing for {$code}"
            );
            $this->assertSame($counts, json_decode($match[1], true), "donut counts must be scoped to {$code}");

            // Paid + Pending + Overdue must add up to Total Invoices.
            $this->assertSame(
                1,
                preg_match('/dash-donut-center__total">([\d,]+)</', $html, $totalMatch),
                "donut total missing for {$code}"
            );
            $total = (int) str_replace(',', '', $totalMatch[1]);
            $this->assertSame(array_sum($counts), $total, "counts must add up to the total for {$code}");
            $this->assertSame(
                $total,
                $user->invoices()->where('currency', $code)->count(),
                "total must equal ALL invoices in {$code}"
            );
        }

        // The old banner is gone; selector stays in the query string.
        $html = $this->actingAs($user)->get('/dashboard?currency=PKR')->getContent();
        $this->assertStringNotContainsString('You use multiple currencies', $html);
        $this->assertStringContainsString('currency=USD', $html);
        $this->assertStringContainsString('dropdown-item active', $html);

        // Unknown currency falls back to the most-used one (no error).
        $this->actingAs($user)->get('/dashboard?currency=ZZZ')->assertStatus(200);
    }

    public function test_chart_endpoint_uses_requested_currency_and_total_matches_plotted_values(): void
    {
        $this->seed(DemoSeeder::class);
        $user = $this->demoUser();

        // USD: one paid invoice (1y range) → real values, "$" symbol.
        $response = $this->actingAs($user)->getJson('/dashboard/chart?range=1y&currency=USD');
        $response->assertStatus(200);
        $json = $response->json();

        $this->assertSame('USD', $json['currency']);
        $this->assertStringStartsWith('$', $json['total_for_range']);

        // "Paid $X" must equal the sum of the plotted values.
        $plotted = array_sum(array_map('floatval', $json['data']));
        $figure  = (float) str_replace(',', '', substr($json['total_for_range'], 1));
        $this->assertEqualsWithDelta($figure, $plotted, 0.01, 'Paid figure must equal the sum of plotted values');

        // PKR: no paid invoices at all → 0 total, "Rs" symbol.
        $pkr = $this->actingAs($user)->getJson('/dashboard/chart?range=1y&currency=PKR')->json();
        $this->assertSame('PKR', $pkr['currency']);
        $this->assertSame('Rs0.00', $pkr['total_for_range']);
        $this->assertEqualsWithDelta(0.0, array_sum($pkr['data']), 0.01);

        // Unknown currency falls back safely.
        $fallback = $this->actingAs($user)->getJson('/dashboard/chart?range=7d&currency=NOPE');
        $fallback->assertStatus(200);
        $this->assertContains($fallback->json('currency'), ['USD', 'GBP', 'PKR', 'EUR']);
    }

    public function test_dashboard_renders_empty_state_for_new_user(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $html = $response->getContent();
        $this->assertStringContainsString('No invoices yet', $html);
        $this->assertStringContainsString('No activity yet', $html);
        // No invoices → no currency selector at all.
        $this->assertStringNotContainsString('dash-currency-btn', $html);
    }

    public function test_dashboard_chart_endpoint_returns_json(): void
    {
        $this->seed(DemoSeeder::class);

        $response = $this->actingAs($this->demoUser())->getJson('/dashboard/chart?range=7d');

        $response->assertStatus(200);
        $response->assertJsonStructure(['labels', 'data', 'range', 'total_for_range', 'currency']);
    }
}
