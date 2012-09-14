<?php

class PaypalPaymentsStandard extends PaymentMethodAbstract
{
    const SANDBOX_URL = 'https://www.sandbox.paypal.com/us/cgi-bin/webscr';

    public function getPaymentFormAction()
    {
        return self::SANDBOX_URL;
    }

    public function getPaymentSubmitButtonText()
    {
        return "Proceed to PayPal.com to pay";
    }

    public function processPayment(Cart $cart, &$error_message)
    {
        // TODO: Implement processPayment() method.
    }
}