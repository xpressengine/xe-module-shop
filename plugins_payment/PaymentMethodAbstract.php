<?php

abstract class PaymentMethodAbstract extends AbstractPlugin
{
    static protected $frontend_form = 'form_payment.html';
    static protected $backend_form = 'form_admin_settings.html';

    public function getPaymentMethodDir()
    {
        return $this->getPluginDir();
    }

    private function getFormHtml($filename)
    {
        if(!file_exists($this->getPaymentMethodDir() . DIRECTORY_SEPARATOR . $filename))
        {
            return '';
        }

        $oTemplate = &TemplateHandler::getInstance();
        return $oTemplate->compile($this->getPaymentMethodDir(), $filename);
    }

    public function getPaymentFormHTML()
    {
        return $this->getFormHtml(self::$frontend_form);
    }

    public function getAdminSettingsFormHTML()
    {
        return $this->getFormHtml(self::$backend_form);
    }

    public function getPaymentFormAction()
    {
        return './';
    }

    public function getPaymentSubmitButtonText()
    {
        return "Place your order";
    }

    public function getSelectPaymentHtml()
    {
        return $this->display_name;
    }

    protected function getCheckoutPageUrl()
    {
        $vid = Context::get('vid');
        return getNotEncodedFullUrl('', 'vid', $vid
            , 'act', 'dispShopCheckout'
            , 'error_return_url', ''
        );
    }

    protected function getPlaceOrderPageUrl()
    {
        $vid = Context::get('vid');
        return getNotEncodedFullUrl('', 'vid', $vid
            , 'act', 'dispShopPlaceOrder'
            , 'payment_method_name', $this->getName()
            , 'error_return_url', ''
        );
    }

    public function getOrderConfirmationPageUrl()
    {
        $vid = Context::get('vid');
        return getNotEncodedFullUrl('', 'vid', $vid
            , 'act', 'dispShopOrderConfirmation'
            , 'payment_method_name', $this->getName()
            , 'error_return_url', ''
        );
    }

    /**
     * Get URL for IPN notifications
     */
    public function getNotifyUrl()
    {
        $vid = Context::get('vid');
        return getNotEncodedFullUrl('', 'vid', $vid
            , 'act', 'procShopPaymentNotify'
            , 'payment_method_name', $this->getName()
            , 'error_return_url', ''
        );
    }

    protected function redirect($url)
    {
        header('location:' . $url);
        exit();
    }

    public function onCheckoutFormSubmit(Cart $cart, &$error_message)
    {
        return true;
    }

    public function onPlaceOrderFormLoad()
    {

    }

    abstract public function processPayment(Cart $cart, &$error_message);

    public function onOrderConfirmationPageLoad($cart, $module_srl)
    {
    }

    public function notify()
    {

    }

	protected function createNewOrderAndDeleteExistingCart($cart, $transaction_id)
	{
		$order = new Order($cart);
		$order->transaction_id = $transaction_id;
		$order->save(); //obtain srl
		$order->saveCartProducts($cart);
		Order::sendNewOrderEmails($order->order_srl);
		$cart->delete();

		Context::set('order_srl', $order->order_srl);
		// Override cart, otherwise it would still show up with products
		Context::set('cart', NULL);
	}

}

abstract class PaymentAPIAbstract
{
    public function request($url, $data)
    {
        $post_string = http_build_query($data);
        if(__DEBUG__)
        {
            ShopLogger::log('REQUEST ' . $url . ' ' . $post_string);
        }

        // Request
        $request = curl_init($url);
        curl_setopt($request, CURLOPT_HEADER, 0);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_POSTFIELDS, $post_string);
        $response = curl_exec($request);
        if(__DEBUG__)
        {
            ShopLogger::log('RESPONSE ' . $response);
        }

        curl_close ($request);
        return $response;
    }

}