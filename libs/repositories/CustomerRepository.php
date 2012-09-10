<?php

/**
 * Handles database operations for Customer
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class CustomerRepository extends BaseRepository
{
      public function getCustomersList($site_srl){
          if(!isset($site_srl))
              throw new Exception("Missing arguments for get customers list : please provide [site_srl]");

          $oMemberModel = getAdminModel('member');
          $shopModel = getModel('shop');
          $addressRepository = $shopModel->getAddressRepository();
          $output = $oMemberModel->getSiteMemberList($site_srl);
          foreach($output->data as $member){
              $customer = new Customer($member);
              $customer->addresses = $addressRepository->getAddresses($customer->member_srl);
              $customer->telephone = $customer->addresses->default_billing->telephone;
              $customer->postal_code = $customer->addresses->default_billing->postal_code;
              $customer->country = $customer->addresses->default_billing->country;
              $customer->region = $customer->addresses->default_billing->region;
              $customers[] = $customer;
          }
          $output->customers = $customers;
          return $output;
      }
}