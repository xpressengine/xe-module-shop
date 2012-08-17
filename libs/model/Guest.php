<?php
require_once dirname(__FILE__) . '/BaseItem.php';

class Guest extends BaseItem
{

    public
        $guest_srl,
        $address_srl,
        $ip,
        $session_id,
        $regdate,
        $last_update;

}