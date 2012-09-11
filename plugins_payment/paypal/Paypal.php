<?php

class Paypal extends PaymentMethodAbstract
{
    const PAYPAL_WEB_SANDBOX = 'https://www.sandbox.paypal.com/webscr';

    public function processCheckoutForm()
    {
        $vid = Context::get('vid');
        $success_url = getNotEncodedFullUrl('', 'vid', $vid
                                          , 'act', 'dispShopTestOrderConfirmation'
                                          , 'payment_method', $this->getName()
                                          , 'error_return_url', ''
        );

        $cancel_url = getNotEncodedFullUrl('', 'vid', $vid
                                        , 'act', 'dispShopTestCheckout'
                                        , 'error_return_url', ''
        );


        $paypalAPI = new PaypalAPI($this->api_username
            , $this->api_password
            , $this->signature
        );

        $paypalAPI->setExpressCheckout(10, $success_url, $cancel_url);

        if(!$paypalAPI->success)
        {
            $shopController = getController('shop');
            $shopController->setMessage($paypalAPI->error_message);
            $vid = Context::get('vid');
            return getNotEncodedUrl('', 'vid', $vid, 'act', 'dispShopTestCheckout');
        }
        else
        {
            return self::PAYPAL_WEB_SANDBOX
                            . '?cmd=_express-checkout'
                            . '&token=' . $paypalAPI->token;
        }
    }


    public function onConfirmPaymentFormLoad()
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
        $paypalAPI->doExpressCheckoutPayment($token, $payer_id, 10);

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
    const SANDBOX_API_URL = 'https://api.sandbox.paypal.com/nvp';

    private $data = array(
        'VERSION' => '94'
    );

    public function __construct($api_username, $api_password, $signature)
    {
        $this->data['USER'] = $api_username;
        $this->data['PWD'] = $api_password;
        $this->data['SIGNATURE'] = $signature;
    }

    public function setExpressCheckout($amount, $success_url, $cancel_url)
    {
        $this->data['METHOD'] = 'SetExpressCheckout';
        $this->data['PAYMENTREQUEST_0_AMT'] = $amount;
        $this->data['RETURNURL'] = $success_url;
        $this->data['CANCELURL'] = $cancel_url;
        $this->data['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Sale';

        $response = $this->request(self::SANDBOX_API_URL, $this->data);

        unset($this->data['METHOD']
            , $this->data['PAYMENTREQUEST_0_AMT']
            , $this->data['RETURNURL']
            , $this->data['CANCELURL']
            , $this->data['PAYMENTREQUEST_0_PAYMENTACTION']
            );

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

        $response = $this->request(self::SANDBOX_API_URL, $this->data);

        unset($this->data['METHOD'], $this->data['TOKEN']);
        return $response;
    }

    public function doExpressCheckoutPayment($token, $payer_id, $amount, $currency = 'USD')
    {
        $this->data['METHOD'] = 'DoExpressCheckoutPayment';
        $this->data['TOKEN'] = $token;
        $this->data['PAYERID'] = $payer_id;

        $this->data['PAYMENTREQUEST_0_AMT'] = $amount;
        $this->data['PAYMENTREQUEST_0_CURRENCYCODE'] = $currency;
        $this->data['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Sale';

        $response = $this->request(self::SANDBOX_API_URL, $this->data);

        unset($this->data['METHOD']
        , $this->data['TOKEN']
        , $this->data['PAYERID']
        , $this->data['PAYMENTREQUEST_0_AMT']
        , $this->data['PAYMENTREQUEST_0_CURRENCYCODE']
        , $this->data['PAYMENTREQUEST_0_PAYMENTACTION']
        );

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