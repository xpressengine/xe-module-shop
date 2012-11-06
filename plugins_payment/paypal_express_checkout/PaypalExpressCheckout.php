<?php

class PaypalExpressCheckout extends PaymentMethodAbstract
{
    const SANDBOX_URL = 'https://www.sandbox.paypal.com/webscr'
		, LIVE_URL = 'https://www.paypal.com/webscr';

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
            $item->amount = ShopDisplay::numberFormat($product->price);
            $item->quantity = $product->quantity;
            $items[] = $item;
        }

		if($cart->getDiscountAmount() > 0)
		{
			$item = new stdClass();
			$item->name = 'Discount';
			$item->description = $cart->getDiscountName();
			$item->amount = -1 * $cart->getDiscountAmount();
			$item->quantity = 1;
			$items[] = $item;
		}
        return $items;
    }

    public function onCheckoutFormSubmit(Cart $cart, &$error_message)
    {
        $success_url = $this->getPlaceOrderPageUrl();
        $cancel_url = $this->getCheckoutPageUrl();

        $paypalAPI = new PaypalExpressCheckoutAPI( $this->gateway_api == PaypalExpressCheckout::LIVE_URL
			, $this->api_username
            , $this->api_password
            , $this->signature
        );

        // Get shop info
        $shop_info = new ShopInfo($cart->module_srl);

        // Prepare cart info
        $items = $this->getItemsFromCart($cart);

        $paypalAPI->setExpressCheckout(
            $items
            , ShopDisplay::numberFormat($cart->getItemTotal() - $cart->getDiscountAmount())
            , 0
            , ShopDisplay::numberFormat($cart->getShippingCost())
            , ShopDisplay::numberFormat($cart->getTotal())
            , $shop_info->getCurrency()
            , $success_url
            , $cancel_url);

        if(!$paypalAPI->success)
        {
            $error_message = $paypalAPI->error_message;
            return FALSE;
        }
        else
        {
            // Redirect to PayPal login
            $this->redirect($this->gateway_api
                            . '?cmd=_express-checkout'
                            . '&token=' . $paypalAPI->token);
        }
    }


    public function onPlaceOrderFormLoad()
    {
        $token = Context::get('token');
        $paypalAPI = new PaypalExpressCheckoutAPI($this->gateway_api == PaypalExpressCheckout::LIVE_URL
			, $this->api_username
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

        $paypalAPI = new PaypalExpressCheckoutAPI($this->gateway_api == PaypalExpressCheckout::LIVE_URL
			, $this->api_username
            , $this->api_password
            , $this->signature
        );

        // Get shop info
        $shop_info = new ShopInfo($cart->module_srl);

        // Prepare cart info
        $items = $this->getItemsFromCart($cart);

        $paypalAPI->doExpressCheckoutPayment($token
            , $payer_id
            , $items
            , ShopDisplay::numberFormat($cart->getItemTotal())
            , 0
            , ShopDisplay::numberFormat($cart->getShippingCost())
            , ShopDisplay::numberFormat($cart->getTotal())
            , $shop_info->getCurrency()
        );

        if(!$paypalAPI->success)
        {
            $error_message = $paypalAPI->error_message;
            return FALSE;
        }
        else
        {
            Context::set('payment_status', $paypalAPI->payment_status);
            return TRUE;
        }
    }

	/**
	 * Make sure all mandatory fields are set
	 */
	public function isConfigured(&$error_message = 'msg_invalid_request')
	{
		if(isset($this->api_username)
			&& isset($this->api_password)
			&& isset($this->gateway_api)
			&& isset($this->signature))
		{
			$error_message = 'msg_paypal_express_missing_fields';
			return true;
		}
		return false;
	}
}

class PaypalExpressCheckoutAPI extends APIAbstract
{
    const SANDBOX_API_URL = 'https://api-3t.sandbox.paypal.com/nvp'
		, LIVE_API_URL = 'https://api-3t.paypal.com/nvp';

	private $gateway_api = null;

    private $setup = array(
        'VERSION' => '94'
    );
    private $data = array();

    public function __construct($is_live, $api_username, $api_password, $signature)
    {
		if($is_live)
		{
			$this->gateway_api = PaypalExpressCheckoutAPI::LIVE_API_URL;
		}
		else
		{
			$this->gateway_api = PaypalExpressCheckoutAPI::SANDBOX_API_URL;
		}
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

	private function disablePaypalShippingAddresses()
	{
		$this->data["NOSHIPPING"] = 1;
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
		$this->disablePaypalShippingAddresses();
        $this->addOrderTotal($order_total);
        $this->addCurrency($currency);
        $this->addPaymentAction();

        $this->data['RETURNURL'] = $success_url;
        $this->data['CANCELURL'] = $cancel_url;

        $response = $this->request($this->gateway_api, array_merge($this->setup, $this->data));

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

        $response = $this->request($this->gateway_api, array_merge($this->setup, $this->data));

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

        $response = $this->request($this->gateway_api, array_merge($this->setup, $this->data));

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

    public function request($url, $data)
    {
        $response = parent::request($url, $data);

        $response_array = array();
        parse_str($response, $response_array);
        return $response_array;
    }


}