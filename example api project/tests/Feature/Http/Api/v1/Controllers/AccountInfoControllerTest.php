<?php

namespace Tests\Feature\Http\Api\v1\Controllers;

use App\Domains\Account\Models\ApiUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Stubs\Traits\JsonRpcClientMock;
use Tests\TestCase;

class AccountInfoControllerTest extends TestCase
{
    use JsonRpcClientMock;
    use RefreshDatabase;

    public function testIndex(): void
    {
        $this->setUpJsonRpcClientMock();

        Sanctum::actingAs(
            ApiUser::factory()->create(),
            []
        );

        $response = $this->get('/api/v1/account');

        $response->assertStatus(200);
        $response->assertJson([
            'result' => [
                'id' => 11,
                'name' => 'Test User',
                'email' => 'test@email.com',
                'addresses' => [
                    [
                        "id" => 85,
                        "postal" => "47334",
                        "state" => "Georgia",
                        "address1" => "6400 S Overlook Dr",
                        "address2" => "",
                        "city" => "Daleville",
                        "telephone" => "+1 12312312312",
                        "firstname" => "Rita",
                        "lastname" => "Snurkowski",
                        "company" => "",
                        "email" => "",
                        "salutation" => "mr"
                    ]
                ],
                'graves' => [
                    [
                        'id' => 1,
                        'cemetery' => [
                            "id" => 201,
                            "name" => "Paradise Gardens",
                            "city" => "West Memphis",
                            "address1" => "212 North 6th Street",
                            "address2" => "",
                            "state" => "AR",
                            "stateName" => "",
                            "zip" => "72301",
                            "phone" => "+1 12312312312",
                            "email" => "",
                            "is_active" => true
                        ],
                        "state_name" => "Arkansas",
                        "city" => "West Memphis",
                        "section" => "1",
                        "lot" => "",
                        "space" => "",
                        "building" => "",
                        "tier" => "",
                        "notes" => "This is Grave Comment",
                        "loved_info" => "Dona Kir",
                        "contact_phone" => "+1 12312312312",
                        "grave_image" => ""
                    ]
                ],
            ]
        ]);
    }

    public function testIndexServerErrorByInvalidResponse(): void
    {
        $this->setUpJsonRpcClientMock([
            'account' => [
                'id' => 1,
            ]
        ]);

        Sanctum::actingAs(
            ApiUser::factory()->create(),
            []
        );

        $response = $this->get('/api/v1/account');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => 'Server error'
        ]);
    }

    public function testIndexAccessDenied(): void
    {
        $this->setUpJsonRpcClientMock();

        $response = $this->getJson('/api/v1/account');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => 'Access Denied'
        ]);
    }

    /**
     * @param string $includes
     * @param bool $hasError
     * @return void
     * @dataProvider validateIncludesDataProvider
     */
    public function testIndexValidateIncludes(string $includes, bool $hasError): void
    {
        $this->setUpJsonRpcClientMock();

        Sanctum::actingAs(
            ApiUser::factory()->create(),
            []
        );

        $response = $this->getJson('/api/v1/account?includes=' . $includes);

        $response->assertStatus(200);

        if ($hasError) {
            $response->assertJson([
                'error' => [
                    'includes' => [
                        "Includes contains invalid value(s)"
                    ]
                ]
            ]);
        } else {
            $response->assertJson([
                'result' => [
                    'id' => 11,
                    'name' => 'Test User',
                    'email' => 'test@email.com',
                    'addresses' => [],
                    'graves' => [],
                ]
            ]);
        }
    }

    public function validateIncludesDataProvider(): array
    {
        return [
            'Invalid includes word' => [
                'random',
                true,
            ],
            'Valid includes' => [
                'addresses, graves',
                false,
            ],
            'Invalid includes separator' => [
                'addresses; graves;',
                true,
            ]
        ];
    }
}
