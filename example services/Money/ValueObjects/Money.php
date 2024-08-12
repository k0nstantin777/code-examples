<?php


namespace App\Services\Money\ValueObjects;

class Money
{
    private int $amount;
    private int $precision;

    public function __construct(int $amount, int $precision)
    {
        $this->precision = $precision;
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * @param int $precision
     */
    public function setPrecision(int $precision): void
    {
        $this->precision = $precision;
    }

    public function toFloat() : float
    {
        return number_format($this->amount * (0.1 ** $this->precision), $this->precision);
    }
}
