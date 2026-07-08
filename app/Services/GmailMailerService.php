<?php

namespace App\Services;

use App\Models\GmailToken;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Auth\XOAuth2Authenticator;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

class GmailMailerService
{
    /**
     * Summary of send
     * @param string $to
     * @param string $from_email
     * @param string $from_name
     * @param string $subject
     * @param string $body
     * @param mixed $others
     * @param string $attachment
     * @throws \Exception
     * @return void
     */
    public function send(
        string $to,
        string $subject,
        string $body,
        string $from_email = '',
        string $from_name = '',
        mixed $others = null,
        string $attachment = ''
    ): void {
        $username = $from_email ?: config('app.GMAIL_USER');
        $token = $this->getValidAccessToken($username); // Debe contener un objeto que tenga ->getToken()
        if (!$token) {
            throw new \Exception('No hay token de Gmail guardado');
        }

        ini_set('default_socket_timeout', 10);

        $from = new Address($username, $from_name);
        $accessToken = $token;

        // Crea el transportador SMTP
        $transport = new EsmtpTransport('smtp.gmail.com', 587);
        $transport->setUsername($username);
        $transport->setPassword($accessToken);

        // Establecer XOAUTH2 como autenticador
        $transport->setAuthenticators([new XOAuth2Authenticator()]);

        // Enviar correo
        $mailer = new Mailer($transport);

        $email = (new Email())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->html($body);

        if ($attachment && file_exists($attachment)) {
            $email->attachFromPath($attachment);
        }
        if ($others) {
            if (is_array($others)) {
                $email->cc(...$others);
            } else {
                $email->cc($others);
            }
        }

        try {
            logger()->info("✅ Enviando correo a: $to");
            $mailer->send($email);
            logger()->info("✅ Correo enviado a: $to");
        } catch (Throwable $e) {
            // Loguea el error sin romper el flujo
            logger()->error('Fallo al enviar correo: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getValidAccessToken($username): string
    {
        $record = GmailToken::where('email', $username)->first();

        if (!$record) {
            throw new \Exception('No hay token de Gmail guardado en base de datos.');
        }

        $token = new AccessToken([
            'access_token'  => $record->access_token,
            'refresh_token' => $record->refresh_token,
            'expires'       => $record->expires,
        ]);

        $provider = new Google([
            'clientId'     => config('app.GOOGLE_CLIENT_ID'),
            'clientSecret' => config('app.GOOGLE_CLIENT_SECRET'),
            'redirectUri'  => config('app.GOOGLE_REDIRECT_URI'),
        ]);

        if ($token->hasExpired()) {
            $newToken = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $token->getRefreshToken(),
            ]);

            $record->update([
                'access_token'  => $newToken->getToken(),
                'refresh_token' => $newToken->getRefreshToken() ?? $token->getRefreshToken(),
                'expires'       => $newToken->getExpires(),
            ]);

            return $newToken->getToken();
        }

        return $token->getToken();
    }
}
