<?php

abstract class ShippingMethodAbstract extends AbstractPlugin
{
    public $shipping_info;
    protected $shipping_method_dir;
    static protected $template_file_name = 'template.html';


    public function getCode()
    {
        return $this->getName();
    }

    public function getFormHtml()
    {
        if(!file_exists($this->getPluginDir() . DIRECTORY_SEPARATOR . self::$template_file_name))
        {
            return '';
        }

        $oTemplate = &TemplateHandler::getInstance();
        return $oTemplate->compile($this->getPluginDir(), self::$template_file_name);
    }

	public function hasVariants()
	{
		return false;
	}


	/**
	 * Defines the variants of a certain shipping type
	 * For instance, for UPS we can have: Expedited, Saver etc.
	 */
	public function getVariants()
	{
		return array();
	}

    /**
     * Calculates shipping rates
     *
     * @param Cart $cart SHipping cart for which to calculate shipping
     * @param Address $shipping_address Address to which products should be shipped
     */
    abstract public function calculateShipping(Cart $cart, Address $shipping_address = NULL);



}