<?php
/**
 * Description
 *
 * @author Daniel Ionescu
 * @date 8/9/12
 * @param
 */
class PaymentGatewayManager {

    public $activeGateways;
    public $allGateways;

    public function __construct() {

        $this->activeGateways = $this->getActiveGateways();

    }

    /**
     * Includes active payment gateways
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @throws exception
     * @return boolean
     **/
    public function loadAdminTemplates() {

        if ($this->activeGateways) {

            foreach ($this->activeGateways as $value) {

                $value->loadAdminTemplate();

            }

        }

    }

    /**
     * Includes active payment gateways
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @throws exception
     * @return object
    **/
    public function getActiveGateways() {

        $paymentGateways = new stdClass();

        $baseDir = _XE_PATH_ . 'modules/shop/payment_gateways/';
        $shopModel = getModel('shop');
        $repository = $shopModel->getPaymentGatewayRepository();
        $activeGateways = $repository->getActivePaymentGateways();

        if ($activeGateways) {

            foreach ($activeGateways as $pg) {

                // load gateway class
                $classPath = $baseDir . $pg->name . '/' . $pg->name . '.php';

                if (file_exists($classPath)) {

                    require_once($classPath);

                    $className = $pg->name.'Gateway';
                    $paymentGateways->{$pg->name} = new $className($pg);

                }

            }

        }

        return $paymentGateways;

    }

    /**
     * Get all gateways from the DB and instantiates them
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @return object
     */
    public function getAllGateways() {

        /**
         *  @var shopModel $shopModel
         */
        $shopModel = getModel('shop');
        $paymentGateways = new stdClass();
        $baseDir = _XE_PATH_ . 'modules/shop/payment_gateways/';
        $repository = $shopModel->getPaymentMethodRepository();
        $gatewaysData = $repository->getAllGateways();

        if ($gatewaysData) {

            foreach ($gatewaysData as $pg) {

                // load gateway class
                $classPath = $baseDir . $pg->name . '/' . $pg->name . '.php';

                if (file_exists($classPath)) {

                    require_once($classPath);

                    $className = $pg->name.'Gateway';
                    $paymentGateways->{$pg->name} = new $className($pg);

                } else {

                    // TO DO
                    // Throw error when payment class does not exists

                }

            }

        }

        return $paymentGateways;

    }

}
