<?php

namespace App\Services\Merchant\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class PaymentFormCreateException extends HttpException
{
    public function __construct(
        int $statusCode = 400,
        ?string $message = '',
        Throwable $previous = null,
        array $headers = [],
        ?int $code = 0
    ) {
        if (!$message) {
            $message = (string) __('Error creating a payment form, please contact support');
        }

        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }
}
