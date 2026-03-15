<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateway;

use App\Domain\Transaction\Contracts\GatewayClientInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class Gateway1Client implements GatewayClientInterface
{
    public function supports(string $gatewayName): bool
    {
        return in_array(strtolower($gatewayName), ['gateway1', 'gateway 1'], true);
    }

    public function charge(array $payload): array
    {
        $response = Http::withToken($this->getAccessToken())
            ->post(config('services.gateway1.base_url') . '/transactions', [
                'amount' => $payload['amount'],
                'name' => $payload['name'],
                'email' => $payload['email'],
                'cardNumber' => $payload['cardNumber'],
                'cvv' => $payload['cvv'],
            ])
            ->throw();

        $externalId = $response->json('id');

        if (!is_string($externalId) || trim($externalId) === '') {
            throw new RuntimeException('Gateway1 did not return a valid external id.');
        }

        return ['external_id' => $externalId];
    }

    public function refund(string $externalId): void
    {
        Http::withToken($this->getAccessToken())
            ->post(config('services.gateway1.base_url') . "/transactions/{$externalId}/charge_back")
            ->throw();
    }

    private function getAccessToken(): string
    {
        $response = Http::post(config('services.gateway1.base_url') . '/login', [
            'email' => config('services.gateway1.email'),
            'token' => config('services.gateway1.token'),
        ])->throw();

        $accessToken = $response->json('token');

        if (!is_string($accessToken) || trim($accessToken) === '') {
            throw new RuntimeException('Gateway1 authentication failed.');
        }

        return $accessToken;
    }
}