<?php

namespace Tests\Feature;

use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_with_seeded_data(): void
    {
        $this->seed(DemoSeeder::class);

        $response = $this->actingAs(
            \App\Models\User::where('email', 'demo@invoiceflow.test')->firstOrFail()
        )->get('/dashboard');

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

    public function test_dashboard_renders_empty_state_for_new_user(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $this->assertStringContainsString('No invoices yet', $response->getContent());
        $this->assertStringContainsString('No activity yet', $response->getContent());
    }

    public function test_dashboard_chart_endpoint_returns_json(): void
    {
        $this->seed(DemoSeeder::class);

        $response = $this->actingAs(
            \App\Models\User::where('email', 'demo@invoiceflow.test')->firstOrFail()
        )->getJson('/dashboard/chart?range=7d');

        $response->assertStatus(200);
        $response->assertJsonStructure(['labels', 'data', 'range', 'total_for_range']);
    }
}
