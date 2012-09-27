<?php
// florin, 9/27/12 3:45 PM 
abstract class Discount
{
    private $value, $discountValue, $minValueForDiscount, $VATPercent, $calculateBeforeApplyingVAT, $currency;

    abstract public function getName();
    abstract public function getDescription();
    abstract public function validate();
    abstract public function calculate($value, $discountValue);

    public function __construct($value, $discountValue, $minValueForDiscount=null, $VATPercent=0, $calculateBeforeApplyingVAT=false, $currency=null)
    {
        $this
            ->setValue($value)
            ->setDiscountValue($discountValue)
            ->setMinValueForDiscount($minValueForDiscount)
            ->setVATPercent($VATPercent)
            ->setCalculateBeforeApplyingVAT($calculateBeforeApplyingVAT)
            ->setCurrency($currency)
            ->validate();
    }

    public function getValueDiscounted()
    {
        if ($this->getMinValueForDiscount() > $this->getValue()) {
            return $this->getValue();
        }
        return $this->getValue() - $this->getReductionValue();
    }

    public function getReductionValue()
    {
        if ($this->getMinValueForDiscount() > $this->getValue()) {
            return 0;
        }
        $value = ($this->calculateBeforeApplyingVAT() ? $this->getValueWithoutVAT() : $this->getValue());
        return $this->calculate($value, $this->getDiscountValue());
    }

    public function getValueWithoutVAT()
    {
        return $this->getValue() - $this->getVATPercent() / 100 * $this->getValue();
    }

    //region Getters/setters
    public function setDiscountValue($amount)
    {
        $this->discountValue = $amount;
        return $this;
    }
    public function getDiscountValue()
    {
        return $this->discountValue;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    public function getValue()
    {
        return $this->value;
    }

    public function setVATPercent($VATPercent)
    {
        if ($VATPercent < 0 || $VATPercent > 99.9) {
            throw new Exception("Invalit VAT '$VATPercent'");
        }
        $this->VATPercent = $VATPercent;
        return $this;
    }
    public function getVATPercent()
    {
        return $this->VATPercent;
    }

    public function setCalculateBeforeApplyingVAT($calculateBeforeApplyingVAT)
    {
        $this->calculateBeforeApplyingVAT = $calculateBeforeApplyingVAT;
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
        return "You get {$this->getDiscountValue()}{$this->getCurrency()} discount when your cart value steps over {$this->getMinValueForDiscount()}{$this->getCurrency()}";
    }

    public function validate()
    {
        if ($this->getValue() < $this->getDiscountValue()) {
            throw new Exception("{$this->getValue()} should be bigger than the fix amount discount value {$this->getDiscountValue()}");
        }
    }

    public function calculate($value, $discountValue)
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
        return "Your discount is a percent of the total order when you step over {$this->getMinValueForDiscount()}{$this->getCurrency()}";
    }

    public function validate()
    {
        if ($this->getDiscountValue() > 99.9 || $this->getDiscountValue() <= 0.1 ) {
            throw new Exception('Discount value should be between 1 and 99');
        }
    }

    public function calculate($value, $discountValue)
    {
        return $discountValue / 100 * $value;
    }
}