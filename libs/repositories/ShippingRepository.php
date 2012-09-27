<?php

/**
 * Handles logic for Shipping
 *
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class ShippingRepository extends AbstractPluginRepository
{
    /**
     * Returns all available shipping methods
     */
    public function getAvailableShippingMethods()
    {
        return $this->getAvailablePlugins();
    }

    /**
     * Get a certain shipping method instance
     *
     * @param string $code Folder name of the shipping method
     *
     * @return ShippingMethodAbstract
     */
    public function getShippingMethod($name)
    {
        return $this->getPlugin($name);
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

    protected function getPluginInfoFromDatabase($name)
    {
        $output = $this->query('shop.getShippingMethod', array('name' => $name));
        return $output->data;
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

    protected function deletePluginInfo($name)
    {
        $this->query('shop.deleteShippingMethod', array('name' => $name));
    }

    protected function getAllPluginsInDatabase()
    {
        $this->query('shop.getPaymentMethods', null, true);
    }

    protected function getAllActivePluginsInDatabase()
    {
        $this->query('shop.getPaymentMethods', array('status' => 1), true);
    }
}