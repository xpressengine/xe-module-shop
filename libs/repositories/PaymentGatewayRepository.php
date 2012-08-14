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

    /**
     * Returns a specific gateway by name
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @param $args
     * @throws exception
     * @return object
     */
    function getGateway($args) {

        $output = executeQuery('shop.getGateway',$args);

        if(!$output->toBool()) {

            throw new Exception($output->getMessage(), $output->getError());

        }

        return $output->data;

    }

    /**
     * Returns all gateways
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @throws exception
     * @return object
     */
    function getAllGateways() {

        $output = executeQueryArray('shop.getAllGateways');

        if(!$output->toBool()) {

            throw new Exception($output->getMessage(), $output->getError());

        }

        return $output->data;
    }

    /**
     * updates the status of a payment gateway (active=1/inactve=0)
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @param  $pg
     * @throws exception
     * @return boolean
     */
    public function updatePaymentGatewayStatus(PaymentGateway $pg) {

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
    public function insertPaymentGateway($args) {

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
        $args->status = 0;
        $output = executeQuery('shop.getActiveGateways',$args);

        if (!$output->toBool()) {

            throw new Exception($output->getMessage(), $output->getError());

        }

        return $output->data;

    }

    /**
     * Inserts a new payment gateway
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

    /**
     * Gets payment gateway by folders
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @param none
     * @throws exception
     * @return array
     */
    public function getPaymentGatewaysByFolders() {

        $paymentGateways = array();
        $baseDir = _XE_PATH_ . 'modules/shop/payment_gateways/';
        $dirHandle = opendir($baseDir);

        while( $file = readdir($dirHandle) ) {
            if(is_dir($baseDir.$file) && $file != '.' && $file != '..') {
                $paymentGateways[] = strtolower($file);
            }
        }

        return $paymentGateways;

    }

}
