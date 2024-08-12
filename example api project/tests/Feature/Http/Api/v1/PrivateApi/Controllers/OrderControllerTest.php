<?php

namespace Tests\Feature\Http\Api\v1\PrivateApi\Controllers;

use App\Domains\Account\Enums\Ability;
use App\Domains\Account\Models\ApiUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Stubs\Traits\JsonRpcClientMock;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use JsonRpcClientMock;
    use RefreshDatabase;

    public function testIndex(): void
    {
        $this->setUpJsonRpcClientMock();

        Sanctum::actingAs(
            ApiUser::factory()->create(),
            [Ability::ACCESS_PRIVATE_API->value]
        );

        $response = $this->getJson('/api/v1/private/orders');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'result' => [
                'data',
                'meta'
            ]
        ]);
        $response->assertJson([
            'result' => [
                'data' => [
                    [
                        "id" => 54404,
                        "created_date" => "04/01/2021 05:11pm",
                        'customer' => [
                            'id' => 11,
                            'name' => 'Test User',
                            'email' => 'test@email.com',
                        ],
                        "order_number" => "RS-01D-EDB6",
                        "status" => "Active",
                        "price" => "52.77",
                        "comment" => "",
                        "delivery_address" => [
                            "id" => 108088,
                            "postal" => "99515",
                            "state" => "AK",
                            "address1" => "440 East Klatt Road",
                            "address2" => "",
                            "city" => "Anchorage",
                            "telephone" => "9073441311",
                            "firstname" => "",
                            "lastname" => "",
                            "company" => "Angelus Memorial Park Inc",
                            "email" => "",
                            "salutation" => "company",
                            "address_id" => null
                        ],
                        "payment_address" => [
                            "id" => 108087,
                            "postal" => "47334",
                            "state" => "GA",
                            "address1" => "6400 S Overlook Dr",
                            "address2" => "",
                            "city" => "Daleville",
                            "telephone" => "",
                            "firstname" => "Bred",
                            "lastname" => "Pit",
                            "company" => "",
                            "email" => "",
                            "salutation" => "mr",
                            "address_id" => 85
                        ],
                        "delivery_service" => [
                            "id" => 108847,
                            "name" => "UPS",
                            "price" => "0.00",
                            "service_id" => 1
                        ],
                        "payment_service" => [
                            "id" => 108848,
                            "name" => "Invoice",
                            "price" => "52.77",
                            "service_id" => 13
                        ],
                        "products" => [
                              [
                                  "id" => 240942,
                                "product_id" => 1448,
                                "code" => "BR2618",
                                "name" => "Sunset Dahlia and Mums",
                                "price" => "29.99",
                                "quantity" => 1
                              ],
                              [
                                  "id" => 240943,
                                    "product_id" => 1477,
                                    "parent_id" => 240942,
                                    "code" => "MA2649",
                                    "name" => "Poinsettia with Snow Pinecones",
                                    "price" => "19.79",
                                    "quantity" => 1
                              ]
                        ],
                        "grave" => [
                            "id" => 7279,
                            "cemetery" => [
                                "id" => 135,
                                "name" => "Angelus Memorial Park Inc",
                                "city" => "Anchorage",
                                "address1" => "440 East Klatt Road",
                                "address2" => "",
                                "state" => "AK",
                                "stateName" => "",
                                "zip" => "99515",
                                "phone" => "+1 1231231231",
                                "email" => "",
                                "is_active" => true
                            ],
                            "state_name" => "Alaska",
                            "city" => "Anchorage",
                            "section" => "q",
                            "lot" => "w",
                            "space" => "q",
                            "building" => "q",
                            "tier" => "q",
                            "notes" => "",
                            "loved_info" => "Dona Kir",
                            "contact_phone" => "+1 1231231231",
                            "grave_image" => ""
                        ]
                    ],
                ],
                'meta' => [
                    'offset' => 0,
                    'limit' => 100,
                    'total' => 1,
                ]
            ]
        ]);
    }

    public function testIndexServerErrorByInvalidResponse(): void
    {
        $this->setUpJsonRpcClientMock([
            'orders' => [
                'data' => [],
            ]
        ]);

        Sanctum::actingAs(
            ApiUser::factory()->create(),
            [Ability::ACCESS_PRIVATE_API->value]
        );

        $response = $this->get('/api/v1/private/orders');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => 'Server error'
        ]);
    }

    public function testIndexAccessDeniedIfUnauth(): void
    {
        $this->setUpJsonRpcClientMock();

        $response = $this->getJson('/api/v1/private/orders');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => 'Access Denied'
        ]);
    }

    public function testIndexAccessDeniedIfWithoutAbility(): void
    {
        $this->setUpJsonRpcClientMock();

        Sanctum::actingAs(
            ApiUser::factory()->create(),
            []
        );

        $response = $this->getJson('/api/v1/private/orders');

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
            [Ability::ACCESS_PRIVATE_API->value]
        );

        $response = $this->getJson('/api/v1/private/orders?includes=' . $includes);

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
                    'data' => [],
                    'meta' => [],
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
                'delivery_address, payment_address, delivery_service,payment_service,grave,products,coupon',
                false,
            ],
            'Invalid includes separator' => [
                'delivery_address; payment_address; delivery_service',
                true,
            ]
        ];
    }
}
