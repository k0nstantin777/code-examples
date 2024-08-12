<?php

namespace App\Services\Exchanger\Endpoints;

use App\Services\Exchanger\Enums\ExchangeRequestStatus;
use App\Services\Exchanger\Exceptions\JsonRpcErrorResponseException;
use App\Services\Exchanger\RequestDTOs\GetExchangeRequestRequestDto;
use App\Services\Exchanger\ValueObjects\ActiveExchangeRequest;
use App\Services\Exchanger\ValueObjects\CreditCard;
use App\Services\Exchanger\ValueObjects\Currency;
use App\Services\Exchanger\ValueObjects\Customer;
use App\Services\Exchanger\ValueObjects\ExchangeFormAttribute;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class GetActiveExchangeRequest extends BaseJsonRpcEndpoint
{
    /**
     * @param mixed ...$arguments
     * @throws ValidationException|JsonRpcErrorResponseException
     * @throws UnknownProperties
     */
    public function execute(...$arguments) : ActiveExchangeRequest
    {
        /** @var GetExchangeRequestRequestDto $dto */
        [$dto] = $arguments;

        $response = $this->jsonRpcClient->send('exchange-requests.show', [
            'id' => $dto->id,
            'customer_id' => $dto->customerId,
            'has_active_fields' => true,
        ]);

        $attributes = [];
        foreach ($response['attributes'] as $attributeData) {
            $attributes[] = new ExchangeFormAttribute($attributeData);
        }

        $creditCards = [];
        foreach ($response['credit_cards'] as $creditCardData) {
            $creditCards[] = new CreditCard($creditCardData);
        }

        return new ActiveExchangeRequest(
            id: $response['id'],
            formatted_token: $response['formatted_token'],
            status_string: $response['status_string'],
            status: ExchangeRequestStatus::tryFrom($response['status']),
            created_date_string: $response['created_date_string'],
            created_at: Carbon::parse($response['created_at']),
            given_currency_rate: $response['given_currency_rate'],
            given_sum: $response['given_sum'],
            received_sum: $response['received_sum'],
            received_currency_rate: $response['received_currency_rate'],
            is_expired: $response['is_expired'],
            expired_at: isset($response['expired_at']) ? Carbon::parse($response['expired_at']) : null,
            show_link: $response['show_link'],
            payment_address: $response['payment_address'] ?? null,
            given_currency: new Currency($response['given_currency']),
            received_currency: new Currency($response['received_currency']),
            attributes: $attributes,
            commission_string: $response['commission_string'],
            qr_code_img: $response['qr_code_img'],
            is_payable: $response['is_payable'],
            is_rejectable: $response['is_rejectable'],
            is_need_card_verify: $response['is_need_card_verify'],
            comment_for_customer: $response['comment_for_customer'],
            customer: new Customer($response['customer']),
            credit_cards: $creditCards,
            payment_form_data: $response['payment_form_data'] ?? [],
            received_requisites: $response['received_requisites'] ?? null,
            given_requisites: $response['given_requisites'] ?? null,
        );
    }
}
