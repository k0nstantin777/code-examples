<?php

namespace Tests\Mocks\Exchanger;

use App\Services\Exchanger\Enums\MessageCode;
use Datto\JsonRpc\Http\Client;
use Illuminate\Support\Str;

class JsonRpcClient extends Client
{
    protected array $responses = [];

    public function __construct($uri, array $headers = null, array $options = null)
    {
    }

    public function query($method, $arguments, &$response)
    {
        $response = [];
        $defaultResponse = [];

        $responseMethod = 'getDefault' . $this->parseMethod($method) . 'Response';

        if (method_exists($this, $responseMethod)) {
            $defaultResponse = $this->$responseMethod();
        }

        $response = $this->getResponse($method, $defaultResponse);
    }

    private function parseMethod(string $method) : string
    {
        $replacedDotsMethod = str_replace('.', '-', $method);

        return ucfirst(Str::camel($replacedDotsMethod));
    }

    public function send() : void
    {
    }

    private function getDefaultExchangeDirectionsResponse() : array
    {
        return [
            'data' => [
                [
                    'id' => 1,
                    'given_currency' => $this->getCurrencyData('BTC'),
                    'received_currency' => $this->getCurrencyData('ETH'),
                ],
                [
                    'id' => 2,
                    'given_currency' => $this->getCurrencyData('ADVUSD'),
                    'received_currency' => $this->getCurrencyData('ETH'),
                ],
            ],
            'meta' => [
                'offset' => 0,
                'limit' => 100,
                'total' => 2,
            ]
        ];
    }

    private function getCurrencies() : array
    {
        return [
            'BTC' => [
                'id' => 1,
                'position' => 1,
                'label' => 'crypto',
                'label_description' => 'Crypto',
                'name' => 'Bitcoin',
                'code' =>  'BTC',
                'icon' =>  '',
                'reserve' => 1,
                'exchange_prompt' => 'Bitcoin address',
            ],
            'ETH' => [
                'id' => 2,
                'position' => 2,
                'label' => 'crypto',
                'label_description' => 'Crypto',
                'name' => 'Ethereum',
                'code' =>  'ETH',
                'icon' =>  '',
                'reserve' => 1,
                'exchange_prompt' => 'Ethereum address',
            ],
            'ADVUSD' => [
                'id' => 3,
                'position' => 3,
                'label' => 'payment_system',
                'label_description' => 'PS',
                'name' => 'AdvCash USD',
                'code' =>  'USD',
                'icon' =>  '',
                'reserve' => 1,
                'exchange_prompt' => 'Adv USD wallet',
            ]
        ];
    }

    private function getCurrencyData(string $code) : array
    {
        return $this->getCurrencies()[$code] ?? [];
    }

    private function getDefaultCustomersShowResponse() : array
    {
        return [
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@email.com',
        ];
    }

    private function getDefaultExternalCustomerSessionsShowResponse() : array
    {
        return [
            'type' => config('services.exchanger.login_source_type'),
            'customer_id' => 1,
            'params' => [
                'chat_id' => 4323
            ],
            'expired_at' => now()->addMinute(),
        ];
    }

    private function getDefaultExchangeDirectionsShowResponse() : array
    {
        return [
            'id' => 1,
            'given_currency' => $this->getCurrencyData('BTC'),
            'received_currency' => $this->getCurrencyData('ETH'),
            'given_currency_rate' => 1,
            'received_currency_rate' => 100,
            'given_min_sum' => 0.1,
            'given_max_sum' => 0,
            'received_min_sum' => 0,
            'received_max_sum' => 1000,
            'commission_value' => '1% ETH',
            'access' => [
                'is_allowed' => true,
                'cause' => ''
            ],
            'form_attributes' => [
                [
                    'id'=> 2,
                    'name'=> 'Record details of the received currency',
                    'code'=> 'requisites_received_currency',
                    'value'=> '1'
                ],
                [
                    'id'=> 6,
                    'name'=> 'Phone Number',
                    'code'=> 'customer_phone',
                    'value'=> '1'
                ]
            ],
        ];
    }

