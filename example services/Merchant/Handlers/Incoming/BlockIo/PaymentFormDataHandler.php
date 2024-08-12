<?php

namespace App\Services\Merchant\Handlers\Incoming\BlockIo;

use App\Models\Exchange\ExchangeRequest;
use App\Modules\Queue\Entities\ExchangeRequest\Criterion\HasLastPaymentAddressByMerchantCriteria;
use App\Repositories\Filters\ExchangeRequest\ExchangeRequestFilter;
use App\Services\BlockIO\BlockIoService;
use App\Services\BlockIO\Exceptions\BlockIoApiException;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\ValueObjects\PaymentAddress;
use App\Services\Merchant\ValueObjects\Transaction;
use App\Services\Merchant\Storages\PaymentAddressesStorage;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\QrCode\QrCodeService;
use JetBrains\PhpStorm\ArrayShape;

class PaymentFormDataHandler
{
    public function __construct(
        private TransactionsStorage $transactionsStorage,
        private PaymentAddressesStorage $paymentAddressesStorage,
        private BlockIoService $blockIoService,
    ) {
    }

    /**
     * @throws BlockIoApiException
     * @throws  \JsonException
     */
    #[ArrayShape([
        'address' => "string",
        'qr_code_url' => "string",
        'transaction_id' => "string",
    ])]
    public function handle(ExchangeRequest $exchangeRequest): array
    {
        $transaction = $this->transactionsStorage->get($exchangeRequest->getUniqString());

        if ($transaction === null) {
            $transaction = $this->createTransaction($exchangeRequest);

            $this->transactionsStorage->save($exchangeRequest->getUniqString(), $transaction);
        }

        return [
            'address' => $transaction->getPaymentAddress()->getAddress(),
            'qr_code_url' => $transaction->get('qrCodeUrl'),
            'transaction_id' => $transaction->getTransactionId(),
        ];
    }

    /**
     * @throws BlockIoApiException
     * @throws \JsonException
     */
    private function getPaymentAddress(ExchangeRequest $exchangeRequest) : PaymentAddress
    {
        try {
            $blockIoPaymentAddress = $this->blockIoService->getNewAddress($exchangeRequest->givenCurrency->code);

            $paymentAddress = new PaymentAddress($blockIoPaymentAddress->getAddress());
        } catch (BlockIoApiException) {
            $paymentAddress =  $this->getAvailableAddress($exchangeRequest);
        }

        $this->paymentAddressesStorage->save($paymentAddress->getAddress(), $paymentAddress);
        return $paymentAddress;
    }

    /**
     * @param ExchangeRequest $exchangeRequest
     * @throws BlockIoApiException
     * @throws \JsonException
     * @return PaymentAddress
     */
    private function getAvailableAddress(ExchangeRequest $exchangeRequest) : PaymentAddress
    {
        $addresses = $this->blockIoService->getAddresses($exchangeRequest->givenCurrency->code);
        $countAddresses = count($addresses);

        if ($countAddresses === 0) {
            throw new BlockIoApiException('Have not available addresses');
        }

        $lastUsedAddress = $this->getLastUsedAddress($exchangeRequest);
        $lastPaymentAddressIndex = null;

        if ($lastUsedAddress) {
            foreach ($addresses as $index => $blockIoPaymentAddress) {
                if ($lastUsedAddress === $blockIoPaymentAddress->getAddress()) {
                    $lastPaymentAddressIndex = $index;
                    break;
                }
            }
        }

        $paymentAddress = $addresses[0];
        $startSearch = $lastPaymentAddressIndex === null ? 0 : ++$lastPaymentAddressIndex;

        for ($i = $startSearch; $i < $countAddresses; $i++) {
            $blockIoPaymentAddress = $addresses[$i];

            if ($blockIoPaymentAddress->getPendingReceivedBalance() > 0) {
                continue;
            }

            $availablePaymentAddress = $this->paymentAddressesStorage->get($blockIoPaymentAddress->getAddress());

            if ($availablePaymentAddress === null) {
                $paymentAddress = $blockIoPaymentAddress;
                break;
            }
        }

        return new PaymentAddress($paymentAddress->getAddress());
    }

    private function getLastUsedAddress(ExchangeRequest $exchangeRequest) : string
    {
        $lastExchangeRequestWithSameMerchant = ExchangeRequestFilter::getInstance()
            ->pushCriteria(new HasLastPaymentAddressByMerchantCriteria($exchangeRequest->given_merchant_id))
            ->addSortBy('created_at', 'desc')
            ->first();

        return $lastExchangeRequestWithSameMerchant->payment_address ?? '';
    }

    /**
     * @throws BlockIoApiException
     * @throws \JsonException
     */
    private function createTransaction(ExchangeRequest $exchangeRequest) : Transaction
    {
        $paymentAddress = $this->getPaymentAddress($exchangeRequest);

        $transaction = new Transaction(
            TransactionIdPlaceholder::SEARCHING,
            $paymentAddress,
            $exchangeRequest->given_sum,
        );

        $transaction->set('qrCodeUrl', QrCodeService::getInstance()->getByUrl($paymentAddress->getAddress()));

        return $transaction;
    }
}
