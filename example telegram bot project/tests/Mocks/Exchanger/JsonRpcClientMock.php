<?php

namespace Tests\Mocks\Exchanger;

use App\Services\Exchanger\JsonRpcClient;
use App\Services\Language\LanguageService;
use Mockery\MockInterface;
use Tests\Mocks\Exchanger\JsonRpcClient as FakeJsonRpcClient;

trait JsonRpcClientMock
{
    public function initJsonRpcClientMock(array $responses = []): void
    {
        $jsonRpcClient = new FakeJsonRpcClient('');
        $jsonRpcClient->setResponses($responses);

        $mock = \Mockery::mock(
            JsonRpcClient::class,
            [app(LanguageService::class)],
            static function (MockInterface $mock) use ($jsonRpcClient) {
                $mock->shouldAllowMockingProtectedMethods()
                    ->allows('getClient')
                    ->andReturn($jsonRpcClient)
                ;
            }
        )
            ->makePartial();

        $this->instance(
            JsonRpcClient::class,
            $mock
        );
    }
}
