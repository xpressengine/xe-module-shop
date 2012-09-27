<?php

/**
 * Handles database operations for Product
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class PaymentMethodRepository extends AbstractPluginRepository
{
    public function getPluginsDirectoryPath()
    {
        return _XE_PATH_ . 'modules/shop/plugins_payment';
    }

    public function getClassNameThatPluginsMustExtend()
    {
        return "PaymentMethodAbstract";
    }

    protected function getPluginInfoFromDatabase($name, $module_srl)
    {
        $args = new stdClass();
        $args->name = $name;
        $args->module_srl = $module_srl;

        $output = executeQuery('shop.getPaymentMethod',$args);
        if(!$output->toBool()) {
            throw new Exception($output->getMessage(), $output->getError());
        }
        return $output->data;
    }

    protected function updatePluginInfo($payment_method)
    {
        $output = executeQuery('shop.updatePaymentMethod', $payment_method);

        if(!$output->toBool()) {
            throw new Exception($output->getMessage(), $output->getError());
        }
    }

    protected function insertPluginInfo(AbstractPlugin $payment_method)
    {
        $payment_method->id = getNextSequence();
        $output = executeQuery('shop.insertPaymentMethod', $payment_method);
        if(!$output->toBool()) {
            throw new Exception($output->getMessage(), $output->getError());
        }
    }

    protected function deletePluginInfo($name, $module_srl)
    {
        $args = new stdClass();
        $args->name = $name;
        $args->module_srl = $module_srl;
        $output = executeQuery('shop.deletePaymentMethod',$args);
        if (!$output->toBool()) {
            throw new Exception($output->getMessage(), $output->getError());
        }

        return $output->data;
    }

    protected function getAllPluginsInDatabase($module_srl)
    {
        $args = new stdClass();
        $args->module_srl = $module_srl;
        $output = executeQueryArray('shop.getPaymentMethods', $args);

        if (!$output->toBool())
        {
            throw new Exception($output->getMessage(), $output->getError());
        }

        return $output->data;
    }

    protected function getAllActivePluginsInDatabase($module_srl)
    {
        $args = new stdClass();
        $args->status = 1;
        $args->module_srl = $module_srl;
        $output = executeQueryArray('shop.getPaymentMethods', $args);

        if (!$output->toBool())
        {
            throw new Exception($output->getMessage(), $output->getError());
        }

        return $output->data;
    }

    public function getPaymentMethod($name, $module_srl)
    {
        return $this->getPlugin($name, $module_srl);
    }

    public function installPaymentMethod($name, $module_srl)
    {
        return $this->getPaymentMethod($name, $module_srl);
    }

    /**
     * Returns all available payment methods
     *
     * Looks in the database and also in the plugins_payment folder to see
     * if any new extension showed up. If yes, also adds it in the database
     */
    public function getAvailablePaymentMethods($module_srl)
    {
        return $this->getAvailablePlugins($module_srl);
    }

     /**
      *
      * Updates a payment method
      *
      * Status: active = 1; inactive = 0
      *
      * @author Daniel Ionescu (dev@xpressengine.org)
      * @param  $payment_method
      * @throws exception
      * @return boolean
     */
    public function updatePaymentMethod($payment_method) {
       $this->updatePlugin($payment_method);
    }

    /**
     * Inserts a new payment method
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @param  args
     * @throws exception
     * @return boolean
     */
    public function insertPaymentMethod($args)
    {
        $this->insertPlugin($args);
    }

    /**
     * Get active payment methods
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @throws exception
     * @return object
     */
    public function getActivePaymentMethods($module_srl) {
        return $this->getActivePlugins($module_srl);
    }

    /**
     * Deletes a  payment method
     */
    public function deletePaymentMethod($name, $module_srl) {
        $this->deletePlugin($name, $module_srl);
    }

    public function sanitizePaymentMethods($module_srl) {
        $this->sanitizePlugins($module_srl);
    }

}
