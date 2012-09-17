<?php

class CashOnDelivery extends PaymentMethodAbstract
{
    public function processPayment(Cart $cart, &$error_message)
    {
        return true;
    }
}