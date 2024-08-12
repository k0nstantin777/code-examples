<?php

namespace App\Services\FFC;

use App\Services\FFC\Exceptions\JsonRpcErrorResponseException;
use Datto\JsonRpc\Http\Client;
use Datto\JsonRpc\Http\Exceptions\HttpException;
use Datto\JsonRpc\Responses\ErrorResponse;
use ErrorException;
use Illuminate\Validation\ValidationException;
use JsonException;

class JsonRpcClient
{
    protected function getClient(): Client
    {
        return app(Client::class, [
            'uri' =>  config('services.ffc_json_rpc.uri')
        ]);
    }

    /**
     * @throws JsonRpcErrorResponseException|ValidationException
     */
    public function send(string $method, array $params)
    {
        $client = $this->getClient();

        try {
            $client->query($method, $params, $response);

            $client->send();

            if ($response instanceof ErrorResponse) {
                $this->handleErrorResponse($response);
            }

            return $response;
        } catch (HttpException | ErrorException | JsonException $e) {
            throw new JsonRpcErrorResponseException($e);
        }
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws JsonException
     */
    private function handleErrorResponse(ErrorResponse $response): void
    {
        if ($response->getCode() === -32602 && isset($response->getData()['violations'])) {
            throw ValidationException::withMessages($response->getData()['violations']);
        }

        throw new JsonRpcErrorResponseException(json_encode([
            'message' => $response->getMessage(),
            'code' => $response->getCode(),
            'data' => $response->getData(),
        ], JSON_THROW_ON_ERROR));
    }
}
