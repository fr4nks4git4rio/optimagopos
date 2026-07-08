<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver("$provider-login")
            ->with([
                'prompt' => 'select_account', // fuerza a elegir cuenta cada vez
            ])
            ->redirect();
    }

    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver("$provider-login")
                // ->stateless()
                ->user();
        } catch (\Exception $e) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'social' => "No se pudo autenticar con $provider."
                ]);
        }

        if (!$socialUser->getEmail()) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'social' => "$provider no devolvió un email válido."
                ]);
        }

        $user = User::where('email', $socialUser->getEmail())->first();

        // ❌ Usuario NO existe
        if (!$user) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'social' => 'Tu cuenta no está registrada en el sistema.'
                ]);
        }

        if ($user->cliente_id && $user->suscripciones_activas()->count() == 0) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'social' => __('auth.subscription_failed')
                ]);
        }

        // ✅ Usuario existe → login
        Auth::login($user, true);

        return redirect()->route('home');
    }
}
