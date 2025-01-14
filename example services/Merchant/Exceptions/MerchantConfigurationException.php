<?php

namespace App\Services\Merchant\Exceptions;

use Exception;
use Throwable;

class MerchantConfigurationException extends Exception
{
    public function __construct($message = "", $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
