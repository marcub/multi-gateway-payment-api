<?php

declare(strict_types=1);

namespace App\Infrastructure\Gateway;

use App\Domain\Transaction\Contracts\GatewayClientInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class Gateway2Client implements GatewayClientInterface
{
    public function supports(string $gatewayName): bool
    {
        return in_array(strtolower($gatewayName), ['gateway2', 'gateway 2'], true);
    }

    public function charge(array $payload): array
    {
        $response = Http::withHeaders($this->authHeaders())
            ->post(config('services.gateway2.base_url') . '/transacoes', [
                'valor' => $payload['amount'],
                'nome' => $payload['name'],
                'email' => $payload['email'],
                'numeroCartao' => $payload['cardNumber'],
                'cvv' => $payload['cvv'],
            ])
            ->throw();

        $externalId = $response->json('id');

        if (!is_string($externalId) || trim($externalId) === '') {
            throw new RuntimeException('Gateway2 did not return a valid external id.');
        }

        return ['external_id' => $externalId];
    }

    public function refund(string $externalId): void
    {
        Http::withHeaders($this->authHeaders())
            ->post(config('services.gateway2.base_url') . '/transacoes/reembolso', [
                'id' => $externalId,
            ])
            ->throw();
    }

    private function authHeaders(): array
    {
        return [
            'Gateway-Auth-Token'  => config('services.gateway2.token'),
            'Gateway-Auth-Secret' => config('services.gateway2.secret'),
        ];
    }
}