<?php

namespace App\Services\Merchant\Handlers\Incoming\WhiteBit\CryptoCurrency;

use App\Models\Exchange\ExchangeRequest;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\Merchant\ValueObjects\PaymentAddress;
use App\Services\Merchant\ValueObjects\Transaction;
use App\Services\QrCode\QrCodeService;
use App\Services\WhiteBit\PrivateApi\Exceptions\WhiteBitApiException;
use App\Services\WhiteBit\PrivateApi\RequestDTOs\GetCryptoDepositAddressRequestDto;
use App\Services\WhiteBit\PrivateApi\WhiteBitPrivateApiService;
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
        ];
    }

    /**
     * @throws WhiteBitApiException|\JsonException|UnknownProperties
     */
    private function createInvoice(ExchangeRequest $exchangeRequest) : Transaction
    {
        $createInvoiceDto = new GetCryptoDepositAddressRequestDto(
            ticker: $exchangeRequest->givenCurrency->code,
            network: $exchangeRequest->givenCurrency->network ?? '',
        );

        $paymentAddress = $this->whiteBitApiService->createNewCryptoDepositAddress($createInvoiceDto);

        $transaction = new Transaction(
            TransactionIdPlaceholder::SEARCHING,
            new PaymentAddress($paymentAddress),
            $exchangeRequest->given_sum,
        );

        $transaction->set('qrCodeUrl', $this->qrCodeService->getByUrl($paymentAddress));

        return $transaction;
    }
}
