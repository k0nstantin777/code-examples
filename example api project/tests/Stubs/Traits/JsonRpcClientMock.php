<?php

namespace Tests\Stubs\Traits;

use Mockery\MockInterface;
use Tests\Stubs\JsonRpcClient as FakeJsonRpcClient;
use App\Services\FFC\JsonRpcClient;

trait JsonRpcClientMock
{
    public function setUpJsonRpcClientMock(array $responses = []): void
    {
        $jsonRpcClient = new FakeJsonRpcClient('');
        $jsonRpcClient->setResponses($responses);

        $mock = $this->partialMock(JsonRpcClient::class, function (MockInterface $mock) use ($jsonRpcClient) {
            $mock->shouldAllowMockingProtectedMethods()
                ->allows('getClient')
                ->andReturn($jsonRpcClient);
        });

        $this->instance(
            JsonRpcClient::class,
            $mock
        );
    }
}
