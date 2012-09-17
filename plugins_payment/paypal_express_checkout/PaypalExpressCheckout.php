<?php

class PaypalExpressCheckout extends PaymentMethodAbstract
{
    const PAYPAL_WEB_SANDBOX = 'https://www.sandbox.paypal.com/webscr';

    public function getSelectPaymentHtml()
    {
        return '<img src="https://www.paypal.com/en_US/i/logo/PayPal_mark_37x23.gif"
                    align="left"
                    style="margin-right:7px;">
                    <span style="font-size:11px; font-family: Arial, Verdana;">
                    The safer, easier way to pay.
                    </span>';
    }

    private function getItemsFromCart(Cart $cart)
    {
        $items = array();
        foreach($cart->getProducts() as $product)
        {
            $item = new stdClass();
            $item->name = $product->title;
            $item->number = $product->sku;
            $item->description = substr($product->short_description, 0, 127);
            $item->amount = number_format($product->price, 2);
            $item->quantity = $product->quantity;
            $items[] = $item;
        }
        return $items;
    }

    public function onCheckoutFormSubmit(Cart $cart, &$error_message)
    {
        $success_url = $this->getPlaceOrderPageUrl();
        $cancel_url = $this->getCheckoutPageUrl();

        $paypalAPI = new PaypalAPI($this->api_username
            , $this->api_password
            , $this->signature
        );

        // Prepare cart info
        $items = $this->getItemsFromCart($cart);

        $paypalAPI->setExpressCheckout(
            $items
            , number_format($cart->getItemTotal(),2)
            , 0
            , number_format($cart->getShippingCost(),2)
            , number_format($cart->getTotal(), 2)
            , 'USD'
            , $success_url
            , $cancel_url);

        if(!$paypalAPI->success)
        {
            $error_message = $paypalAPI->error_message;
            return false;
        }
        else
        {
            // Redirect to PayPal login
            $this->redirect(self::PAYPAL_WEB_SANDBOX
                            . '?cmd=_express-checkout'
                            . '&token=' . $paypalAPI->token);
        }
    }


    public function onPlaceOrderFormLoad()
    {
        $token = Context::get('token');
        $paypalAPI = new PaypalAPI($this->api_username
            , $this->api_password
            , $this->signature
        );
        $customer_info = $paypalAPI->getExpressCheckoutDetails($token);
        Context::set('payer_id', $customer_info['PAYERID']);
    }

    public function processPayment(Cart $cart, &$error_message)
    {
        $payer_id = Context::get('payer_id');
        $token = Context::get('token');

        $paypalAPI = new PaypalAPI($this->api_username
            , $this->api_password
            , $this->signature
        );
        // Prepare cart info
        $items = $this->getItemsFromCart($cart);

        $paypalAPI->doExpressCheckoutPayment($token
            , $payer_id
            , $items
            , number_format($cart->getItemTotal(),2)
            , 0
            , number_format($cart->getShippingCost(),2)
            , number_format($cart->getTotal(), 2)
            , 'USD'
        );

        if(!$paypalAPI->success)
        {
            $error_message = $paypalAPI->error_message;
            return false;
        }
        else
        {
            Context::set('payment_status', $paypalAPI->payment_status);
            return true;
        }
    }
}

class PaypalAPI extends PaymentAPIAbstract
{
    const SANDBOX_API_URL = 'https://api-3t.sandbox.paypal.com/nvp';

    private $setup = array(
        'VERSION' => '94'
    );
    private $data = array();

    public function __construct($api_username, $api_password, $signature)
    {
        $this->setup['USER'] = $api_username;
        $this->setup['PWD'] = $api_password;
        $this->setup['SIGNATURE'] = $signature;
    }

    private function addItemsInfo($items)
    {
        $i = 0;
        foreach($items as $item)
        {
            $this->data["L_PAYMENTREQUEST_0_NAME" . $i] = $item->name;
            $this->data["L_PAYMENTREQUEST_0_NUMBER" . $i]  = $item->number;
            $this->data["L_PAYMENTREQUEST_0_DESC" . $i]  = $item->description;
            $this->data["L_PAYMENTREQUEST_0_AMT" . $i]  = $item->amount;
            $this->data["L_PAYMENTREQUEST_0_QTY" . $i]  = $item->quantity;
            $i++;
        }
    }

