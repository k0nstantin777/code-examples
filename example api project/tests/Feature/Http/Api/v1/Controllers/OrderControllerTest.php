<?php

namespace Tests\Feature\Http\Api\v1\Controllers;

use App\Domains\Account\Models\ApiUser;
use App\Domains\Order\Models\PreparedOrder;
use Datto\JsonRpc\Responses\ErrorResponse;
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
            []
        );

        $response = $this->getJson('/api/v1/orders');

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
                        "order_number" => "RS-01D-EDB6",
                        "status" => "Active",
                        "price" => "52.77",
                        "tax" => 0,
                        "rebate" => 0,
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
            []
        );

        $response = $this->get('/api/v1/orders');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => 'Server error'
        ]);
    }

    public function testIndexAccessDenied(): void
    {
        $this->setUpJsonRpcClientMock();

        $response = $this->getJson('/api/v1/orders');

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

        $response = $this->getJson('/api/v1/orders?includes=' . $includes);

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

    public function testStore(): void
    {
        $this->setUpJsonRpcClientMock();

        $user = ApiUser::factory()->create();

        Sanctum::actingAs(
            $user,
            []
        );

        /* @var PreparedOrder $prepareOrder */
        $prepareOrder = PreparedOrder::factory()->create();

        $requestData =  [
            'prepared_order_id' => $prepareOrder->id,
            'ship_rate' => [
                'id' => 1,
                'cost' => 12.77,
                'carrierName' => 'UPS',
                'serviceName' => 'UPS® Ground'
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $requestData);

        $response->assertStatus(200);
        $response->assertJson([
            'result' => [
                "id" => 54474,
                "order_number" => "RS-16E-5943",
            ]
        ]);

        $this->assertSoftDeleted(PreparedOrder::class, [
            'id' => $prepareOrder->id,
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

        $response = $this->postJson('/api/v1/orders', $inputData);

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
                    'prepared_order_id' => [
                        'The prepared order id field is required.'
                    ],
                    'ship_rate' => [
                        'The ship rate field is required.'
                    ],
                ]
            ],
            'Array rule' => [
                [
                    'ship_rate' => false
                ],
                [
                    'ship_rate' => [
                        'The ship rate must be an array.'
                    ],
                ]
            ],
        ];
    }

    public function testStoreValidateExistPreparedOrder(): void
    {
        $this->setUpJsonRpcClientMock();

        $user1 = ApiUser::factory()->create();
        $user2 = ApiUser::factory()->create();
        Sanctum::actingAs(
            $user1,
            []
        );
        /* @var PreparedOrder $preparedOrder1 */
        $preparedOrder1 = PreparedOrder::factory()->create([
            'user_id' => $user1->id,
        ]);

        /* @var PreparedOrder $preparedOrder2 */
        $preparedOrder2 = PreparedOrder::factory()->create([
            'user_id' => $user2->id,
        ]);

        $response = $this->postJson('/api/v1/orders', [
            'prepared_order_id' => $preparedOrder2->id
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => [
                'prepared_order_id' => [
                    'The selected prepared order id is invalid.'
                ],
            ],
        ]);
    }

    public function testStoreValidateNotExpiredPreparedOrder(): void
    {
        $this->setUpJsonRpcClientMock();

        $user1 = ApiUser::factory()->create();

        Sanctum::actingAs(
            $user1,
            []
        );
        /* @var PreparedOrder $preparedOrder1 */
        $preparedOrder1 = PreparedOrder::factory()->create([
            'user_id' => $user1->id,
        ]);

        $preparedOrder1->created_at = now()->subMinutes(6);
        $preparedOrder1->save();

        $response = $this->postJson('/api/v1/orders', [
            'prepared_order_id' => $preparedOrder1->id
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => [
                'prepared_order_id' => [
                    'The prepared order is expired.'
                ],
            ],
        ]);
    }

    /**
     * @dataProvider validateCorrectShipRateDataProvider
     * @param array $inputData
     * @param bool $hasError
     * @return void
     */
    public function testStoreValidateCorrectShipRate(array $inputData, bool $hasError): void
    {
        $this->setUpJsonRpcClientMock();

        $user1 = ApiUser::factory()->create();

        Sanctum::actingAs(
            $user1,
            []
        );
        /* @var PreparedOrder $preparedOrder1 */
        $preparedOrder1 = PreparedOrder::factory()->create();

        $response = $this->postJson('/api/v1/orders', [
            'prepared_order_id' => $preparedOrder1->id,
            'ship_rate' => $inputData
        ]);

        if (false === $hasError) {
            $response->assertJson([
                'result' => [
                    "id" => 54474,
                    "order_number" => "RS-16E-5943",
                ]
            ]);
        } else {
            $response->assertJson([
                'error' => [
                    'ship_rate' => [
                        'The ship rate incorrect.'
                    ]
                ]
            ]);
        }
    }

    public function validateCorrectShipRateDataProvider(): array
    {
        return [
            'Without Id' => [
                [
                    'cost' => 12.77,
                    'carrierName' => 'UPS',
                    'serviceName' => 'UPS® Ground'
                ],
                true
            ],
            'Without Cost' => [
                [
                    'id' => 1,
                    'carrierName' => 'UPS',
                    'serviceName' => 'UPS® Ground'
                ],
                true
            ],
            'Without serviceName' => [
                [
                    'id' => 1,
                    'cost' => 12.77,
                    'carrierName' => 'UPS',
                ],
                true
            ],
            'Without carrierName' => [
                [
                    'id' => 1,
                    'cost' => 12.77,
                    'serviceName' => 'UPS® Ground'
                ],
                true
            ],
            'Not exist in PreparedOrder' => [
                [
                    'id' => 2,
                    'cost' => 12.77,
                    'carrierName' => 'UPS',
                    'serviceName' => 'UPS® Ground'
                ],
                true
            ],
            'Valid' => [
                [
                    'id' => 1,
                    'cost' => 12.77,
                    'carrierName' => 'UPS',
                    'serviceName' => 'UPS® Ground'
                ],
                false
            ]
        ];
    }

    public function testStoreServerErrorByInvalidResponse(): void
    {
        $this->setUpJsonRpcClientMock([
            'orders.store' => new ErrorResponse(
                1,
                'Error',
                ErrorResponse::INVALID_REQUEST
            ),
        ]);

        Sanctum::actingAs(
            ApiUser::factory()->create(),
            []
        );

        /* @var PreparedOrder $prepareOrder */
        $prepareOrder = PreparedOrder::factory()->create();

        $requestData =  [
            'prepared_order_id' => $prepareOrder->id,
            'ship_rate' => [
                'id' => 1,
                'cost' => 12.77,
                'carrierName' => 'UPS',
                'serviceName' => 'UPS® Ground'
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $requestData);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => 'Server error'
        ]);
    }
}
