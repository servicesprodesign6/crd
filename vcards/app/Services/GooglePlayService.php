<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GooglePlayService
{
    protected $credentials;
    public function __construct()
    {
        $this->credentials = json_decode(
            file_get_contents(storage_path('app/google-service-account.json')),
            true
        );
    }

    protected function getAccessToken()
    {
        return Cache::remember('google_play_access_token', 3500, function () {
            return $this->generateAccessToken();
        });
    }

    protected function generateAccessToken()
    {
        $now = time();

        $payload = [
            'iss'   => $this->credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/androidpublisher',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'exp'   => $now + 3600,
            'iat'   => $now,
        ];

        $jwt = $this->generateJWT($payload);

        $response = Http::asForm()->post(
            'https://oauth2.googleapis.com/token',
            [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]
        );

        if (!$response->successful()) {
            throw new Exception('Google OAuth token generation failed.');
        }

        return $response->json('access_token');
    }

    protected function generateJWT($payload)
    {
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $base64UrlEncode = fn ($data) =>
            rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');

        $segments = [];
        $segments[] = $base64UrlEncode($header);
        $segments[] = $base64UrlEncode($payload);

        $signingInput = implode('.', $segments);

        if (!openssl_sign(
            $signingInput,
            $signature,
            $this->credentials['private_key'],
            'sha256WithRSAEncryption'
        )) {
            throw new Exception('JWT signing failed.');
        }

        $segments[] = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return implode('.', $segments);
    }

    protected function makeRequest($url)
    {
        $response = Http::withToken($this->getAccessToken())->get($url);

        // If token expired, clear cache & retry once (SDK behavior)
        if ($response->status() === 401) {
            Cache::forget('google_play_access_token');

            $response = Http::withToken($this->getAccessToken())->get($url);
        }

        if (!$response->successful()) {
            throw new Exception('Google Play API request failed.');
        }

        return $response->json();
    }

    public function getSubscriptionPurchase($packageName, $subscriptionId, $token)
    {
        $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/subscriptions/{$subscriptionId}/tokens/{$token}";

        return $this->makeRequest($url);
    }

    // Example method for an in-app product purchase
    public function getInAppProductPurchase($packageName, $productId, $token)
    {
        $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/products/{$productId}/tokens/{$token}";

        return $this->makeRequest($url);
    }
}
