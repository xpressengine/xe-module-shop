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

    public function onOrderConfirmationPageLoad($module_srl)
    {
        // Check if cart doesn't exist anymore, which means the order has already been created
        $cartRepo = new CartRepository();
        $logged_info = Context::get('logged_info');
        $cart = $cartRepo->getCart($module_srl, null, $logged_info->member_srl, session_id());
        $orderRepository = new OrderRepository();
        // TODO Check if cart exists and if order has not been already created
        if(!$cart)
        {
            // TODO What to check for not logged in users?
            $order = $orderRepository->getLastOrder($module_srl, $logged_info->member_srl);
            Context::set('order_srl', $order->order_srl);
            return;
        }

        $tx_token = Context::get('tx');
        if(!$tx_token || !$this->pdt_token)
        {
            throw new Exception("Invalid input. Please add transaction token and authorization token.");
        }

        $params = array();
        $params['cmd'] = '_notify-synch';
        $params['tx'] = $tx_token;
        $params['at'] = $this->pdt_token;

        $paypalAPI = new PaypalPaymentsStandardAPI();
        $response = $paypalAPI->request(self::SANDBOX_URL, $params);
        $response_array = explode("\n", $response);
        if($response_array[0] == 'SUCCESS')
        {
            $order = $orderRepository->getOrderFromCart($cart);
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
}

class PaypalPaymentsStandardAPI extends PaymentAPIAbstract
{

}