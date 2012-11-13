<?php

/**
 * Handles logic for Shipping
 *
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class ShippingMethodRepository extends AbstractPluginRepository
{
    /**
     * Returns all available shipping methods
     */
    public function getAvailableShippingMethods($module_srl)
    {
        return $this->getAvailablePlugins($module_srl);
    }

	/**
	 * Returns all active shipping methods
	 */
	public function getActiveShippingMethods($module_srl)
	{
		return $this->getActivePlugins($module_srl);
	}

	/**
	 * Returns all available shipping methods for the current cart
	 * together with the appropriate prices
	 *
	 * Sample return
	 * return array(
	 *  'flat_rate_shipping' => 'Flat rate shipping 10$'
	 * , 'ups__01' => 'UPS Domestic bla bla 5$'
	 * , 'ups__65' => 'UPS International saver 4$'
	 * )
	 */
	public function getAvailableShippingMethodsAndTheirPrices($module_srl, Cart $cart)
	{
		$shop_info = new ShopInfo($module_srl);
		$active_shipping_methods = $this->getActiveShippingMethods($module_srl);

		$available_shipping_methods = array();
		foreach($active_shipping_methods as $shipping_method)
		{
			$available_variants = $shipping_method->getAvailableVariants($cart);
			foreach($available_variants as $variant)
			{
				$key = $variant->name;
				if($variant->variant) $key .= '__' . $variant->variant;

				$value = $variant->display_name;
				if($variant->variant) $value .= ' - ' . $variant->variant_display_name;
				$value .= ' - ' . ShopDisplay::priceFormat($variant->price, $shop_info->getCurrencySymbol());

				$available_shipping_methods[$key] = $value;
			}
		}
		return $available_shipping_methods;
	}


    /**
     * Get a certain shipping method instance
     *
     * @param string $code Folder name of the shipping method
     *
     * @return ShippingMethodAbstract
     */
    public function getShippingMethod($name, $module_srl)
    {
        return $this->getPlugin($name, $module_srl);
    }

    public function updateShippingMethod($shipping_info)
    {
        if(isset($shipping_info->is_active))
        {
            $shipping_info->status = $shipping_info->is_active == 'Y' ? 1 : 0;
            unset($shipping_info->is_active);
        }
        $this->updatePlugin($shipping_info);
    }


    function getPluginsDirectoryPath()
    {
        return _XE_PATH_ . 'modules/shop/plugins_shipping';
    }

    function getClassNameThatPluginsMustExtend()
    {
        return "ShippingMethodAbstract";
    }

    protected function getPluginInfoFromDatabase($name, $module_srl)
    {
        $output = $this->query('shop.getShippingMethod', array('name' => $name, 'module_srl' => $module_srl));
        return $output->data;
    }

    protected function fixPlugin($name, $old_module_srl, $new_module_srl)
    {
        $this->query('shop.fixShippingMethod', array('name' => $name, 'module_srl' => $new_module_srl, 'source_module_srl' => $old_module_srl));
    }

    protected function updatePluginInfo($plugin)
    {
        $this->query('shop.updateShippingMethod', $plugin);
    }

    protected function insertPluginInfo(AbstractPlugin $plugin)
    {
        $plugin->id = getNextSequence();
        $this->query('shop.insertShippingMethod', $plugin);
    }

    protected function deletePluginInfo($name, $module_srl)
    {
        $this->query('shop.deleteShippingMethod', array('name' => $name, 'module_srl' => $module_srl));
    }

    protected function getAllPluginsInDatabase($module_srl, $args)
    {
		if(!$args) $args = new stdClass();
		$args->module_srl = $module_srl;

        $output = $this->query('shop.getShippingMethods', $args, TRUE);
		return $output->data;
    }

    protected function getAllActivePluginsInDatabase($module_srl)
    {
        $output = $this->query('shop.getShippingMethods', array('status' => 1, 'module_srl' => $module_srl), TRUE);
		return $output->data;
    }

	protected function updatePluginsAllButThis($is_default, $name, $module_srl)
	{
		$args = new stdClass();
		$args->except_name = $name;
		$args->module_srl = $module_srl;
		$args->is_default = 0;
		$this->query('shop.updateShippingMethods', $args);
	}
}