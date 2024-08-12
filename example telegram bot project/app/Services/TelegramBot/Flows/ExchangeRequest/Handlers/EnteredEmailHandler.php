<?php

namespace App\Services\TelegramBot\Flows\ExchangeRequest\Handlers;

use App\Services\Exchanger\Services\CustomerService;
use App\Services\Exchanger\ValueObjects\Customer;
use App\Services\TelegramBot\Exceptions\InvalidBotActionException;
use App\Services\TelegramBot\ValueObjects\ExchangeRequest;
use App\Services\TelegramBot\Services\TelegramBotApi;

class EnteredEmailHandler extends ExchangeRequestProcessingHandler
{
    public function __construct(
        TelegramBotApi $telegram,
        ExchangeRequest $exchangeRequest,
        private readonly CustomerService $customerService,
    ) {
        parent::__construct($telegram, $exchangeRequest);
    }

    /**
     * @throws InvalidBotActionException
     */
    public function handle(): void
    {
        $email = $this->update->message->text;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidBotActionException('Invalid email address');
        }

        $customer = $this->getCustomerByEmailOrNull($email);

        if ($customer) {
            throw new InvalidBotActionException('Customer already exist, please run /login and sign in');
        }

        $this->exchangeRequest->setEmail($email);

        parent::handle();
    }

    private function getCustomerByEmailOrNull(string $email) : ?Customer
    {
        return $this->customerService->getByEmailOrNull($email);
    }
}