    private function getDefaultExchangeDirectionRateCalculateReceivedResponse() : array
    {
        return [
            'given_sum' => 1,
            'received_sum' => 10,
            'commission' => 1,
        ];
    }

    private function getDefaultExchangeDirectionRateCalculateGivenResponse() : array
    {
        return [
            'given_sum' => 1,
            'received_sum' => 10,
            'commission' => 1,
        ];
    }

    private function getDefaultExchangeRequestsStoreResponse() : array
    {
        return [
            'token' => 'c4bed09a-3f3c-495a-b920-b328d574479f'
        ];
    }

    private function getDefaultExchangeRequestsProcessingPayResponse() : array
    {
        return [
            'success' => true
        ];
    }

    private function getDefaultExchangeRequestsProcessingRejectResponse() : array
    {
        return [
            'success' => true
        ];
    }

    private function getDefaultExchangeRequestsShowResponse() : array
    {
        return [
            "id"=> "c4bed09a-3f3c-495a-b920-b328d574479f",
            "formatted_token"=> "57300299",
            "status_string"=> "Awaiting Payment",
            "status"=> "awaiting_payment",
            "created_date_string"=> "08.09.2022, 16:14",
            "created_at"=> "2022-09-08T13:14:17.000000Z",
            "given_currency_rate"=> "1",
            "given_sum"=> "100",
            "received_sum"=> "9.4172",
            "received_currency_rate"=> "0.102971",
            "is_expired"=> false,
            "expired_at" => '2022-09-08T14:14:17.000000Z',
            "show_link"=> "http://exchanger.loc/exchange-requests/c4bed09a-3f3c-495a-b920-b328d574479f",
            "payment_address"=> null,
            "commission_string"=> "9 RUB",
            "qr_code_img" => '',
            "is_payable"=> true,
            "is_rejectable"=> true,
            "is_need_card_verify"=> false,
            "comment_for_customer"=> null,
            "given_currency" => $this->getCurrencyData('BTC'),
            "received_currency"=> $this->getCurrencyData('ETH'),
            "attributes"=> [
                [
                    "id"=> 2,
                    "code"=> "requisites_received_currency",
                    "name"=> "Record details of the received currency",
                    "value"=> "3EfFU4HSsuTHeMrKLuMmj5A5ycoiBePZ8n"
                ],
                [
                    "id"=> 6,
                    "code"=> "customer_phone",
                    "name"=> "Phone Number",
                    "value"=> "+79221630150"
                ]
            ],
            "customer" => $this->getDefaultCustomersShowResponse(),
            "credit_cards"=> [
                [
                    "id"=> 9,
                    "card_number"=> "5522043362842856",
                    "secret_card_number"=> "**** **** **** 2856",
                    "status"=> "rejected",
                    "status_string"=> "Rejected"
                ],
            ],
            'payment_form_data' => [
                'address' => 'test_payment_address'
            ],
            'received_requisites' => '3EfFU4HSsuTHeMrKLuMmj5A5ycoiBePZ8n',
        ];
    }

    private function getDefaultSettingsResponse() : array
    {
        return [
            [
                "group" => "telegram_bot",
                "code" => "messages",
                "value" => json_encode($this->messages()),
            ]
        ];
    }

