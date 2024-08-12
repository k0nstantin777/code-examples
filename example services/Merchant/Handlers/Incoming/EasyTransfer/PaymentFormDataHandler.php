<?php

namespace App\Services\Merchant\Handlers\Incoming\EasyTransfer;

use App\Models\Exchange\ExchangeRequest;
use App\Services\EasyTransfer\EasyTransferApiService;
use App\Services\EasyTransfer\Exceptions\EasyTransferApiException;
use App\Services\EasyTransfer\RequestDTOs\CreateInvoiceDto;
use App\Services\Merchant\Enums\AddressPlaceholder;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\PaymentAddress;
use App\Services\Merchant\ValueObjects\Transaction;
use App\Services\QrCode\QrCodeService;
use App\Settings\ExchangeProcessSettings;
use JetBrains\PhpStorm\ArrayShape;

class PaymentFormDataHandler
{
    public function __construct(
        private TransactionsStorage $transactionsStorage,
        private EasyTransferApiService $easyTransferApiService,
        private QrCodeService $qrCodeService,
        private ExchangeProcessSettings $exchangeProcessSettings
    ) {
    }

    /**
     * @throws EasyTransferApiException|\JsonException
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
     * @throws EasyTransferApiException|\JsonException
     */
    private function createInvoice(ExchangeRequest $exchangeRequest) : Transaction
    {
        $createInvoiceDto = new CreateInvoiceDto(
            $exchangeRequest->given_sum,
            $exchangeRequest->givenCurrency->code,
        );

        $createInvoiceDto->setClientName($exchangeRequest->customer->name);
        $createInvoiceDto->setExternalId($exchangeRequest->token);
        $createInvoiceDto->setExpiresInMinutes($this->exchangeProcessSettings->time_to_pay_exchange_request_in_minutes);
        $createInvoiceDto->setReturnUrl(route(WEB_EXCHANGE_REQUESTS_SHOW_ROUTE, [$exchangeRequest->token]));
        $createInvoiceDto->setDescription('Payment exchange request');

        $invoice = $this->easyTransferApiService->createInvoice($createInvoiceDto);

        $transaction = new Transaction(
            $invoice->getId(),
            new PaymentAddress(AddressPlaceholder::EXTERNAL_PAYMENT_FORM),
            $invoice->getAmount(),
        );

        $transaction->set('payLink', $invoice->getLink());
        $transaction->set('qrCodeUrl', $this->qrCodeService->getByUrl($invoice->getLink()));

        return $transaction;
    }
}
