<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test Google auth redirects to setup guide when API keys are not yet configured
     */
    public function test_google_auth_redirect_endpoint(): void
    {
        config(['services.google.client_id' => null]);

        $response = $this->get(route('auth.google'));
        $response->assertRedirect(route('auth.google.setup'));
    }

    /**
     * Test Google setup guide page renders with the correct redirect URI
     */
    public function test_google_auth_setup_guide_displays_redirect_uri(): void
    {
        $response = $this->get(route('auth.google.setup'));
        $response->assertStatus(200);
        $response->assertSee(url('/auth/google/callback'));
        $response->assertSee('google_client_id');
        $response->assertSee('google_client_secret');
    }

    /**
     * Test saving credentials via the setup guide form
     */
    public function test_google_setup_guide_saves_credentials(): void
    {
        $response = $this->post(route('auth.google.setup.save'), [
            'google_client_id' => '123456789-test-app.apps.googleusercontent.com',
            'google_client_secret' => 'GOCSPX-secret123456',
        ]);

        $response->assertRedirect(route('auth.google.setup'));
        $response->assertSessionHas('success');
        $this->assertEquals('123456789-test-app.apps.googleusercontent.com', config('services.google.client_id'));
        $this->assertEquals('GOCSPX-secret123456', config('services.google.client_secret'));
    }

    /**
     * Test Google auth redirects to Google OAuth consent screen when keys are configured
     */
    public function test_google_auth_redirects_to_google_when_configured(): void
    {
        config(['services.google.client_id' => 'real-google-client-id-12345']);
        config(['services.google.client_secret' => 'real-google-secret-67890']);

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('with')->with(['prompt' => 'select_account'])->andReturnSelf();
        $provider->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google'));
        $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    /**
     * Test 1-click Demo Google OAuth flow creates passenger and logs in
     */
    public function test_demo_google_login_authenticates_passenger(): void
    {
        $response = $this->get(route('auth.google.demo'));
        
        $response->assertRedirect(route('passenger.dashboard'));
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertEquals('google.passenger@transport.cm', $user->email);
        $this->assertEquals('passager', $user->role);
        $this->assertEquals('google_demo_1092837465', $user->google_id);
    }

    /**
     * Test Socialite Google OAuth callback creates new passenger account and logs in
     */
    public function test_socialite_google_callback_creates_and_authenticates_user(): void
    {
        config(['services.google.client_id' => 'test-google-client-id']);
        config(['services.google.client_secret' => 'test-google-client-secret']);

        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-unique-id-998877');
        $abstractUser->shouldReceive('getName')->andReturn('Samuel Eto\'o');
        $abstractUser->shouldReceive('getEmail')->andReturn('samuel.etoo@fecafoot.cm');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/a/photo.jpg');
        $abstractUser->shouldReceive('getNickname')->andReturn(null);

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('passenger.dashboard'));
        $this->assertAuthenticated();

        $createdUser = User::where('google_id', 'google-unique-id-998877')->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals('samuel.etoo@fecafoot.cm', $createdUser->email);
        $this->assertEquals('passager', $createdUser->role);
        $this->assertEquals('Samuel Eto\'o', $createdUser->name);
    }

    /**
     * Test Socialite Google OAuth links existing account by email without duplication
     */
    public function test_socialite_google_callback_links_existing_user_by_email(): void
    {
        config(['services.google.client_id' => 'test-google-client-id']);
        config(['services.google.client_secret' => 'test-google-client-secret']);

        // Existing passenger from seeder
        $existingPassenger = User::where('email', 'passenger@transport.cm')->first();
        $this->assertNotNull($existingPassenger);
        $this->assertNull($existingPassenger->google_id);

        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-linked-id-12345');
        $abstractUser->shouldReceive('getName')->andReturn('Paul Biya Passenger');
        $abstractUser->shouldReceive('getEmail')->andReturn('passenger@transport.cm');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/a/avatar.jpg');
        $abstractUser->shouldReceive('getNickname')->andReturn(null);

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('passenger.dashboard'));
        $this->assertAuthenticatedAs($existingPassenger);

        $existingPassenger->refresh();
        $this->assertEquals('google-linked-id-12345', $existingPassenger->google_id);
    }

    /**
     * Test Google OAuth callback handles user cancellation gracefully
     */
    public function test_google_callback_handles_user_cancellation(): void
    {
        $response = $this->get(route('auth.google.callback', ['error' => 'access_denied']));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('info');
        $this->assertGuest();
    }
}
