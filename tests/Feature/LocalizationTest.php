<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_locale_switcher_changes_language_to_english(): void
    {
        $response = $this->get(route('lang.swap', 'en'));
        $response->assertRedirect();
        $response->assertSessionHas('locale', 'en');
        $response->assertCookie('realvoyage_locale', 'en');

        // Verify homepage content is in English when locale session is active
        $page = $this->withSession(['locale' => 'en'])->get(route('home'));
        $page->assertStatus(200);
        $page->assertSee('3. Station Pickup:');
        $page->assertSee('2. SMS OTP Code:');
        $page->assertSee('Available seats');
    }

    public function test_locale_switcher_changes_language_to_french(): void
    {
        $response = $this->get(route('lang.swap', 'fr'));
        $response->assertRedirect();
        $response->assertSessionHas('locale', 'fr');
        $response->assertCookie('realvoyage_locale', 'fr');

        $page = $this->withSession(['locale' => 'fr'])->get(route('home'));
        $page->assertStatus(200);
        $page->assertSee('3. Retrait en Gare :');
        $page->assertSee('2. Code OTP SMS :');
        $page->assertSee('Places libres');
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $response = $this->get('/lang/es');
        $response->assertStatus(404);
    }

    public function test_admin_dashboard_translates_between_languages(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        // In English
        $responseEn = $this->actingAs($admin)->withSession(['locale' => 'en'])->get(route('admin.dashboard'));
        $responseEn->assertStatus(200);
        $responseEn->assertSee('Consolidated Revenue');
        $responseEn->assertSee('Station Managers & Operations Teams');
        $responseEn->assertSee('National & Regional Supervision');

        // In French
        $responseFr = $this->actingAs($admin)->withSession(['locale' => 'fr'])->get(route('admin.dashboard'));
        $responseFr->assertStatus(200);
        $responseFr->assertSee("Chiffre d'Affaires Consolidé");
        $responseFr->assertSee('Chefs de Gare & Équipes');
        $responseFr->assertSee('Supervision Nationale & Régionale');
    }

    public function test_manager_dashboard_translates_between_languages(): void
    {
        $manager = \App\Models\User::where('role', 'manager')->first();
        $this->assertNotNull($manager);

        // In English
        $responseEn = $this->actingAs($manager)->withSession(['locale' => 'en'])->get(route('manager.dashboard'));
        $responseEn->assertStatus(200);
        $responseEn->assertSee('Station & Operations Dashboard');
        $responseEn->assertSee('Check-in & Boarding');

        // In French
        $responseFr = $this->actingAs($manager)->withSession(['locale' => 'fr'])->get(route('manager.dashboard'));
        $responseFr->assertStatus(200);
        $responseFr->assertSee('Tableau de Bord Gare & Exploitation');
        $responseFr->assertSee('Contrôle & Embarquement');
    }

    public function test_server_renders_dark_theme_from_cookie(): void
    {
        // Dark theme cookie
        $responseDark = $this->withCookie('realvoyage_theme', 'dark')->get(route('home'));
        $responseDark->assertStatus(200);
        $responseDark->assertSee('data-theme="dark"', false);
        $responseDark->assertSee('class="dark"', false);

        // Light theme cookie
        $responseLight = $this->withCookie('realvoyage_theme', 'light')->get(route('home'));
        $responseLight->assertStatus(200);
        $responseLight->assertSee('data-theme="light"', false);
    }

    public function test_trip_booking_page_translates_3d_coach_elements(): void
    {
        $passenger = \App\Models\User::where('role', 'passager')->first() ?? \App\Models\User::factory()->create(['role' => 'passager']);
        $this->assertNotNull($passenger);
        $trip = \App\Models\Trip::first();
        $this->assertNotNull($trip);

        // English view
        $responseEn = $this->actingAs($passenger)->withSession(['locale' => 'en'])->get(route('trips.show', $trip));
        $responseEn->assertStatus(200);
        $responseEn->assertSee('VIP Grand Touring Coach');
        $responseEn->assertSee('Guaranteed on-time departure');
        $responseEn->assertSee('Seats Selected on 3D Model');

        // French view
        $responseFr = $this->actingAs($passenger)->withSession(['locale' => 'fr'])->get(route('trips.show', $trip));
        $responseFr->assertStatus(200);
        $responseFr->assertSee('Autocar VIP Grand Tourisme');
        $responseFr->assertSee('Départ ponctuel garanti');
        $responseFr->assertSee('Fauteuils Réservés sur le Modèle 3D');
    }
}
