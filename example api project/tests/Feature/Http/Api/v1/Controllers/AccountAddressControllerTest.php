<?php

namespace Tests\Feature\Http\Api\v1\Controllers;

use App\Domains\Account\Models\ApiUser;
use Datto\JsonRpc\Responses\ErrorResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Stubs\Traits\JsonRpcClientMock;
use Tests\TestCase;

class AccountAddressControllerTest extends TestCase
{
    use JsonRpcClientMock;
    use RefreshDatabase;

    public function testStore(): void
    {
        $this->setUpJsonRpcClientMock();

        $user = ApiUser::factory()->create();

        Sanctum::actingAs(
            $user,
            []
        );

        $requestData =  [
            "postal" => "04555",
            "state" => "TX",
            "address1" => "Street, 1 - 24/2",
            "city" => "New York",
            "telephone" => "+01 213213 123213",
            "firstname" => "Nik",
            "lastname" => "Marshal",
            "email" => "email@email.com",
            "salutation" => "mr"
        ];

        $response = $this->postJson('/api/v1/account/addresses', $requestData);

        $response->assertStatus(200);
        $response->assertJson([
            'result' => [
                "id" => 54474,
            ]
        ]);
    }

    /**
     * @dataProvider validateDataProvider
     * @param array $inputData
     * @param array $errorMessages
     * @return void
     */
    public function testStoreValidate(array $inputData, array $errorMessages): void
    {
        $this->setUpJsonRpcClientMock();

        $user = ApiUser::factory()->create();
        Sanctum::actingAs(
            $user,
            []
        );

        $response = $this->postJson('/api/v1/account/addresses', $inputData);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => $errorMessages,
        ]);
    }

    public function validateDataProvider(): array
    {
        return [
            'Required rule' => [
                [

                ],
                [
                    "postal" => [
                        "The postal field is required."
                    ],
                        "state" => [
                        "The state field is required."
                    ],
                        "address1" => [
                        "The address1 field is required."
                    ],
                        "city" => [
                        "The city field is required."
                    ],
                        "firstname" => [
                        "The firstname field is required when company is not present."
                    ],
                        "lastname" => [
                        "The lastname field is required when company is not present."
                    ],
                        "company" => [
                        "The company field is required when firstname / lastname is not present."
                    ],
                        "email" => [
                        "The email field is required."
                    ],
                    "salutation" => [
                        "The salutation field is required."
                    ]
                ]
            ],
            'Min rule' => [
                [
                    "postal" => "045",
                    "state" => "T",
                    "address1" => "Stre",
                    "city" => "York",
                    "telephone" => "+01 213",
                    "firstname" => "N",
                    "lastname" => "Ma",
                    "salutation" => "r"
                ],
                [
                    "postal" => [
                        "The postal must be at least 4 characters."
                    ],
                    "state" => [
                        "The state must be 2 characters."
                    ],
                    "address1" => [
                        "The address1 must be at least 5 characters."
                    ],
                    "city" => [
                        "The city must be at least 5 characters."
                    ],
                    "firstname" => [
                        "The firstname must be at least 2 characters."
                    ],
                    "email" => [
                        "The email field is required."
                    ],
                    "salutation" => [
                        "The selected salutation is invalid."
                    ]
                ]
            ],
        ];
    }

    public function testStoreServerErrorByInvalidResponse(): void
    {
        $this->setUpJsonRpcClientMock([
            'account/addresses.store' => new ErrorResponse(
                1,
                'Error',
                ErrorResponse::INVALID_REQUEST
            ),
        ]);

        Sanctum::actingAs(
            ApiUser::factory()->create(),
            []
        );

        $requestData =  [
            "postal" => "04555",
            "state" => "TX",
            "address1" => "Street, 1 - 24/2",
            "city" => "New York",
            "telephone" => "+01 213213 123213",
            "firstname" => "Nik",
            "lastname" => "Marshal",
            "email" => "email@email.com",
            "salutation" => "mr"
        ];

        $response = $this->postJson('/api/v1/account/addresses', $requestData);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => 'Server error'
        ]);
    }
}
