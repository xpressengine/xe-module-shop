<?php
/**
 * Plugin for doing payments in XE Shop
 * using Paypal Payments Standard
 *
 * https://cms.paypal.com/cms_content/US/en_US/files/developer/PP_WebsitePaymentsStandard_IntegrationGuide.pdf
 */
class PaypalPaymentsStandard extends PaymentMethodAbstract
{
    const SANDBOX_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr'
        , LIVE_URL = 'https://www.paypal.com/cgi-bin/webscr';

    /**
     * Paypal form action
     *
     * @return string
     */
    public function getPaymentFormAction()
    {
        return $this->gateway_api;
    }

    /**
     * Text that will be displayed on the payment form Submit button
     *
     * @return string
     */
    public function getPaymentSubmitButtonText()
    {
        return "Proceed to PayPal.com to pay";
    }

    /**
     * Used for getting the money from the customer
     *
     * Nothing happens here since payment is done on the Paypal website
     * And the payment confirmation notification comes via IPN
     *
     * @param Cart $cart
     * @param $error_message
     */
    public function processPayment(Cart $cart, &$error_message)
    {
    }

    /**
     * Page where user is redirected back to after
     * he completed the payment on the Paypal website
     *
     * If an order has not been created, we create it now
     * If payment is complete, we update order status to Processing
     *
     * If an error occurred, we show it to the user
     *
     * @param $cart
     * @param $module_srl
     * @throws Exception
     */
    public function onOrderConfirmationPageLoad($cart, $module_srl)
    {
        // Retrieve unique transaction id
        $tx_token = Context::get('tx');

        // If no transaction token was retrieved
        // or the user did not configure his PDT (Payment Data Transfer) token in backend
        // do nothing
        if(!$tx_token || !$this->pdt_token)
        {
            // TODO What do we do in this case?
            return;
        }

        if(!$order = $this->orderCreatedForThisTransaction($tx_token))
        {
            if($this->thisTransactionWasAlreadyProcessedAndWasInvalid($cart, $tx_token))
            {
                $this->redirectUserToOrderUnsuccessfulPageAndShowHimTheErrorMessage($cart->getTransactionErrorMessage());
                return;
            }

            // Retrieve payment info from Paypal
            $response = $this->getTransactionInfoFromPDT($tx_token);
            if($response->requestWasSuccessful())
            {
                $this->createNewOrderAndDeleteExistingCart($cart, $tx_token);
            }
            else
            {
                // We couldn't retrieve transaction info from Paypal
                ShopLogger::log("PDT request FAIL: " .print_r($response));
                throw new NetworkErrorException("There was some error from PDT");
            }
        }
        else
        {
            // Order already exists for this transaction, so we'll just display it
            // skipping any requests to paypal
            Context::set('order_srl', $order->order_srl);
            return;
        }
    }

    /**
     * Given a transaction id, checks if an order was created or not for it
     * (from an IPN call, for instance)
     *
     * @return boolean
     */
    private function orderCreatedForThisTransaction($transaction_id)
    {
        $orderRepository = new OrderRepository();
        $order = $orderRepository->getOrderByTransactionId($transaction_id);
        return $order;
    }

    /**
     * Checks if a transaction was already processed but was invalid
     * causing the order not to be created;
     * Thus, even though there is no order created, we should not parse this again
     */
    private function thisTransactionWasAlreadyProcessedAndWasInvalid(Cart $cart, $transaction_id)
    {
        return $cart->getTransactionId()
            && $cart->getTransactionId() == $transaction_id;
    }

    private function redirectUserToOrderUnsuccessfulPageAndShowHimTheErrorMessage($error_message)
    {
        $shopController = getController('shop');
        $shopController->setMessage($error_message, "error");
        $this->redirect($this->getPlaceOrderPageUrl());
    }

    /**
     * Retrieve payment info from Paypal through
     * Payment Data Transfer
     */
    private function getTransactionInfoFromPDT($tx_token)
    {
        $params = array();
        $params['cmd'] = '_notify-synch';
        $params['tx'] = $tx_token;
        $params['at'] = $this->pdt_token;

        $paypalAPI = new PaypalPaymentsStandardAPI();
        $response = $paypalAPI->request($this->gateway_api, $params);
        $response_array = explode("\n", $response);
        return new PDTResponse($response_array);
    }

    private function createNewOrderAndDeleteExistingCart($cart, $transaction_id)
    {
        $order = new Order($cart);
        $order->transaction_id = $transaction_id;
        $order->save(); //obtain srl
        $order->saveCartProducts($cart);
        $cart->delete();

        Context::set('order_srl', $order->order_srl);
        // Override cart, otherwise it would still show up with products
        Context::set('cart', null);
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

        $response = $paypalAPI->request($this->gateway_api, $decoded_args);

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

class PDTResponse
{
    public $response_array = null;

    public function __construct($response_array)
    {
        $this->response_array = $response_array;
    }

    public function requestWasSuccessful()
    {
        return $this->response_array[0] == 'SUCCESS';
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

