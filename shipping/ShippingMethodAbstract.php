<?php

require_once dirname(__FILE__) . '/ShippingMethodInterface.php';

class ShippingMethodAbstract implements ShippingMethodInterface
{
    public $shipping_info;
    protected $shipping_method_dir;

    public function __construct()
    {
        $config = simplexml_load_file($this->shipping_method_dir . '/config.xml');
        $this->shipping_info = $config;
    }

    public function getName()
    {
        return $this->shipping_info->name;
    }

    public function isActive()
    {
        return (boolean)$this->shipping_info->is_active;
    }

    public function getFormHtml()
    {
        $oTemplate = &TemplateHandler::getInstance();
        return $oTemplate->compile($this->shipping_method_dir, 'template.html');
    }

}