    private function addItemsTotal($items_total)
    {
        $this->data["PAYMENTREQUEST_0_ITEMAMT"] = $items_total;
    }

    private function addTaxTotal($tax_total)
    {
        $this->data["PAYMENTREQUEST_0_TAXAMT"] = $tax_total;
    }

    private function addShippingTotal($shipping_total)
    {
        $this->data["PAYMENTREQUEST_0_SHIPPINGAMT"] = $shipping_total;
    }

    private function addOrderTotal($order_total)
    {
        $this->data["PAYMENTREQUEST_0_AMT"] = $order_total;
    }

    private function addCurrency($currency = 'USD')
    {
        $this->data['PAYMENTREQUEST_0_CURRENCYCODE'] = $currency;
    }

    private function addPaymentAction($action = 'Sale')
    {
        $this->data['PAYMENTREQUEST_0_PAYMENTACTION'] = $action;
    }

    public function setExpressCheckout(
        $items
        , $item_total
        , $tax_total
        , $shipping_total
        , $order_total
        , $currency = 'USD'
        , $success_url
        , $cancel_url
    ){

        $this->data['METHOD'] = 'SetExpressCheckout';

        if($items) $this->addItemsInfo($items);
        if($item_total) $this->addItemsTotal($item_total);
        if($tax_total) $this->addTaxTotal($tax_total);
        if($shipping_total) $this->addShippingTotal($shipping_total);
        $this->addOrderTotal($order_total);
        $this->addCurrency($currency);
        $this->addPaymentAction();

        $this->data['RETURNURL'] = $success_url;
        $this->data['CANCELURL'] = $cancel_url;

        $response = $this->request(self::SANDBOX_API_URL, array_merge($this->setup, $this->data));

        unset($this->data);
        $this->data = array();

        $this->ack = $response['ACK'];
        if($this->ack != 'Success')
        {
            $this->success = false;
            $this->error_message = $response['L_SHORTMESSAGE0'] . ' (' .  $response['L_ERRORCODE0'] . ' ' . $response['L_LONGMESSAGE0'] . ')';
        }
        else
        {
            $this->success = true;
            $this->token = $response['TOKEN'];
            $this->correlation_id = $response['CORRELATIONID'];
        }
    }

    public function getExpressCheckoutDetails($token)
    {
        $this->data['METHOD'] = 'GetExpressCheckoutDetails';
        $this->data['TOKEN'] = $token;

        $response = $this->request(self::SANDBOX_API_URL, array_merge($this->setup, $this->data));

        unset($this->data);
        $this->data = array();
        return $response;
    }

    /**
     * @param $token
     * @param $payer_id
     * @param $amount the format must have a decimal point with exactly
     *                  two digits to the right and an optional thousands
     *                  separator to the left, which must be a comma.
     * @param string $currency
     * @return bool
     */
    public function doExpressCheckoutPayment($token, $payer_id
        , $items
        , $item_total
        , $tax_total
        , $shipping_total
        , $order_total
        , $currency = 'USD')
    {
        $this->data['METHOD'] = 'DoExpressCheckoutPayment';
        $this->data['TOKEN'] = $token;
        $this->data['PAYERID'] = $payer_id;

        if($items) $this->addItemsInfo($items);
        if($item_total) $this->addItemsTotal($item_total);
        if($tax_total) $this->addTaxTotal($tax_total);
        if($shipping_total) $this->addShippingTotal($shipping_total);
        $this->addOrderTotal($order_total);
        $this->addCurrency($currency);
        $this->addPaymentAction();

        $response = $this->request(self::SANDBOX_API_URL, array_merge($this->setup, $this->data));

        unset($this->data);
        $this->data = array();

        $this->ack = $response['ACK'];
        if($this->ack != 'Success')
        {
            $this->success = false;
            $this->error_message = $response['L_SHORTMESSAGE0'] . ' (' .  $response['L_ERRORCODE0'] . ' ' . $response['L_LONGMESSAGE0'] . ')';
        }
        else
        {
            $this->success = true;
            $this->token = $response['TOKEN'];
            $this->correlation_id = $response['CORRELATIONID'];

            // TODO Retrieve payment info and send to final form user sees
        }

        return true;
    }


}