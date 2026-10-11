<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every public marketing page should render with the shared layout.
     */
    public function test_public_pages_render(): void
    {
        $pages = [
            'features' => 'Everything you need to invoice',
            'pricing'  => 'Simple, honest pricing',
            'help'     => 'How can we help?',
            'contact'  => 'Get in touch',
            'privacy'  => 'Privacy Policy',
            'terms'    => 'Terms of Service',
        ];

        foreach ($pages as $route => $needle) {
            $this->get(route($route))
                ->assertStatus(200)
                ->assertSee($needle);
        }
    }

    public function test_navbar_and_footer_link_to_the_new_pages(): void
    {
        $html = $this->get(route('home'))->assertStatus(200)->getContent();

        $this->assertStringContainsString(route('features'), $html);
        $this->assertStringContainsString(route('pricing'), $html);
        $this->assertStringContainsString(route('help'), $html);
        $this->assertStringContainsString(route('contact'), $html);
    }

    public function test_contact_form_stores_a_message(): void
    {
        $response = $this->from(route('contact'))->post(route('contact.send'), [
            'name'    => 'Ayesha Malik',
            'email'   => 'ayesha@example.com',
            'message' => 'I would like to know more about the Pro plan, please.',
        ]);

        $response->assertRedirect(route('contact'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'name'  => 'Ayesha Malik',
            'email' => 'ayesha@example.com',
        ]);
    }

    public function test_contact_form_validates_required_fields(): void
    {
        $this->from(route('contact'))
            ->post(route('contact.send'), [])
            ->assertSessionHasErrors(['name', 'email', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_honeypot_silently_discards_spam(): void
    {
        $response = $this->from(route('contact'))->post(route('contact.send'), [
            'name'    => 'Spam Bot',
            'email'   => 'bot@example.com',
            'message' => 'Buy cheap things at https://example.com right now!',
            'website' => 'http://spam.example.com',
        ]);

        // Looks successful to the bot, but nothing is stored.
        $response->assertRedirect(route('contact'));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_form_is_throttled_to_five_per_minute(): void
    {
        $payload = [
            'name'    => 'Test User',
            'email'   => 'test@example.com',
            'message' => 'A perfectly valid message that is long enough.',
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->from(route('contact'))
                ->post(route('contact.send'), $payload)
                ->assertStatus(302);
        }

        // The sixth submission within the same minute is rate limited.
        $this->from(route('contact'))
            ->post(route('contact.send'), $payload)
            ->assertStatus(429);
    }

    public function test_sitemap_lists_public_pages(): void
    {
        $response = $this->get(route('sitemap'))->assertStatus(200);

        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');
        $response->assertSee('<urlset', false);
        $response->assertSee(route('features'), false);
        $response->assertSee(route('pricing'), false);
    }

    public function test_missing_page_shows_the_custom_404(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertStatus(404)
            ->assertSee("We couldn't find that page", false);
    }
}
