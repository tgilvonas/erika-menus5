<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'hasGoogleGeminiApiKey' => filled($request->user()->google_gemini_api_key),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $googleGeminiApiKey = $validated['google_gemini_api_key'] ?? null;
        $clearGoogleGeminiApiKey = $request->boolean('clear_google_gemini_api_key');

        unset($validated['google_gemini_api_key'], $validated['clear_google_gemini_api_key']);

        $request->user()->fill($validated);

        if ($clearGoogleGeminiApiKey) {
            $request->user()->google_gemini_api_key = null;
        } elseif (filled($googleGeminiApiKey)) {
            $request->user()->google_gemini_api_key = $googleGeminiApiKey;
        }

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
