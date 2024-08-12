<?php

namespace App\Services\Exchanger;

use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Language\LanguageService;
use Datto\JsonRpc\Http\Client;
use Datto\JsonRpc\Http\Exceptions\HttpException;
use Datto\JsonRpc\Responses\ErrorResponse;
use ErrorException;
use Illuminate\Validation\ValidationException;
use JsonException;

class JsonRpcClient
{
    public function __construct(
        private readonly LanguageService $languageService,
    ) {
    }

    /**
     * @throws JsonRpcErrorResponseException|ValidationException
     */
    public function send(string $method, array $params)
    {
        $client = $this->getClient();

        try {
            $this->addLangParam($params);

            $client->query($method, $params, $response);

            $client->send();

            if ($response instanceof ErrorResponse) {
                $this->handleErrorResponse($response);
            }

            return $response;
        } catch (HttpException|ErrorException|JsonException $e) {
            throw new JsonRpcErrorResponseException($e);
        }
    }

    private function addLangParam(array &$params) : void
    {
        $params['lang'] = $this->languageService->getAppLanguage();
    }

    protected function getClient() : Client
    {
        return app(Client::class, [
            'uri' =>  config('services.exchanger.base_url') . config('services.exchanger.json_rpc_path')
        ]);
    }

    /**
     * @throws JsonRpcErrorResponseException
     * @throws ValidationException
     * @throws JsonException
     */
    private function handleErrorResponse(ErrorResponse $response) : void
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
