<?php

namespace App\Services\Exchanger\ValueObjects;

use Spatie\DataTransferObject\Attributes\MapFrom;

class ActiveExchangeRequest extends ExchangeRequest
{
    #[MapFrom('commission_string')]
    public string $commissionString;

    #[MapFrom('qr_code_img')]
    public string $qrCodeImg;

    #[MapFrom('is_payable')]
    public bool $isPayable;

    #[MapFrom('is_rejectable')]
    public bool $isRejectable;

    #[MapFrom('is_need_card_verify')]
    public bool $isNeedCardVerify;

    #[MapFrom('comment_for_customer')]
    public ?string $commentForCustomer;

    #[MapFrom('customer')]
    public Customer $customer;

    /**
     * @var CreditCard[]
     */
    #[MapFrom('credit_cards')]
    public array $creditCards;

    #[MapFrom('payment_form_data')]
    public array $paymentFormData = [];

    #[MapFrom('received_requisites')]
    public ?string $receivedRequisites;

    #[MapFrom('given_requisites')]
    public ?string $givenRequisites;

    public function getReceivedRequisites() : string
    {
        foreach ($this->attributes as $exchangeFormAttribute) {
            if ($exchangeFormAttribute->code === ExchangeFormAttribute::REQUISITES_RECEIVED_CURRENCY_CODE) {
                return $exchangeFormAttribute->value;
            }
        }

        return '';
    }
}