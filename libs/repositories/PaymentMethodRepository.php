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

    protected function getPluginInfoFromDatabase($name)
    {
        $args = new stdClass();
        $args->name = $name;

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

    protected function deletePluginInfo($name)
    {
        $args = new stdClass();
        $args->name = $name;
        $output = executeQuery('shop.deletePaymentMethod',$args);
        if (!$output->toBool()) {
            throw new Exception($output->getMessage(), $output->getError());
        }

        return $output->data;
    }

    protected function getAllPluginsInDatabase()
    {
        $output = executeQueryArray('shop.getPaymentMethods');

        if (!$output->toBool())
        {
            throw new Exception($output->getMessage(), $output->getError());
        }

        return $output->data;
    }

    protected function getAllActivePluginsInDatabase()
    {
        $args = new stdClass();
        $args->status = 1;
        $output = executeQueryArray('shop.getPaymentMethods', $args);

        if (!$output->toBool())
        {
            throw new Exception($output->getMessage(), $output->getError());
        }

        return $output->data;
    }

    public function getPaymentMethod($name)
    {
        return $this->getPlugin($name);
    }

    public function installPaymentMethod($name)
    {
        return $this->getPaymentMethod($name);
    }

    /**
     * Returns all available payment methods
     *
     * Looks in the database and also in the plugins_payment folder to see
     * if any new extension showed up. If yes, also adds it in the database
     */
    public function getAvailablePaymentMethods()
    {
        return $this->getAvailablePlugins();
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
    public function getActivePaymentMethods() {
        return $this->getActivePlugins();
    }

    /**
     * Deletes a  payment method
     */
    public function deletePaymentMethod($args) {
        $this->deletePlugin($args->name);
    }

    public function sanitizePaymentMethods() {
        $this->sanitizePlugins();
    }

}
