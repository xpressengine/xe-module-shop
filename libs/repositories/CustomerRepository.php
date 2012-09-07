<?php

require_once dirname(__FILE__) . '/../model/Customer.php';
require_once "BaseRepository.php";

/**
 * Handles database operations for Customer
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class CustomerRepository extends BaseRepository
{
      public function getCustomers($site_srl){
          $oMemberModel = getAdminModel('member');
          $members = $oMemberModel->getSiteMemberList($site_srl);
          return $members;
      }
}