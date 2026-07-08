<?php

namespace App\Http\Controllers;

use App\Models\GmailToken;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\Google;
use Illuminate\Support\Facades\Cache;

class GmailOAuthController extends Controller
{
    public function redirect()
    {
        $provider = $this->googleProvider();

        $authUrl = $provider->getAuthorizationUrl([
            'scope' => [
                'https://mail.google.com/',
                'email',
                'profile',
            ],
            'access_type' => 'offline',
            'prompt' => 'consent'
        ]);

        session(['oauth2state' => $provider->getState()]);

        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        $provider = $this->googleProvider();

        if (!$request->has('code') || $request->get('state') !== session('oauth2state')) {
            return redirect('/')->with('error', 'Estado inválido');
        }

        $token = $provider->getAccessToken('authorization_code', [
            'code' => $request->get('code')
        ]);

        GmailToken::updateOrCreate(
            ['email' => config('app.GMAIL_USER')],
            [
                'access_token'  => $token->getToken(),
                'refresh_token' => $token->getRefreshToken(),
                'expires'       => $token->getExpires(),
            ]
        );

        return redirect('/')->with('success', 'Token guardado correctamente');
    }

    protected function googleProvider()
    {
        return new Google([
            'clientId'     => config('app.GOOGLE_CLIENT_ID'),
            'clientSecret' => config('app.GOOGLE_CLIENT_SECRET'),
            'redirectUri'  => config('app.GOOGLE_REDIRECT_URI'),
        ]);
    }
}