    private function getDefaultCurrenciesShowResponse() : array
    {
        return $this->getCurrencyData('BTC');
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

    private function messages() : array
    {
        return [
            MessageCode::WELCOME() => [
                'en' => 'Welcome to FXService!',
                'ru' => 'Добро пожаловать в FXService!'
            ],
            MessageCode::ERROR_INVALID_BOT_ACTION() => [
                'en' => 'I don\'t understand you',
                'ru' => 'Я не понимаю Вас'
            ],
            MessageCode::ENTER_FORM_ATTRIBUTE() => [
                'en' => 'Please enter next required information: *"attribute_name"*',
                'ru' => 'Пожалуйста введите следующую обязательную информацию: *"attribute_name"*'
            ],
            MessageCode::ERROR_COMMON_ERROR_OCCURRED() => [
                'en' => "Oops... something seems to have gone wrong.\nTry again, if the error persists, please contact us",
                'ru' => "Уупс... кажется, что-то пошло не так\nПопробуйте еще раз и если ошибка повторится, свяжитесь с нами"
            ],
            MessageCode::ERROR_EXCHANGE_NOT_FILLED_YET() => [
                'en' => 'The exchange request not filled yet, follow the bot prompts',
                'ru' => 'Заявка на обмен еще не заполнена, следуйте подсказкам'
            ],
            MessageCode::SELECT_RECEIVED_CURRENCY() => [
                'en' => 'Please select received currency',
                'ru' => 'Пожалуйста, выберите получаемую валюту'
            ],
            MessageCode::SELECT_GIVEN_CURRENCY() => [
                'en' => 'Please select given currency',
                'ru' => 'Пожалуйста, выберите отдаваемую валюту'
            ],
            MessageCode::GIVEN_CURRENCY_SELECTED() => [
                'en' => 'Selected given currency: *currency_name*',
                'ru' => 'Выбрана отдаваемая валюта: *currency_name*'
            ],
            MessageCode::RECEIVED_CURRENCY_SELECTED() => [
                'en' => 'Selected received currency: *currency_name*',
                'ru' => 'Выбрана получаемая валюта: *currency_name*'
            ],
            MessageCode::SELECT_CALCULATING_SUM_CURRENCY() => [
                'en' => 'Please select currency for calculating exchange sum',
                'ru' => 'Пожалуйста, выберите тип валюты для калькуляции суммы обмена'
            ],
            MessageCode::ERROR_EXCHANGE_NOT_STARTED_YET() => [
                'en' => 'You did not started exchange request yet',
                'ru' => 'Вы еще не инициировали заявку на обмен'
            ],
            MessageCode::ERROR_PAYMENT_FORM_ERROR() => [
                'en' => 'Payment temporary impossible',
                'ru' => 'Оплата временно невозможна'
            ],
            MessageCode::PAYMENT_INSTRUCTIONS_HEAD() => [
                'en' => 'To pay follow the next steps',
                'ru' => 'Для оплаты следуйте следующим шагам'
            ],
            MessageCode::EXCHANGE_REQUEST_COMPLETED() => [
                'en' => 'Your order has been completed with status: *"status_name"*',
                'ru' => 'Ваша заявка была завершена со статусом: *"status_name"*'
            ],
            MessageCode::OPERATOR_COMMENTED() => [
                'en' => 'Operator leaved comment: "comment_text"',
                'ru' => 'Оператор оставил комментарий: "comment_text"'
            ],
            MessageCode::EXCHANGE_REQUEST_MARKED_AS_PAID() => [
                'en' => 'The application has been successfully marked as paid, we will check the payment and inform you',
                'ru' => 'Заявка на обмен была помечена как оплаченная, мы проверим платеж и сообщим Вам'
            ],
            MessageCode::ERROR_EXCHANGE_REQUEST_NOT_EXIST() => [
                'en' => 'The application does not exist or expired',
                'ru' => 'Заявка на обмен не существует или просрочена'
            ],
            MessageCode::EXCHANGE_REQUEST_CANCELLED() => [
                'en' => 'The application has been cancelled',
                'ru' => 'Заявка на обмен отменена'
            ],
            MessageCode::EXCHANGE_REQUEST_CREATED() => [
                'en' => 'The application has been created successfully',
                'ru' => 'Заявка на обмен успешно создана'
            ],
        ];
    }
}
