<?php


namespace App\Services\Money\ValueObjects;

class MoneyFloat
{
    public function __construct(
        private readonly string $amount,
        private readonly ?int $precision = null
    ) {
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        if ($this->isScientificNotation($this->amount)) {
            return $this->convertFromScientificNotation($this->amount);
        }

        return $this->amount;
    }

    public function getPrecision() : int
    {
        return $this->precision ?? numberOfDecimals($this->getAmount());
    }

    private function isScientificNotation(string $value) : bool
    {
        return preg_match('/\d.\d+e[+-]\d+/i', $value);
    }

    private function convertFromScientificNotation(string $value) : string
    {
        return rtrim(sprintf('%.18f', $value), '0');
    }

    public function __toString(): string
    {
        return $this->getAmount();
    }
}
