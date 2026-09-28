<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_cannot_access_profile_page(): void
    {
        $response = $this->get(route('profile.edit'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_profile_page(): void
    {
        $user = User::factory()->create();

        // 1. Verify in English
        $responseEn = $this->actingAs($user)->withSession(['locale' => 'en'])->get(route('profile.edit'));
        $responseEn->assertStatus(200);
        $responseEn->assertSee($user->name);
        $responseEn->assertSee('Personal Information');
        $responseEn->assertSee('Security & Password');

        // 2. Verify in French
        $responseFr = $this->actingAs($user)->withSession(['locale' => 'fr'])->get(route('profile.edit'));
        $responseFr->assertStatus(200);
        $responseFr->assertSee('Informations Personnelles');
        $responseFr->assertSee('Sécurité & Mot de Passe');
    }

    public function test_user_can_update_name_and_contact_info(): void
    {
        $user = User::factory()->create([
            'name' => 'Ancien Nom',
            'phone' => '+237670000000',
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Nouveau Nom Modifié',
            'email' => $user->email,
            'phone' => '+237699112233',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('Nouveau Nom Modifié', $user->name);
        $this->assertEquals('+237699112233', $user->phone);
    }

    public function test_user_can_upload_and_delete_avatar_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();

        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);

        // Test removing avatar
        $removeResponse = $this->actingAs($user)->delete(route('profile.avatar.destroy'));
        $removeResponse->assertSessionHas('success');

        $user->refresh();
        $this->assertNull($user->avatar);
    }

    public function test_user_can_update_password_with_valid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'OldPassword123',
            'password' => 'NewSecurePassword2026',
            'password_confirmation' => 'NewSecurePassword2026',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('NewSecurePassword2026', $user->password));
    }

    public function test_user_cannot_update_password_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'WrongCurrentPassword',
            'password' => 'NewSecurePassword2026',
            'password_confirmation' => 'NewSecurePassword2026',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_user_cannot_update_password_with_weak_new_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123'),
        ]);

        // Too short (< 8 chars)
        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'OldPassword123',
            'password' => 'short1',
            'password_confirmation' => 'short1',
        ]);
        $response->assertSessionHasErrors('password');

        // Mismatched confirmation
        $response2 = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'OldPassword123',
            'password' => 'NewSecurePassword2026',
            'password_confirmation' => 'Different2026',
        ]);
        $response2->assertSessionHasErrors('password');
    }
}
