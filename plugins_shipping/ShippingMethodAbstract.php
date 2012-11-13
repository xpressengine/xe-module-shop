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
		return FALSE;
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
     * @param Cart $cart Shipping cart for which to calculate shipping; includes shipping address
	 * @param String $service Represents the specific service for which to calcualte shipping (e.g. Standard or Priority)
     */
    abstract public function calculateShipping(Cart $cart, $service = NULL);

	/**
	 * Returns a list of available variants
	 * The structure is:
	 * array(
	 * 	stdclass(
	 * 		'name' => 'ups'
	 * 		, 'display_name' => 'UPS'
	 * 		, 'variant' => '01'
	 * 		, 'variant_display_name' => 'Domestic'
	 * 		,  price => 12
	 * ))
	 *
	 * @param Cart $cart
	 * @return array
	 */
	abstract public function getAvailableVariants(Cart $cart);


}