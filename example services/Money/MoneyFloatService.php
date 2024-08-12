<?php


namespace App\Services\Money;

use App\Services\BaseService;
use App\Services\Money\ValueObjects\Money;
use App\Services\Money\ValueObjects\MoneyFloat;

class MoneyFloatService extends BaseService
{
    private const MAX_FLOAT_PRECISION = 18;

    public function sub(MoneyFloat $first, MoneyFloat $second) : MoneyFloat
    {
        return new MoneyFloat(
            bcsub(
                $first->getAmount(),
                $second->getAmount(),
                $this->getMaxPrecision($first, $second)
            )
        );
    }

    public function add(MoneyFloat $first, MoneyFloat $second) : MoneyFloat
    {
        return new MoneyFloat(
            bcadd(
                $first->getAmount(),
                $second->getAmount(),
                self::MAX_FLOAT_PRECISION
            )
        );
    }

    public function div(MoneyFloat $first, MoneyFloat $second) : MoneyFloat
    {
        return new MoneyFloat(
            bcdiv(
                $first->getAmount(),
                $second->getAmount(),
                self::MAX_FLOAT_PRECISION
            )
        );
    }

    public function mul(MoneyFloat $first, MoneyFloat $second) : MoneyFloat
    {
        return new MoneyFloat(
            bcmul(
                $first->getAmount(),
                $second->getAmount(),
                self::MAX_FLOAT_PRECISION
            )
        );
    }

    public function compare(MoneyFloat $first, MoneyFloat $second) : int
    {
        return bccomp($first->getAmount(), $second->getAmount(), $this->getMaxPrecision($first, $second));
    }

    public function isFirstGreater(MoneyFloat $first, MoneyFloat $second) : bool
    {
        return $this->compare($first, $second) === 1;
    }

    public function isSecondGreater(MoneyFloat $first, MoneyFloat $second) : bool
    {
        return $this->compare($first, $second) === -1;
    }

    public function getMaxPrecision(MoneyFloat $first, MoneyFloat $second) : int
    {
        $resultPrecision = $first->getPrecision();
        if ($second->getPrecision() > $resultPrecision) {
            $resultPrecision = $second->getPrecision();
        }

        return $resultPrecision;
    }

    public function convertToMoney(MoneyFloat $moneyFloat) : Money
    {
        $precision = $moneyFloat->getPrecision();

        if ($precision === 0) {
            $precision = DEFAULT_MONEY_PRECISION;
        }

        $amount = $moneyFloat->getAmount() * (10 ** $precision);

        return new Money($amount, $precision);
    }

    /**
     * Calculate (amount/100) * percents
     * Get percent value from number.
     * @param MoneyFloat $amount
     * @param float $percents
     * @return MoneyFloat
     */
    public function convertFromPercents(MoneyFloat $amount, float $percents) : MoneyFloat
    {
        return $this->mul(
            $this->div(
                $amount,
                new MoneyFloat(100)
            ),
            new MoneyFloat($percents)
        );
    }

    public function isEqualZero(MoneyFloat $moneyFloat) : bool
    {
        return $this->compare($moneyFloat, new MoneyFloat(0)) === 0;
    }

    public function revertSign(MoneyFloat $moneyFloat) : MoneyFloat
    {
        return $this->mul($moneyFloat, new MoneyFloat(-1));
    }
}
