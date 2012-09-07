<?php

require_once dirname(__FILE__) . '/../PaymentMethodAbstract.php';

class Authorize extends PaymentMethodAbstract
{
    public function getDisplayName()
    {
        return 'Authorize.net AIM';
    }
}

?>