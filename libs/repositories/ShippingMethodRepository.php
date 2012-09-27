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

    protected function getAllPluginsInDatabase($module_srl)
    {
        $this->query('shop.getPaymentMethods', array('module_srl' => $module_srl), true);
    }

    protected function getAllActivePluginsInDatabase($module_srl)
    {
        $this->query('shop.getPaymentMethods', array('status' => 1, 'module_srl' => $module_srl), true);
    }
}