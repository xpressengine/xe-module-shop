<?php

require_once dirname(__FILE__) . '/../../plugins_payment/PaymentMethodAbstract.php';
require_once dirname(__FILE__) . '/BaseRepository.php';

/**
 * Handles database operations for Product
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class PaymentMethodRepository extends BaseRepository
{
    public static $PAYMENT_METHODS_DIR;

    public function __construct()
    {
        self::$PAYMENT_METHODS_DIR = _XE_PATH_ . 'modules/shop/plugins_payment';
    }

    private function getPaymentMethodInstanceByName($payment_extension_name)
    {
        // Skip files (we are only interested in the folders)
        if(!is_dir(self::$PAYMENT_METHODS_DIR . DIRECTORY_SEPARATOR . $payment_extension_name))
        {
            throw new Exception("Given folder name is not a directory");
        }

        // Convert from under_scores to CamelCase in order to get class name
        $payment_class_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $payment_extension_name)));
        $payment_class_path = self::$PAYMENT_METHODS_DIR
            . DIRECTORY_SEPARATOR . $payment_extension_name
            . DIRECTORY_SEPARATOR . $payment_class_name . '.php';

        if(!file_exists($payment_class_path)) {
            throw new Exception("Payment class was not found in given folder");
        };

        // Include class and check if it extends the required abstract class
        require_once $payment_class_path;

        $payment_instance = new $payment_class_name;
        if(!($payment_instance instanceof PaymentMethodAbstract))
        {
            throw new Exception("Payment class does not extend required PaymentMethodAbstract");
        };

        return $payment_instance;
    }

    private function getPaymentMethodFromProperties($data)
    {
        $data->properties = unserialize($data->props);
        unset($data->props);

        $payment_gateway = $this->getPaymentMethodInstanceByName($data->name);
        $payment_gateway->setProperties($data);
        return $payment_gateway;
    }

    public function getPaymentMethod($name)
    {
        $args = new stdClass();
        $args->name = $name;

        $output = executeQuery('shop.getGateway',$args);
        if(!$output->toBool()) {
            throw new Exception($output->getMessage(), $output->getError());
        }

        // If payment gateway exists in the database, return it as is
        if($output->data)
        {
            return $this->getPaymentMethodFromProperties($output->data);
        }

        // Otherwise, initialize it with info from the extension class and insert in database
        $payment_gateway = $this->getPaymentMethodInstanceByName($name);

        $this->insertPaymentMethod($payment_gateway);

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
        // Scan through the plugins_shipping extension directory to retrieve available methods
        $payment_extensions = FileHandler::readDir(self::$PAYMENT_METHODS_DIR);

        $payment_methods = array();
        foreach($payment_extensions as $payment_extension_name)
        {
            try
            {
                $payment_methods[] = $this->getPaymentMethod($payment_extension_name);
            }
            catch(Exception $e)
            {
                continue;
            }
        }

        return $payment_methods;
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

        if(isset($payment_method->properties) && !is_string($payment_method->properties))
        {
            $serialized_properties = serialize($payment_method->properties);
            $payment_method->properties = $serialized_properties;
        }

        $output = executeQuery('shop.updateGateway', $payment_method);

        if(!$output->toBool()) {
            throw new Exception($output->getMessage(), $output->getError());
        }

        return TRUE;
    }

    /**
     * Inserts a new payment gateway
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @param  args
     * @throws exception
     * @return boolean
     */
    public function insertPaymentMethod($args)
    {
        $args->id = getNextSequence();
        $output = executeQuery('shop.insertGateway', $args);
        if(!$output->toBool()) {
            throw new Exception($output->getMessage(), $output->getError());
        }
        return TRUE;
    }

    /**
     * Get active payment gateways
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @throws exception
     * @return object
     */
    public function getActivePaymentMethods() {

        $args = new stdClass();
        $args->status = 1;
        $output = executeQueryArray('shop.getGateways',$args);

        if (!$output->toBool())
        {
            throw new Exception($output->getMessage(), $output->getError());
        }

        $active_payment_gateways = array();
        foreach($output->data as $data)
        {
            $active_payment_gateways[] = $this->getPaymentMethodFromProperties($data);
        }

        return $active_payment_gateways;
    }

    /**
     * Deletes a  payment gateway
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @param  $args
     * @throws exception
     * @return boolean
     */
    public function deletePaymentMethod($args) {

        $output = executeQuery('shop.deletePaymentMethod',$args);

        if (!$output->toBool()) {

            throw new Exception($output->getMessage(), $output->getError());

        }

        return $output->data;

    }

    /**
     * Deletes payment gateways from DB if they do not have a folder with a corresponding name
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @param  none
     */
    public function sanitizePaymentMethods() {
        $pgByDatabase = $this->getAllGateways();
        $pgByFolders = $this->getPaymentGatewaysByFolders();

        foreach ($pgByDatabase as $obj) {
            if (!in_array($obj->name,$pgByFolders)) {
                $this->deletePaymentMethod($obj);
            }
        }
    }
}
