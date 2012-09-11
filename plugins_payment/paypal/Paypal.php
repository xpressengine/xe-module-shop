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

    public function processPayment(Cart $cart, &$error_message)
    {
        // TODO: Implement processPayment() method.
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

        $response_array = array();
        parse_str($response, $response_array);

        $this->ack = $response_array['ACK'];
        if($this->ack != 'Success')
        {
            $this->success = false;
            $this->error_message = $response_array['L_SHORTMESSAGE0'] . ' (' .  $response_array['L_ERRORCODE0'] . ' ' . $response_array['L_LONGMESSAGE0'] . ')';
        }
        else
        {
            $this->success = true;
            $this->token = $response_array['TOKEN'];
            $this->correlation_id = $response_array['CORRELATIONID'];

        }

        // return $response_array;
//        if($response_array['ACK'] != 'Success')
//        {
//            $shopController = getController('shop');
//            $shopController->setMessage($response_array['SHORTMESSAGE0']);
//
//        }

/**
 * TIMESTAMP=2012%2d09%2d10T16%3a33%3a23Z
 * &CORRELATIONID=aeb509123e16d
 * &ACK=Failure
 * &VERSION=94
 * &BUILD=3622349
 * &L_ERRORCODE0=10471
 * &L_ERRORCODE1=10472
 * &L_SHORTMESSAGE0=Transaction%20refused%20because%20of%20an%20invalid%20argument%2e%20See%20additional%20error%20messages%20for%20details%2e
 * &L_SHORTMESSAGE1=Transaction%20refused%20because%20of%20an%20invalid%20argument%2e%20See%20additional%20error%20messages%20for%20details%2e
 * &L_LONGMESSAGE0=ReturnURL%20is%20invalid%2e
 * &L_LONGMESSAGE1=CancelURL%20is%20invalid%2e
 * &L_SEVERITYCODE0=Error
 * &L_SEVERITYCODE1=Error
 */


    }
}