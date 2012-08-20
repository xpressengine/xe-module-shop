<?php

require_once dirname(__FILE__) . '/../model/Guest.php';
require_once "BaseRepository.php";

/**
 * Handles database operations for Guests table
 *
 * @author Florin Ercus (dev@xpressengine.org)
 */
class GuestRepository extends BaseRepository
{

    public function createOrRetrieve()
    {
        return new Guest(array(
            'guest_srl' => getNextSequence(),
            'address_srl' => null,
            'ip' => '43.46.62.212',
            'session_id' => session_id(),
            'regdate' => time(),
            'last_update' => time()
        ));
    }

}