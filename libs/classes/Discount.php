<?php
// florin, 9/27/12 3:45 PM 
abstract class Discount
{
    const
        WHEN_BEFORE_TAXES = 'pre_taxes',
        WHEN_AFTER_TAXES = 'post_taxes';

    private $amount, $when, $value, $VAT;

    abstract public function calculate();

    public function __construct($value, $amount, $when, $VAT)
    {
        if (!in_array(self::WHEN_AFTER_TAXES, self::WHEN_BEFORE_TAXES)) {
            throw new Exception('Invalid $when');
        }
        $this->setAmount($amount)->setValue($value)->setWhen($when)->setVAT($VAT);
    }

    public function setVAT($VAT)
    {
        $this->VAT = $VAT;
        return $this;
    }

    public function getVAT()
    {
        return $this->VAT;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
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

    public function setWhen($when)
    {
        $this->when = $when;
        return $this;
    }

    public function getWhen()
    {
        return $this->when;
    }

}

class FixedAmountDiscount extends Discount
{

    public function calculate()
    {

    }

}

class PercentageDiscount extends Discount
{
    public function calculate()
    {

    }
}