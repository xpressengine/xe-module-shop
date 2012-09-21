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

    /**
     * Page where user is redirected back to after
     * he completed the payment on the Paypal website
     *
     * If an order has not been created, we create it now
     * If payment is complete, we update order status to Processing
     *
     * @param $cart
     * @param $module_srl
     * @throws Exception
     */
    public function onOrderConfirmationPageLoad($cart, $module_srl)
    {
        // Retrieve unique transaction id
        $tx_token = Context::get('tx');
        if(!$tx_token || !$this->pdt_token)
        {
            return;
        }

        // Check if order has not been created already (from an IPN call, for instance)
        $orderRepository = new OrderRepository();
        $order = $orderRepository->getOrderByTransactionId($tx_token);
        if(!$order)
        {
            // Check if transaction was not already processed by IPN and invalid
            if($cart->getExtra("transaction_id") && $cart->getExtra("transaction_id") == $tx_token)
            {
                $shopController = getController('shop');
                $shopController->setMessage($cart->getExtra('transaction_message'), "error");
                $this->redirect($this->getPlaceOrderPageUrl());
            }

            // Retrieve payment info from Paypal
            $params = array();
            $params['cmd'] = '_notify-synch';
            $params['tx'] = $tx_token;
            $params['at'] = $this->pdt_token;

            $paypalAPI = new PaypalPaymentsStandardAPI();
            $response = $paypalAPI->request(self::SANDBOX_URL, $params);
            $response_array = explode("\n", $response);
            if($response_array[0] == 'SUCCESS')
            {
                $order = new Order($cart);
                $order->transaction_id = $tx_token;
                $order->save(); //obtain srl
                $order->saveCartProducts($cart);
                $cart->delete();

                Context::set('order_srl', $order->order_srl);
                // Override cart, otherwise it would still show up with products
                Context::set('cart', null);
            }
            else
            {
                throw new Exception("There was some error from PDT");
            }
        }

        Context::set('order_srl', $order->order_srl);
        return;
    }

    /**
     * Handles all IPN notifications from Paypal
     */
    public function notify($cart)
    {
        // 1. Retrieve all POST data received and post back to paypal, to make sure
        // the request sender is not fake

        // Do not retrieve data with Context::getRequestVars() because it skips empty values
        // causing the Paypal validation to fail
        $args = $_POST;
        if(__DEBUG__)
        {
            ShopLogger::log("Received IPN Notification: " . http_build_query($args));
        }

        $paypalAPI = new PaypalPaymentsStandardAPI();
        $paypal_info = $paypalAPI->decodeArray($args);
        $decoded_args = array_merge(array('cmd' => '_notify-validate'), $paypal_info);

        $response = $paypalAPI->request(self::SANDBOX_URL, $decoded_args);

        if($response == 'VERIFIED')
        {
            ShopLogger::log("Successfully validated IPN data");

            // assign posted variables to local variables
            $payment_status = $paypal_info['payment_status'];
            $payment_amount = $paypal_info['mc_gross'];
            $payment_currency = $paypal_info['mc_currency'];
            $txn_id = $paypal_info['txn_id'];
            $txn_type = $paypal_info['txn_type'];
            $receiver_email = $paypal_info['receiver_email'];
            $payer_email = $paypal_info['payer_email'];
            $cart_srl = $paypal_info['custom'];

            // If message type is not related to a cart payment, return
            // There is nothing we will do for the moment; settlements must be done by hand for special situations
            if($txn_type != 'cart') return;

            // 2. If the source of the POST is correct, we now need to check that data is also valid
            // check that txn_id has not been previously processed
            $orderRepository = new OrderRepository();
            $order = $orderRepository->getOrderByTransactionId($txn_id);
            if(!$order)
            {
                $cart = new Cart($cart_srl);
                // check that receiver_email is your Primary PayPal email
                if($receiver_email != $this->business_account)
                {
                    ShopLogger::log("Possible fraud - invalid receiver email: " . $receiver_email);
                    $cart->setExtra("transaction_id", $txn_id);
                    $cart->setExtra("transaction_message", "There was a problem processing your payment. Your order could not be completed.");
                    $cart->save();
                    return;
                }

                // check the payment_status is Completed
                if($payment_status != 'Completed')
                {
                    ShopLogger::log("Payment is not completed. Payment status [" . $payment_status . "] received");
                    $cart->setExtra("transaction_id", $txn_id);
                    $cart->setExtra("transaction_message", "Your payment was not completed. Your order was not created.");
                    $cart->save();
                }

                if($cart->getTotal() != $payment_amount || $cart->getCurrency() != $payment_currency)
                {
                    ShopLogger::log("Invalid payment. " . PHP_EOL
                        . "Payment amount [" . $payment_amount . "] instead of " . $cart->getTotal() . PHP_EOL
                        . "Payment currency [" . $payment_currency . "] instead of " . $cart->getCurrency()
                    );
                    $cart->setExtra("transaction_id", $txn_id);
                    $cart->setExtra("transaction_message", "Your payment was invalid. Your order was not created.");
                    $cart->save();
                }

                // 3. If the source of the POST is correct, we can now use the data to create an order
                // based on the message received
                $order = new Order($cart);
                $order->transaction_id = $txn_id;
                $order->save();
                $order->saveCartProducts($cart);
                $cart->delete();
                Context::set('cart', null);
                Context::set('order_srl', $order->order_srl);
            }
        }
        else
        {
            ShopLogger::log("Invalid IPN data received: " . $response);
        }

    }
}

class PaypalPaymentsStandardAPI extends PaymentAPIAbstract
{
    private function processArray($data, $function_name)
    {
        $new_data = array();
        $keys = array_keys($data);
        foreach($keys as $key)
        {
            $new_data[$key] = $function_name($data[$key]);
        }
        return $new_data;
    }

    public function decodeArray($data)
    {
        return $this->processArray($data, 'urldecode');
    }

    public function encodeArray($data)
    {
        return $this->processArray($data, 'urlencode');
    }
}

