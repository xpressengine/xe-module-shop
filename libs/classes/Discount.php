<?php
// florin, 9/27/12 3:45 PM 
abstract class Discount
{
    const DISCOUNT_TYPE_FIXED_AMOUNT = 'fixed_amount',
        DISCOUNT_TYPE_PERCENTAGE = 'percentage',
        PHASE_BEFORE_VAT = 'pre_taxes',
        PHASE_AFTER_VAT = 'post_taxes';

    private $value, $discountAmount, $minValueForDiscount, $VATPercent, $calculateBeforeApplyingVAT, $currency;

    abstract public function getName();
    abstract public function getDescription();
    abstract protected function validate($value, $discountValue);
    abstract protected function calculate($value, $discountValue);

    public function __construct($totalValue, $discountValue, $minValueForDiscount=null, $VATPercent=0, $calculateBeforeApplyingVAT=false, $currency=null)
    {
        $this
            ->setTotalValue($totalValue)
            ->setDiscountAmount($discountValue)
            ->setMinValueForDiscount($minValueForDiscount)
            ->setVATPercent($VATPercent)
            ->setCalculateBeforeVAT($calculateBeforeApplyingVAT)
            ->setCurrency($currency)
            ->validate($this->getTotalValue(), $this->getDiscountAmount());
    }

    public function getValueDiscounted()
    {
        if ($this->getMinValueForDiscount() > $this->getTotalValue()) {
            return $this->getTotalValue();
        }
        return $this->getTotalValue() - $this->getReductionValue();
    }

    public function getReductionValue()
    {
        $minValueForDiscount = $this->getMinValueForDiscount();
        $totalValue = $this->getTotalValue();
        $calculateBeforeApplyingVAT = $this->calculateBeforeApplyingVAT();
        $valueWithoutVAT = $this->getValueWithoutVAT();
        $discountAmount = $this->getDiscountAmount();
        if ($minValueForDiscount > $totalValue) return 0;
        return $this->calculate(( $calculateBeforeApplyingVAT == false ? $totalValue : $valueWithoutVAT ), $discountAmount);
    }

    public function getValueWithoutVAT()
    {
        return $this->getTotalValue() / ( 1 + $this->getVATPercent() / 100);
    }

    //region Getters/setters
    public function setDiscountAmount($amount)
    {
        $this->discountAmount = $amount;
        return $this;
    }
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    public function setTotalValue($value)
    {
        $this->value = $value;
        return $this;
    }
    public function getTotalValue()
    {
        return $this->value;
    }

    public function setVATPercent($VATPercent)
    {
        if ($VATPercent < 0 || $VATPercent > 99.9) {
            throw new ShopException("Invalit VAT '$VATPercent'");
        }
        $this->VATPercent = $VATPercent;
        return $this;
    }
    public function getVATPercent()
    {
        return $this->VATPercent;
    }

    public function setCalculateBeforeVAT($calculateBeforeApplyingVAT)
    {
        if ($calculateBeforeApplyingVAT === self::PHASE_AFTER_VAT || $calculateBeforeApplyingVAT === self::PHASE_BEFORE_VAT) {
            $this->calculateBeforeApplyingVAT = self::PHASE_AFTER_VAT ? false : true;
        }
        elseif (is_bool($calculateBeforeApplyingVAT)) {
            $this->calculateBeforeApplyingVAT = $calculateBeforeApplyingVAT;
        }
        else throw new ShopException('Invalid setting');
        return $this;
    }
    public function calculateBeforeApplyingVAT()
    {
        return $this->calculateBeforeApplyingVAT;
    }

    public function setMinValueForDiscount($minValueForDiscount)
    {
        $this->minValueForDiscount = $minValueForDiscount;
        return $this;
    }
    public function getMinValueForDiscount()
    {
        return $this->minValueForDiscount;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }
    public function getCurrency()
    {
        return $this->currency;
    }
    //endregion

}


class FixedAmountDiscount extends Discount
{

    public function getName()
    {
        return "Fixed Amount Discount";
    }

    public function getDescription()
    {
        return "You get {$this->getDiscountAmount()}{$this->getCurrency()} discount when your cart value steps over {$this->getMinValueForDiscount()}{$this->getCurrency()}";
    }

    protected function validate($value, $discountValue)
    {
        if ($value < $discountValue) {
            throw new ShopException("{$this->getTotalValue()} should be bigger than the fix amount discount value {$this->getDiscountAmount()}");
        }
    }

    protected function calculate($value, $discountValue)
    {
        return $discountValue;
    }

}


class PercentageDiscount extends Discount
{
    public function getName()
    {
        return "Percentage Discount";
    }

    public function getDescription()
    {
        return "{$this->getDiscountAmount()}% of your total order gets discounted when you step over {$this->getMinValueForDiscount()}{$this->getCurrency()}";
    }

    protected function validate($value, $discountValue)
    {
        if ($discountValue > 99.9 || $discountValue < 0.1 ) {
            throw new ShopException('Discount value should be between 1 and 99');
        }
    }

    protected function calculate($value, $discountValue)
    {
        return $discountValue / 100 * $value;
    }
}