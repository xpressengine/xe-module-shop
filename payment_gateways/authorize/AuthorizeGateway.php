<?php

require_once dirname(__FILE__) . '/../PaymentGatewayAbstract.php';

class AuthorizeGateway extends PaymentGatewayAbstract
{
    public function getDisplayName()
    {
        return 'Authorize.net AIM';
    }
}

?>