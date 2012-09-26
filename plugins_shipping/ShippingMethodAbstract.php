<?php

abstract class ShippingMethodAbstract extends AbstractPlugin implements ShippingMethodInterface
{
    public $shipping_info;
    protected $shipping_method_dir;
    static protected $config_file_name = 'config.xml';
    static protected $template_file_name = 'template.html';

    public function getCode()
    {
        return $this->getName();
    }

    public function getFormHtml()
    {
        if(!file_exists($this->shipping_method_dir . DIRECTORY_SEPARATOR . self::$template_file_name))
        {
            return '';
        }

        $oTemplate = &TemplateHandler::getInstance();
        return $oTemplate->compile($this->shipping_method_dir, self::$template_file_name);
    }

    /**
     * Calculates shipping rates
     *
     * // TODO Enforce parameter type Address when class is ready
     *
     * @param Cart $cart SHipping cart for which to calculate shipping
     * @param Address $shipping_address Address to which products should be shipped
     */
    abstract public function calculateShipping(Cart $cart, $shipping_address);

}