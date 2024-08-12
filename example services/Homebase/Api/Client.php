<?php

namespace App\Services\Homebase\Api;

use App\Services\Homebase\Api\Exceptions\HomebaseApiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use JsonException;
use Symfony\Component\HttpFoundation\Request;

class Client
{
    public function __construct(protected array $config)
    {
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @throws HomebaseApiException
     */
    public function get(string $uri, array $params = []): array
    {
        return $this->sendRequest(Request::METHOD_GET, $uri, ['query' => http_build_query($params)]);
    }

    /**
     * @throws HomebaseApiException
     */
    public function post(string $uri, array $params = []): array
    {
        return $this->sendRequest(Request::METHOD_POST, $uri, ['form_params' => $params]);
    }

    /**
     * @throws HomebaseApiException
     */
    public function put(string $uri, array $params = []): array
    {
        return $this->sendRequest(Request::METHOD_PUT, $uri, ['form_params' => $params]);
    }

    /**
     * @return PendingRequest
     */
    protected function getClient(): PendingRequest
    {
        return Http::baseUrl($this->config['base_uri'])
            ->withToken($this->config['api_key'])
            ->accept('application/vnd.homebase-v1+json');
    }

    /**
     * @param Response $response
     * @return array
     * @throws JsonException
     * @throws HomebaseApiException
     */
    protected function getResponse(Response $response): array
    {
        $responseData = json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR);

        if ($response->status() !== 200) {
            throw new HomebaseApiException(json_encode($responseData, JSON_THROW_ON_ERROR));
        }

        return $responseData;
    }

    /**
     * @throws HomebaseApiException
     */
    protected function sendRequest(string $method, string $url, array $params): array
    {
        try {
            if (RateLimiter::tooManyAttempts(self::class, $this->config['requests_per_minute'])) {
                $seconds = RateLimiter::availableIn(self::class);

                sleep($seconds);
            }
            RateLimiter::hit(self::class);
            return $this->getResponse($this->getClient()->send($method, $url, $params));
        } catch (\Exception $e) {
            throw new HomebaseApiException($e->getMessage());
        }
    }
}
