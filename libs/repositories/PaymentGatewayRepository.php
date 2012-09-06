<?php

require_once dirname(__FILE__) . '/../model/PaymentGateway.php';
require_once dirname(__FILE__) . '/BaseRepository.php';

/**
 * Handles database operations for Product
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class PaymentGatewayRepository extends BaseRepository
{
    public static $PAYMENT_METHODS_DIR;

    public function __construct()
    {
        self::$PAYMENT_METHODS_DIR = _XE_PATH_ . 'modules/shop/payment_gateways';
    }

    private function getPaymentMethodInstanceByFolderName($payment_extension_name)
    {
        // Skip files (we are only interested in the folders)
        if(!is_dir(self::$PAYMENT_METHODS_DIR . DIRECTORY_SEPARATOR . $payment_extension_name))
        {
            throw new Exception("Given folder name is not a directory");
        }

        // Convert from under_scores to CamelCase in order to get class name
        $payment_class_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $payment_extension_name))) . 'Gateway';
        $payment_class_path = self::$PAYMENT_METHODS_DIR
            . DIRECTORY_SEPARATOR . $payment_extension_name
            . DIRECTORY_SEPARATOR . $payment_class_name . '.php';

        if(!file_exists($payment_class_path)) {
            throw new Exception("Payment class was not found in given folder");
        };

        // Include class and check if it extends the required abstract class
        require_once $payment_class_path;

        $payment_instance = new $payment_class_name;
        if(!($payment_instance instanceof PaymentGatewayAbstract))
        {
            throw new Exception("Payment class does not extend required PaymentGatewayAbstract");
        };

        return $payment_instance;
    }

    public function getPaymentGateway($name)
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
            $payment_gateway = PaymentGateway::getInstanceFromDatabaseInfo($output->data);
            return $payment_gateway;
        }

        // Otherwise, initialize it with info from the extension class and insert in database
        $payment_instance = $this->getPaymentMethodInstanceByFolderName($name);
        $payment_gateway = PaymentGateway::getInstanceFromPaymentExtensionClass($payment_instance);

        $this->insertPaymentGateway($payment_gateway);

        return $this->getPaymentGateway($name);
    }

    /**
     * Returns all available payment methods
     *
     * Looks in the database and also in the payment_gateways folder to see
     * if any new extension showed up. If yes, also adds it in the database
     */
    public function getAvailablePaymentMethods()
    {
        // Scan through the shipping extension directory to retrieve available methods
        $payment_extensions = FileHandler::readDir(self::$PAYMENT_METHODS_DIR);

        $payment_methods = array();
        foreach($payment_extensions as $payment_extension_name)
        {
            try
            {
                $payment_methods[] = $this->getPaymentGateway($payment_extension_name);
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
      * Updates a payment gateway
      *
      * Status: active = 1; inactive = 0
      *
      * @author Daniel Ionescu (dev@xpressengine.org)
      * @param  $pg
      * @throws exception
      * @return boolean
     */
    public function updatePaymentGateway(PaymentGateway $pg) {
        $output = executeQuery('shop.updateGateway', $pg);

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
    public function insertPaymentGateway($args)
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
    public function getActivePaymentGateways() {

        $args = new stdClass();
        $args->status = 1;
        $output = executeQuery('shop.getActiveGateways',$args);

        if (!$output->toBool())
        {
            throw new Exception($output->getMessage(), $output->getError());
        }

        return $output->data;
    }

    /**
     * Deletes a  payment gateway
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @param  $args
     * @throws exception
     * @return boolean
     */
    public function deleteGateway($args) {

        $output = executeQuery('shop.deleteGateway',$args);

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
    public function sanitizeGateways() {
        $pgByDatabase = $this->getAllGateways();
        $pgByFolders = $this->getPaymentGatewaysByFolders();

        foreach ($pgByDatabase as $obj) {
            if (!in_array($obj->name,$pgByFolders)) {
                $this->deleteGateway($obj);
            }
        }
    }
}
