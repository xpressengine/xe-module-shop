<?php

require_once dirname(__FILE__) . '/ShippingMethodInterface.php';

abstract class ShippingMethodAbstract implements ShippingMethodInterface
{
    public $shipping_info;
    protected $shipping_method_dir;
    static protected $config_file_name = 'config.xml';
    static protected $template_file_name = 'template.html';

    public function __construct()
    {
        $config = simplexml_load_file($this->getConfigFilePath());
        $this->shipping_info = $config;
    }

    protected function getConfigFilePath()
    {
        $config_path = $this->shipping_method_dir . DIRECTORY_SEPARATOR . self::$config_file_name;
        if(!file_exists($config_path))
        {
            throw new Exception("You must add a config.xml file describing your shipping class");
        }
        return $config_path;
    }

    public function getName()
    {
        return (string) $this->shipping_info->name;
    }

    public function getCode()
    {
        return (string) $this->shipping_info->code;
    }

    public function isActive()
    {
        return $this->shipping_info->is_active == 'Y' ? true : false;
    }

    private function setIsActive($is_active)
    {
        if(!isset($is_active)) return;
        $this->shipping_info->is_active = $is_active ? 'Y' : 'N';
    }

    public function getDisplayName()
    {
        return (string) $this->shipping_info->display_name;
    }

    private function setDisplayName($display_name)
    {
        if(!isset($display_name)) return;
        $this->shipping_info->display_name = $display_name;
    }

    /**
     * Saves custom properties
     *
     * @abstract
     * @param $shipping_info
     *
     * @return mixed
     */
    abstract protected function saveShippingInfo($shipping_info);

    public function save($shipping_info)
    {
        $this->setIsActive($shipping_info->is_active);
        $this->setDisplayName($shipping_info->display_name);

        $this->saveShippingInfo($shipping_info);

        $this->shipping_info->asXML($this->getConfigFilePath());
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