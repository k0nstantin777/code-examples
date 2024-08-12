<?php

namespace Tests\Stubs;

use App\Services\FFC\Enums\UserType;
use Datto\JsonRpc\Http\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class JsonRpcClient extends Client
{
    protected array $responses = [];

    public function __construct($uri, array $headers = null, array $options = null)
    {
    }

    public function query($method, $arguments, &$response): void
    {
        $response = [];
        $defaultResponse = [];

        $responseMethod = 'getDefault' . $this->parseMethod($method) . 'Response';

        if (method_exists($this, $responseMethod)) {
            $defaultResponse = $this->$responseMethod();
        }

        $response = $this->getResponse($method, $defaultResponse);
    }

    private function parseMethod(string $method): string
    {
        $replacedDotsMethod = str_replace('.', '-', $method);
        $replacedDotsMethod = str_replace('/', '-', $replacedDotsMethod);

        return ucfirst(Str::camel($replacedDotsMethod));
    }

    public function send(): void
    {
    }

    private function getDefaultProductsResponse(): array
    {
        return [
            'data' => [
                [
                    'id' => 1,
                    'code' => 'TEST1',
                    'label' => 'test1',
                    'price' => 10.05,
                    'image' => '/storage/img.jpg',
                    'category' => [
                        'id' => 1,
                        'code' => 'CTEST1',
                        'label' => 'ctest1',
                    ]
                ],
            ],
            'meta' => [
                'offset' => 0,
                'limit' => 100,
                'total' => 1,
            ]
        ];
    }

    private function getDefaultAccountResponse(): array
    {
        return [
            'id' => 11,
            'name' => 'Test User',
            'email' => 'test@email.com',
            'type' => UserType::CONSUMER->value,
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
        ];
    }

    private function getDefaultCategoriesResponse(): array
    {
        return [
            'data' => [
                [
                    'id' => 1,
                    'code' => 'TEST1',
                    'label' => 'test1'
                ],
                [
                    'id' => 2,
                    'code' => 'TEST2',
                    'label' => 'test2'
                ],
            ],
            'meta' => [
                'offset' => 0,
                'limit' => 100,
                'total' => 1,
            ]
        ];
    }

    private function getDefaultOrdersResponse(): array
    {
        return [
            'data' => [
                [
                    "id" => 54404,
                    "created_date" => Carbon::parse('04/01/2021 01:11pm')->timestamp,
                    'customer' => [
                        'id' => 11,
                        'name' => 'Test User',
                        'email' => 'test@email.com',
                        'type' => UserType::CONSUMER->value,
                    ],
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
                        "tax" => "0",
                        "service_id" => 1
                    ],
                    "payment_service" => [
                        "id" => 108848,
                        "name" => "Invoice",
                        "price" => "52.77",
                        "tax" => "0",
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
        ];
    }

    private function getDefaultAuthResponse(): array
    {
        return [
            'id' => 11,
            'name' => 'Test User',
            'email' => 'test@email.com',
            'type' => UserType::CONSUMER->value,
        ];
    }

    private function getDefaultCalculateOrderResponse(): array
    {
        return [
            "id" => 1,
            "order" => [
                "subTotal" => 81.57,
                "rebate" => 0,
                "tax" => 4.89,
                "total" => 86.46,
                "isFreeShipping" => false,
                "shipRates" => [
                    [
                        "id" => 1,
                        "cost" => 12.77,
                        "serviceName" => "UPS® Ground",
                        "carrierName" => "UPS"
                    ],
                    [
                        "id" => 3,
                        "cost" => 13.11,
                        "serviceName" => "FedEx Home Delivery®",
                        "carrierName" => "Fedex"
                    ],
                    [
                        "id" => 12,
                        "cost" => 0,
                        "serviceName" => "Economy Shipping",
                        "carrierName" => "Ruby`s Economy Shipping"
                    ]
                ]
            ]
        ];
    }

    private function getDefaultOrdersStoreResponse(): array
    {
        return [
            "id" => 54474,
            "order_number" => "RS-16E-5943",
        ];
    }

    private function getDefaultAccountAddressesStoreResponse(): array
    {
        return [
            "id" => 54474,
        ];
    }

    private function getDefaultAccountGravesStoreResponse(): array
    {
        return [
            "id" => 54474,
        ];
    }

    public function setResponses(array $responses): void
    {
        $this->responses = $responses;
    }

    private function getResponse(string $method, array $default = [])
    {
        if (!isset($this->responses[$method])) {
            return $default;
        }

        if (is_callable($this->responses[$method])) {
            return $this->responses[$method]();
        }

        return $this->responses[$method];
    }
}
