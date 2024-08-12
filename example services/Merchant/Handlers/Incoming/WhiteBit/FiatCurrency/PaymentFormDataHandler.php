<?php

namespace App\Services\Merchant\Handlers\Incoming\WhiteBit\FiatCurrency;

use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Enums\AddressPlaceholder;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\PaymentAddress;
use App\Services\Merchant\ValueObjects\Transaction;
use App\Services\QrCode\QrCodeService;
use App\Services\WhiteBit\PrivateApi\Enums\ProviderName;
use App\Services\WhiteBit\PrivateApi\Exceptions\WhiteBitApiException;
use App\Services\WhiteBit\PrivateApi\RequestDTOs\CreateFiatDepositAddressRequestDto;
use App\Services\WhiteBit\PrivateApi\WhiteBitPrivateApiService;
use Illuminate\Support\Facades\App;
use JetBrains\PhpStorm\ArrayShape;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class PaymentFormDataHandler
{
    public function __construct(
        private readonly TransactionsStorage $transactionsStorage,
        private readonly WhiteBitPrivateApiService $whiteBitApiService,
        private readonly QrCodeService $qrCodeService,
    ) {
    }

    /**
     * @throws WhiteBitApiException|\JsonException
     * @throws UnknownProperties
     */
    #[ArrayShape([
        'address' => "string",
        'transaction_id' => "string",
        'qr_code_url' => 'string',
        'external_pay_link' => 'string',
        'component' => 'string'
    ])]
    public function handle(ExchangeRequest $exchangeRequest): array
    {
        $transaction = $this->transactionsStorage->get($exchangeRequest->getUniqString());

        if ($transaction === null) {
            $transaction = $this->createInvoice($exchangeRequest);

            $this->transactionsStorage->save($exchangeRequest->getUniqString(), $transaction);
        }

        return [
            'address' => $transaction->getPaymentAddress()->getAddress(),
            'transaction_id' => $transaction->getTransactionId(),
            'qr_code_url' => $transaction->get('qrCodeUrl'),
            'external_pay_link' => $transaction->get('payLink'),
            'component' => 'external_payment_form',
        ];
    }

    /**
     * @throws WhiteBitApiException|\JsonException|UnknownProperties
     */
    private function createInvoice(ExchangeRequest $exchangeRequest) : Transaction
    {
        $createInvoiceDto = new CreateFiatDepositAddressRequestDto(
            currency: $exchangeRequest->givenCurrency->code,
            amount: $exchangeRequest->given_sum,
            unique_id: $exchangeRequest->token,
            provider: ProviderName::VISAMASTER,
        );

        if (App::environment('production')) {
            $createInvoiceDto->successLink = (route(WEB_EXCHANGE_REQUESTS_SHOW_ROUTE, [$exchangeRequest->token]));
        }

        $paymentUrl = $this->whiteBitApiService->getFiatDepositAddress($createInvoiceDto);

        $transaction = new Transaction(
            TransactionIdPlaceholder::SEARCHING,
            new PaymentAddress(AddressPlaceholder::EXTERNAL_PAYMENT_FORM),
            $exchangeRequest->given_sum,
        );

        $transaction->set('payLink', $paymentUrl);
        $transaction->set('qrCodeUrl', $this->qrCodeService->getByUrl($paymentUrl));

        return $transaction;
    }
}
