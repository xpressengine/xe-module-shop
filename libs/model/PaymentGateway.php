<?php

require_once dirname(__FILE__) . '/BaseItem.php';

/**
 * Model class for Product Category
 *
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class PaymentGateway extends BaseItem
{

    public $id;
    public $name;
    public $status = 0;
    public $props;

}
