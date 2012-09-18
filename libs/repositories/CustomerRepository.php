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

      $oMemberAdminModel = getAdminModel('member');
      $shopModel = getModel('shop');
      $addressRepository = $shopModel->getAddressRepository();
      $page = Context::get('page');
      $output = $oMemberAdminModel->getSiteMemberList($site_srl,$page);
      foreach($output->data as $member){
          $customer = new Customer($member);
          $customer->addresses = $addressRepository->getAddresses($customer->member_srl);
          $customer->telephone = $customer->addresses->default_billing->telephone;
          $customer->postal_code = $customer->addresses->default_billing->postal_code;
          $customer->country = $customer->addresses->default_billing->country;
          $customer->region = $customer->addresses->default_billing->region;
          $extra_vars = unserialize($member->extra_vars);
          $customer->newsletter = $extra_vars->newsletter;
          $customers[] = $customer;
      }
      $output->customers = $customers;
      return $output;
    }

    public function getNewsletterCustomers($site_srl){
        $args = new stdClass();
        $args->site_srl = $site_srl;
        $output = $this->query('getAllSiteMemberList',$args,true);
        foreach($output->data as $member){
            $customer = new Customer($member);
            $extra_vars = unserialize($member->extra_vars);
            $customer->newsletter = $extra_vars->newsletter;
            if($customer->newsletter == 'Y') $customers[] = $customer;
        }
        $output->customers = $customers;
        return $output;
    }

    public function getMemberExtraVars($member_srl){
        $args = new stdClass();
        $args->member_srl = $member_srl;
        $output = $this->query('getMemberExtraVars',$args);
        return $output->data->extra_vars;
    }

    public function updateMemberExtraVars($member_srl,$extra_vars){
        $args = new stdClass();
        $args->member_srl = $member_srl;
        $args->extra_vars = $extra_vars;
        $output = $this->query('updateMemberExtraVars',$args);
        return $output;
    }
}