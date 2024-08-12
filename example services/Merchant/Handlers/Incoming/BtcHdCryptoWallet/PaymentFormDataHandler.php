<?php

namespace App\Services\Merchant\Handlers\Incoming\BtcHdCryptoWallet;

use App\Models\Exchange\ExchangeRequest;
use App\Modules\Queue\Entities\ExchangeRequest\Criterion\HasLastPaymentAddressByMerchantCriteria;
use App\Repositories\Filters\ExchangeRequest\ExchangeRequestFilter;
use App\Services\BlockStream\BlockStreamApiService;
use App\Services\BlockStream\Exceptions\BlockStreamApiException;
use App\Services\CryptoWallets\CurrencyCryptoWalletServices\BtcHdCryptoWalletService;
use App\Services\CryptoWallets\Exceptions\CryptoWalletsApiException;
use App\Services\CryptoWallets\Exceptions\CurrencyCryptoWalletException;
use App\Services\CryptoWallets\ValueObjects\HdWalletAddress;
use App\Services\Merchant\Enums\TransactionIdPlaceholder;
use App\Services\Merchant\Exceptions\HaveNotAvailablePaymentAddress;
use App\Services\Merchant\Storages\PaymentAddressesStorage;
use App\Services\Merchant\ValueObjects\PaymentAddress;
use App\Services\Merchant\ValueObjects\Transaction;
use App\Services\Merchant\Storages\TransactionsStorage;
use App\Services\QrCode\QrCodeService;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\ArrayShape;

class PaymentFormDataHandler
{
    public function __construct(
        private TransactionsStorage $transactionsStorage,
        private PaymentAddressesStorage $paymentAddressesStorage,
        private BtcHdCryptoWalletService $btcHdCryptoWalletService,
        private BlockStreamApiService $blockStreamApiService,
    ) {
    }

    /**
     * @throws CryptoWalletsApiException|CurrencyCryptoWalletException
     * @throws \JsonException
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
     * @throws CryptoWalletsApiException|CurrencyCryptoWalletException
     * @throws \JsonException
     * @throws BlockStreamApiException
     */
    private function getPaymentAddress(ExchangeRequest $exchangeRequest) : PaymentAddress
    {
        $addresses = $this->btcHdCryptoWalletService->getAddresses();
        $countAddresses = $addresses->count();
        $addressesLimit = config('services.crypto_wallets.btc_hd_wallet.addresses_limit');

        if ($countAddresses < $addressesLimit) {
            $newAddress = $this->btcHdCryptoWalletService->generateAddress();
            $paymentAddress = new PaymentAddress($newAddress->getAddress());
        } else {
            $paymentAddress =  $this->getAvailableAddress(
                $exchangeRequest,
                $addresses->reverse()->values(),
            );
        }

        $paymentAddress->set(PaymentAddressProperty::UTXOS, $this->getAddressUtxos($paymentAddress->getAddress()));

        $this->paymentAddressesStorage->save($paymentAddress->getAddress(), $paymentAddress);
        return $paymentAddress;
    }

    /**
     * @param ExchangeRequest $exchangeRequest
     * @param Collection $addresses
     * @return PaymentAddress
     * @throws HaveNotAvailablePaymentAddress
     */
    private function getAvailableAddress(ExchangeRequest $exchangeRequest, Collection $addresses) : PaymentAddress
    {
        $lastUsedAddress = $this->getLastUsedAddress($exchangeRequest);
        $lastPaymentAddressIndex = null;

        if ($lastUsedAddress) {
            foreach ($addresses as $index => $hdWalletAddress) {
                if ($lastUsedAddress === $hdWalletAddress->getAddress()) {
                    $lastPaymentAddressIndex = $index;
                    break;
                }
            }
        }

        $startSearch = $lastPaymentAddressIndex === null ? 0 : ++$lastPaymentAddressIndex;

        $paymentAddress = null;

        if ($startSearch > 0) {
            $paymentAddress = $this->searchAvailableAddressInStorage($addresses, $startSearch);
        }

        if (null === $paymentAddress) {
            $paymentAddress = $this->searchAvailableAddressInStorage($addresses, 0);
        }

        if (null === $paymentAddress) {
            throw new HaveNotAvailablePaymentAddress();
        }

        return new PaymentAddress($paymentAddress->getAddress());
    }

    private function searchAvailableAddressInStorage(Collection $addresses, int $start) : ?HdWalletAddress
    {
        $paymentAddress = null;
        for ($i = $start; $i < $addresses->count(); $i++) {
            $hdWalletAddress = $addresses->get($i);

            $busyPaymentAddress = $this->paymentAddressesStorage->get($hdWalletAddress->getAddress());

            if ($busyPaymentAddress === null) {
                $paymentAddress = $hdWalletAddress;
                break;
            }
        }

        return $paymentAddress;
    }

    /**
     * @throws BlockStreamApiException
     */
    private function getAddressUtxos(string $address) : array
    {
        return $this->blockStreamApiService->getAddressUtxos($address);
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
     * @throws CryptoWalletsApiException|CurrencyCryptoWalletException
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
