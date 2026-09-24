<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form.
     */
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's name, email, phone and avatar image.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:25',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'name.required' => __('Le nom est obligatoire.'),
            'email.required' => __('L\'adresse email est obligatoire.'),
            'email.email' => __('L\'adresse email n\'est pas valide.'),
            'email.unique' => __('Cette adresse email est déjà utilisée par un autre compte.'),
            'avatar.image' => __('Le fichier sélectionné doit être une image.'),
            'avatar.mimes' => __('Format d\'image accepté : JPEG, PNG, JPG ou WebP.'),
            'avatar.max' => __('La taille de l\'image ne doit pas dépasser 2 Mo.'),
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar if present on public disk
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar in public/avatars
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->save();

        return back()->with('success', __('Votre profil a été mis à jour avec succès !'));
    }

    /**
     * Update the user's password securely.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers(), 'confirmed'],
        ], [
            'current_password.required' => __('Veuillez renseigner votre mot de passe actuel.'),
            'current_password.current_password' => __('Le mot de passe actuel est incorrect.'),
            'password.required' => __('Le nouveau mot de passe est obligatoire.'),
            'password.min' => __('Le nouveau mot de passe doit comporter au moins 8 caractères.'),
            'password.letters' => __('Le nouveau mot de passe doit contenir au moins une lettre.'),
            'password.numbers' => __('Le nouveau mot de passe doit contenir au moins un chiffre.'),
            'password.confirmed' => __('La confirmation du nouveau mot de passe ne correspond pas.'),
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', __('Votre mot de passe a été modifié avec succès !'));
    }

    /**
     * Remove the user's profile avatar.
     */
    public function removeAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->avatar = null;
        $user->save();

        return back()->with('success', __('Votre photo de profil a été supprimée.'));
    }
}
