<?php

namespace Tests\Feature;

use App\Models\BusinessProfile;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Give the user one row of every type of owned data.
     */
    private function seedOwnedData(User $user): void
    {
        $client = Client::query()->create([
            'user_id' => $user->id,
            'name'    => 'Acme Co',
            'email'   => 'billing@acme.test',
        ]);

        $invoice = Invoice::query()->create([
            'user_id'        => $user->id,
            'client_id'      => $client->id,
            'invoice_number' => 'INV-0001',
            'invoice_date'   => now()->toDateString(),
            'currency'       => 'USD',
            'status'         => Invoice::STATUS_PAID,
            'total'          => 150000,
        ]);

        Payment::query()->create([
            'user_id'    => $user->id,
            'invoice_id' => $invoice->id,
            'amount'     => 150000,
            'paid_on'    => now()->toDateString(),
            'method'     => Payment::METHOD_CASH,
        ]);

        $user->products()->create([
            'name'  => 'Consulting hour',
            'price' => 5000,
        ]);

        $user->businessProfile()->create([
            'business_name' => 'Acme Studio',
        ]);
    }

    public function test_account_page_requires_authentication(): void
    {
        $this->get(route('account.edit'))->assertRedirect(route('login'));
    }

    public function test_account_page_renders_the_three_cards(): void
    {
        $user = User::factory()->create();

        $html = $this->actingAs($user)
            ->get(route('account.edit'))
            ->assertStatus(200)
            ->getContent();

        $this->assertStringContainsString('Profile', $html);
        $this->assertStringContainsString('Change password', $html);
        $this->assertStringContainsString('Danger zone', $html);
        $this->assertStringContainsString($user->email, $html);
    }

    public function test_profile_can_be_updated_without_changing_email(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->put(route('account.profile.update'), [
            'name'  => 'New Name',
            'email' => $user->email,
            // No current_password needed when the email does not change.
        ]);

        $response->assertRedirect(route('account.edit'));
        $response->assertSessionHas('success');

        $this->assertSame('New Name', $user->fresh()->name);
    }

    public function test_email_change_requires_the_current_password(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($user)
            ->put(route('account.profile.update'), [
                'name'  => $user->name,
                'email' => 'new@example.com',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertSame('old@example.com', $user->fresh()->email);
    }

    public function test_email_change_rejects_a_wrong_current_password(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($user)
            ->put(route('account.profile.update'), [
                'name'             => $user->name,
                'email'            => 'new@example.com',
                'current_password' => 'not-the-password',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertSame('old@example.com', $user->fresh()->email);
    }

    public function test_email_can_be_changed_with_the_correct_current_password(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($user)->put(route('account.profile.update'), [
            'name'             => $user->name,
            'email'            => 'new@example.com',
            'current_password' => 'password',
        ])->assertRedirect(route('account.edit'));

        $this->assertSame('new@example.com', $user->fresh()->email);
    }

    public function test_password_can_be_changed_with_the_correct_current_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('account.password.update'), [
            'current_password'      => 'password',
            'password'              => 'brand-new-secret',
            'password_confirmation' => 'brand-new-secret',
        ]);

        $response->assertRedirect(route('account.edit'));
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('brand-new-secret', $user->fresh()->password));
    }

    public function test_password_change_rejects_a_wrong_current_password(): void
    {
        $user        = User::factory()->create();
        $originalHash = $user->password;

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password'      => 'wrong-password',
                'password'              => 'brand-new-secret',
                'password_confirmation' => 'brand-new-secret',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertSame($originalHash, $user->fresh()->password);
    }

    public function test_deleting_account_removes_only_the_users_own_data(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();

        $this->seedOwnedData($user);
        $this->seedOwnedData($other);

        $otherId = $other->id;

        $response = $this->actingAs($user)->delete(route('account.destroy'), [
            'password' => 'password',
            'confirm'  => '1',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertGuest();

        // The deleting user's rows are gone.
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('clients', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('invoices', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('payments', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('products', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('business_profiles', ['user_id' => $user->id]);

        // The other user's data is untouched.
        $this->assertDatabaseHas('users', ['id' => $otherId]);
        $this->assertDatabaseHas('clients', ['user_id' => $otherId]);
        $this->assertDatabaseHas('invoices', ['user_id' => $otherId]);
        $this->assertDatabaseHas('payments', ['user_id' => $otherId]);
        $this->assertDatabaseHas('products', ['user_id' => $otherId]);
        $this->assertDatabaseHas('business_profiles', ['user_id' => $otherId]);
    }

    public function test_deleting_account_requires_password_and_confirmation(): void
    {
        $user = User::factory()->create();

        // Missing password + confirmation.
        $this->actingAs($user)
            ->delete(route('account.destroy'))
            ->assertSessionHasErrors(['password', 'confirm']);

        // Wrong password, confirmation ticked.
        $this->actingAs($user)
            ->delete(route('account.destroy'), [
                'password' => 'nope',
                'confirm'  => '1',
            ])
            ->assertSessionHasErrors('password');

        // Correct password but confirmation not accepted.
        $this->actingAs($user)
            ->delete(route('account.destroy'), [
                'password' => 'password',
            ])
            ->assertSessionHasErrors('confirm');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
