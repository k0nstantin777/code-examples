<?php

namespace App\Services\CryptoWallets;

use App\Services\CryptoWallets\Exceptions\CryptoWalletsApiException;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;
use JsonException;

class Client
{
    protected array $config;

    private const REQUEST_PER_MINUTE = 60;

    /**
     * Client constructor.
     */
    public function __construct()
    {
        $this->config = config('services.crypto_wallets', []);
    }

    /**
     * @param $uri
     * @param array $params
     * @return array
     * @throws CryptoWalletsApiException
     */
    public function get($uri, array $params = []): array
    {
        return $this->sendRequest(Request::METHOD_GET, $uri, $params);
    }

    /**
     * @throws CryptoWalletsApiException
     */
    public function post($uri, array $params = []): array
    {
        return $this->sendRequest(Request::METHOD_POST, $uri, $params);
    }

    /**
     * @throws CryptoWalletsApiException
     */
    public function delete($uri, array $params = []): array
    {
        return $this->sendRequest(Request::METHOD_DELETE, $uri, $params);
    }

    /**
     * @return PendingRequest
     */
    protected function getClient(): PendingRequest
    {
        return Http::baseUrl($this->config['base_uri'])
            ->withHeaders([
                'authorization'=>  'Bearer ' . $this->config['api_key'],
            ]);
    }

    /**
     * @param Response $response
     * @return array
     * @throws JsonException
     * @throws CryptoWalletsApiException
     */
    protected function getResponse(Response $response): array
    {
        $responseData = json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR);

        if (isset($responseData['error'])
        ) {
            throw new CryptoWalletsApiException(json_encode($responseData['error'], JSON_THROW_ON_ERROR));
        }

        return $responseData;
    }

    /**
     * @throws CryptoWalletsApiException
     */
    protected function sendRequest($method, $url, $params): array
    {
        try {
            if (RateLimiter::tooManyAttempts($url, self::REQUEST_PER_MINUTE)) {
                $seconds = RateLimiter::availableIn($url);
                sleep($seconds);
            }

            RateLimiter::hit($url);

            return $this->getResponse($this->getClient()->send($method, $url, [
                'json' => $params
            ]));
        } catch (\Exception $e) {
            Log::channel('crypto-wallets-log')->error($e->getMessage(), ['exception' => $e]);
            throw new CryptoWalletsApiException($e->getMessage());
        }
    }
}
