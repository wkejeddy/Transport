<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class PasswordSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test that passenger registration rejects short or weak passwords.
     */
    public function test_registration_rejects_weak_passwords(): void
    {
        // 1. Too short (< 8 chars)
        $response = $this->post(route('register.passenger.submit'), [
            'name' => 'Paul Test',
            'email' => 'paul.test@example.cm',
            'phone' => '+237699001122',
            'password' => 'short1',
            'password_confirmation' => 'short1',
        ]);
        $response->assertSessionHasErrors(['password']);

        // 2. Letters only, no numbers
        $response2 = $this->post(route('register.passenger.submit'), [
            'name' => 'Paul Test',
            'email' => 'paul.test@example.cm',
            'phone' => '+237699001122',
            'password' => 'onlyletterslong',
            'password_confirmation' => 'onlyletterslong',
        ]);
        $response2->assertSessionHasErrors(['password']);

        // 3. Mismatched confirmation
        $response3 = $this->post(route('register.passenger.submit'), [
            'name' => 'Paul Test',
            'email' => 'paul.test@example.cm',
            'phone' => '+237699001122',
            'password' => 'SecurePass2026',
            'password_confirmation' => 'DifferentPass2026',
        ]);
        $response3->assertSessionHasErrors(['password']);
    }

    /**
     * Test that registration succeeds with strong password and hashes it securely with Bcrypt.
     */
    public function test_registration_accepts_strong_password_and_hashes_it(): void
    {
        $response = $this->post(route('register.passenger.submit'), [
            'name' => 'Carole Mbia',
            'email' => 'carole.mbia@example.cm',
            'phone' => '+237699554433',
            'password' => 'SecurePass2026!',
            'password_confirmation' => 'SecurePass2026!',
        ]);

        $response->assertRedirect(route('passenger.dashboard'));
        $user = User::where('email', 'carole.mbia@example.cm')->first();
        $this->assertNotNull($user);

        // Verify password is not plain text and matches Hash::check
        $this->assertNotEquals('SecurePass2026!', $user->password);
        $this->assertTrue(Hash::check('SecurePass2026!', $user->password));
    }

    /**
     * Test brute-force throttle on login: 5 failed attempts trigger rate-limiting.
     */
    public function test_login_rate_limiting_locks_out_after_consecutive_failed_attempts(): void
    {
        $email = 'brute.target@example.cm';

        // Attempt 5 incorrect logins
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->post(route('login.submit'), [
                'email' => $email,
                'password' => 'wrongpassword' . $i,
            ]);
            $response->assertSessionHasErrors('email');
        }

        // 6th attempt should be blocked by RateLimiter
        $blockedResponse = $this->post(route('login.submit'), [
            'email' => $email,
            'password' => 'wrongpassword6',
        ]);

        $blockedResponse->assertSessionHasErrors('email');
        $errorMessage = session('errors')->first('email');
        $this->assertTrue(
            str_contains($errorMessage, 'patienter') || str_contains($errorMessage, 'wait'),
            "Expected rate limiting message to mention waiting/patienter, got: {$errorMessage}"
        );
    }
}
