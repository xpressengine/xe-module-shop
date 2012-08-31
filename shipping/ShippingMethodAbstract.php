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
        return $this->shipping_method_dir . DIRECTORY_SEPARATOR . self::$config_file_name;
    }

    public function getName()
    {
        return $this->shipping_info->name;
    }

    public function getCode()
    {
        return $this->shipping_info->code;
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
        return $this->shipping_info->display_name;
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
        $oTemplate = &TemplateHandler::getInstance();
        return $oTemplate->compile($this->shipping_method_dir, self::$template_file_name);
    }

}