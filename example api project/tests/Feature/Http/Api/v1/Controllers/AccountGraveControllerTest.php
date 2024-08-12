<?php

namespace Tests\Feature\Http\Api\v1\Controllers;

use App\Domains\Account\Models\ApiUser;
use Datto\JsonRpc\Responses\ErrorResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Stubs\Traits\JsonRpcClientMock;
use Tests\TestCase;

class AccountGraveControllerTest extends TestCase
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
            "cemetery_id" => 34,
            "section" => "1",
            "lot" => "4",
            "space" => "2",
            "tier" => "Nik",
            "notes" => "Additiona notes",
            "loved_info" => "Name LastName",
            "contact_phone" => "+01 213213 123213"
        ];

        $response = $this->postJson('/api/v1/account/graves', $requestData);

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

        $response = $this->postJson('/api/v1/account/graves', $inputData);

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
                    "cemetery_id" => [
                        "The cemetery id field is required."
                    ],
                    "loved_info" => [
                        "The loved info field is required."
                    ],
                    "contact_phone" => [
                        "The contact phone field is required."
                    ]
                ]
            ],
            'Min rule' => [
                [
                    "loved_info" => "5",
                    "contact_phone" => "T",
                ],
                [
                    "cemetery_id" => [
                        "The cemetery id field is required."
                    ],
                    "contact_phone" => [
                        "The contact phone must be at least 2 characters."
                    ],
                    "loved_info" => [
                        "The loved info must be at least 2 characters."
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
