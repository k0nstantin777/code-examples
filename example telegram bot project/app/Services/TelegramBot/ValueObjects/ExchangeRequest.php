<?php

namespace App\Services\TelegramBot\ValueObjects;

use App\Domains\User\Models\User;
use App\Services\Exchanger\ValueObjects\ExchangeFormAttribute;
use App\Services\TelegramBot\Enums\CalculateSumType;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\ExchangeRequestFlowState;
use App\Services\TelegramBot\Flows\ExchangeRequest\States\NewState;

class ExchangeRequest
{
    private ?int $givenCurrencyId = null;
    private ?int $receivedCurrencyId = null;
    private ?int $exchangeDirectionId = null;
    private ExchangeRequestFlowState $state;
    private ?CalculateSumType $calculateType = null;
    private ?string $email = null;
    private ?string $givenSum = null;
    private ?string $receivedSum = null;
    private ?string $commission = null;
    private array $filledFormAttributes = [];
    private array $creationValidationErrors = [];
    private array $paymentFormData = [];
    private ?string $remoteId = null;

    public function __construct(
        private readonly User $user,
    ) {
        $this->changeState(app(NewState::class));
    }

    /**
     * @return User
     */
    public function getUser() : User
    {
        return $this->user->refresh();
    }

    public function changeState(ExchangeRequestFlowState $state) : void
    {
        $this->state = $state;
        $this->state->setExchangeRequest($this);
        $this->state->afterChangeHandle();
    }

    public function getGivenCurrencyId(): ?int
    {
        return $this->givenCurrencyId;
    }

    public function setGivenCurrencyId(int $value): void
    {
        $this->givenCurrencyId = $value;
    }

    public function getReceivedCurrencyId(): ?int
    {
        return $this->receivedCurrencyId;
    }

    public function setReceivedCurrencyId(int $value): void
    {
        $this->receivedCurrencyId = $value;
    }

    /**
     * @return int|null
     */
    public function getExchangeDirectionId() : ?int
    {
        return $this->exchangeDirectionId;
    }

    /**
     * @param int $exchangeDirectionId
     */
    public function setExchangeDirectionId(int $exchangeDirectionId) : void
    {
        $this->exchangeDirectionId = $exchangeDirectionId;
    }

    /**
     * @return ExchangeRequestFlowState
     */
    public function getState() : ExchangeRequestFlowState
    {
        return $this->state;
    }

    /**
     * @return CalculateSumType|null
     */
    public function getCalculateType() : ?CalculateSumType
    {
        return $this->calculateType;
    }

    /**
     * @param CalculateSumType $calculateType
     */
    public function setCalculateType(CalculateSumType $calculateType) : void
    {
        $this->calculateType = $calculateType;
    }

    /**
     * @return string|null
     */
    public function getEmail() : ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email) : void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getGivenSum() : ?string
    {
        return $this->givenSum;
    }

    /**
     * @param string|null $givenSum
     */
    public function setGivenSum(?string $givenSum) : void
    {
        $this->givenSum = $givenSum;
    }

    /**
     * @return string|null
     */
    public function getReceivedSum() : ?string
    {
        return $this->receivedSum;
    }

    /**
     * @return string|null
     */
    public function getCommission() : ?string
    {
        return $this->commission;
    }

    /**
     * @param string $commission
     */
    public function setCommission(string $commission) : void
    {
        $this->commission = $commission;
    }

    /**
     * @param string|null $receivedSum
     */
    public function setReceivedSum(?string $receivedSum) : void
    {
        $this->receivedSum = $receivedSum;
    }

    /**
     * @return array
     */
    public function getFilledFormAttributes() : array
    {
        return $this->filledFormAttributes;
    }

    /**
     * @param array $filledFormAttributes
     */
    public function setFilledFormAttributes(array $filledFormAttributes) : void
    {
        $this->filledFormAttributes = $filledFormAttributes;
    }

    /**
     * @return array
     */
    public function getCreationValidationErrors() : array
    {
        return $this->creationValidationErrors;
    }

    /**
     * @return array
     */
    public function getPaymentFormData() : array
    {
        return $this->paymentFormData;
    }

    /**
     * @param array $paymentFormData
     */
    public function setPaymentFormData(array $paymentFormData) : void
    {
        $this->paymentFormData = $paymentFormData;
    }

    /**
     * @param array $creationValidationErrors
     */
    public function setCreationValidationErrors(array $creationValidationErrors) : void
    {
        $this->creationValidationErrors = $creationValidationErrors;
    }

    public function getReceivedRequisites() : string
    {
        foreach ($this->filledFormAttributes as $code => $value) {
            if ($code === ExchangeFormAttribute::REQUISITES_RECEIVED_CURRENCY_CODE) {
                return $value;
            }
        }

        return '';
    }

    /**
     * @return string|null
     */
    public function getRemoteId() : ?string
    {
        return $this->remoteId;
    }

    /**
     * @param string $remoteId
     */
    public function setRemoteId(string $remoteId) : void
    {
        $this->remoteId = $remoteId;
    }

    public function hasAuthData() : bool
    {
        return $this->getEmail() || $this->getUser()->getExchangerUserIdOrNull();
    }

    public function reset() : void
    {
        $this->email = null;
        $this->remoteId = null;
        $this->givenSum = null;
        $this->givenCurrencyId = null;
        $this->receivedSum = null;
        $this->commission = null;
        $this->receivedCurrencyId = null;
        $this->exchangeDirectionId = null;
        $this->calculateType = null;
        $this->filledFormAttributes = [];
        $this->creationValidationErrors = [];
        $this->paymentFormData = [];
        $this->changeState(app(NewState::class));
    }
}
