<?php

require_once dirname(__FILE__) . '/BaseItem.php';

/**
 * Model class for Payment Gateway
 *
 * @author Daniel Ionescu (dev@xpressengine.org)
**/
class PaymentGateway extends BaseItem
{

    public $id;
    public $name;
    public $status = 0;
    public $props;
    public $folderPath;

    public function __construct($data = null) {

        parent::__construct($data);
        $this->folderPath = _XE_PATH_ . 'modules/shop/payment_gateways/' . $this->name . '/';

    }

    /**
     * Loads admin template
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @params path
     * @returns boolean
    **/
    public function loadAdminTemplate() {

        $fullPath = $this->folderPath.'settings.php';

        if (file_exists($fullPath)) {

            include_once($fullPath);
            return true;

        } else {

            return false;

        }

    }

}
