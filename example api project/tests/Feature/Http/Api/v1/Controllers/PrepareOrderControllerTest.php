<?php

namespace Tests\Feature\Http\Api\v1\Controllers;

use App\Domains\Account\Models\ApiUser;
use App\Domains\Order\Enums\ShippingType;
use App\Domains\Order\Models\PreparedOrder;
use Datto\JsonRpc\Responses\ErrorResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\Stubs\Traits\JsonRpcClientMock;
use Tests\TestCase;

class PrepareOrderControllerTest extends TestCase
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
            'delivery_address_id' => 1,
            'payment_address_id' => 1,
            'shipping_type' => ShippingType::ADDRESS->value,
            'products' => [
                [
                    'id' => 1,
                    'quantity' => 1,
                ]
            ],
            'grave_id' => 1,
            'comment' => 'Some comment',
            'coupon' => null,
        ];

        $assertedResponse = [
            "id"=> 1,
            "order"=> [
                "subTotal"=> 81.57,
                "rebate"=> 0,
                "tax"=> 4.89,
                "total"=> 86.46,
                "isFreeShipping"=> false,
                "shipRates"=> [
                    [
                        "id"=> 1,
                        "cost"=> 12.77,
                        "serviceName"=> "UPS® Ground",
                        "carrierName"=> "UPS"
                    ],
                    [
                        "id"=> 3,
                        "cost"=> 13.11,
                        "serviceName"=> "FedEx Home Delivery®",
                        "carrierName"=> "Fedex"
                    ],
                    [
                        "id"=> 12,
                        "cost"=> 0,
                        "serviceName"=> "Economy Shipping",
                        "carrierName"=> "Ruby`s Economy Shipping"
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/orders/prepare', $requestData);

        $response->assertStatus(200);
        $response->assertJson([
            'result' => [
                'id' => 1,
                'order' => $assertedResponse
            ]
        ]);

		$this->assertDatabaseHas(PreparedOrder::class, [
			'id' => 1,
			'order' => json_encode(
                array_merge([
                'user_id' => $user->ffc_id,
            ],
                $requestData,
                $assertedResponse
                ),
                JSON_THROW_ON_ERROR
            ),
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

		$response = $this->postJson('/api/v1/orders/prepare', $inputData);

		$response->assertStatus(200);
		$response->assertJson([
			'error' => $errorMessages,
		]);
	}

    public function validateDataProvider() : array
    {
        return [
            'Required rule' => [
                [

                ],
                [
                    'products' => [
                        'The products field is required.'
                    ],
                    'payment_address_id' => [
                        'The payment address id field is required.'
                    ],
                    'shipping_type' => [
                        'The shipping type field is required.'
                    ]
                ]
            ],
            'Required products` objects fields rule' => [
                [
                    'products' => [
                        [],
                    ]
                ],
                [
                    'products.0.id' => [
                        'The product id in products list field is required.'
                    ],
                    'products.0.quantity' => [
                        'The product quantity in products list field is required.'
                    ],
                ]
            ],
            'Array rule' => [
                [
                    'products' => false
                ],
                [
                    'products' => [
                       'The products must be an array.'
                    ],
                ]
            ],
            'Integer rule' => [
                [
                    'products' => [
                        [
                            'id' => 'qwe1',
                            'quantity' => 'qwers'
                        ],
                    ],
                    'delivery_address_id' => [],
                    'payment_address_id' => false,
                    'grave_id' => 'qwer'
                ],
                [
                    'products.0.id' => [
                        'The product id in products list must be an integer.'
                    ],
                    'products.0.quantity' => [
                        'The product quantity in products list must be an integer.'
                    ],
                    'delivery_address_id' => [
                        'The delivery address id must be an integer.'
                    ],
                    'payment_address_id' => [
                        'The payment address id must be an integer.'
                    ],
                    'grave_id' => [
                        'The grave id must be an integer.'
                    ],
                ]
            ],
            'In rule' => [
                [
                    'shipping_type' => 'qweqwe',
                ],
                [
                    'shipping_type' => [
                        'The selected shipping type is invalid.'
                    ],
                ]
            ],
            'Require if rule when shipping type is address' => [
                [
                    'shipping_type' => ShippingType::ADDRESS->value,
                ],
                [
                    'delivery_address_id' => [
                        'The delivery address id field is required when shipping type is address.'
                    ],
                ]
            ],
            'Require if rule when shipping type is cemetery' => [
                [
                    'shipping_type' => ShippingType::CEMETERY->value,
                ],
                [
                    'grave_id' => [
                        'The grave id field is required when shipping type is cemetery.'
                    ],
                ]
            ],
            'Min rule' => [
                [
                    'coupon' => '1',
                    'comment' => 'w',
                ],
                [
                    'coupon' => [
                        'The coupon must be at least 2 characters.'
                    ],
                    'comment' => [
                        'The comment must be at least 2 characters.'
                    ],
                ]
            ],
            'Max rule' => [
                [
                    'coupon' => Str::random(21),
                    'comment' => Str::random(1001),
                ],
                [
                    'coupon' => [
                        'The coupon must not be greater than 20 characters.'
                    ],
                    'comment' => [
                        'The comment must not be greater than 1000 characters.'
                    ],
                ]
            ],
        ];
    }

    public function testStoreServerErrorByInvalidResponse(): void
    {
        $this->setUpJsonRpcClientMock([
            'calculate-order' => new ErrorResponse(
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
            'delivery_address_id' => 1,
            'payment_address_id' => 1,
            'shipping_type' => ShippingType::ADDRESS->value,
            'products' => [
                [
                    'id' => 1,
                    'quantity' => 1,
                ]
            ],
            'grave_id' => 1,
            'comment' => 'Some comment',
            'coupon' => null,
        ];

        $response = $this->post('/api/v1/orders/prepare', $requestData);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => 'Server error'
        ]);
    }
}
