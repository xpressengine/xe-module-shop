<?php

/**
 * Handles database operations for Newsletter table
 *
 * @author Dan Dragan (dev@xpressengine.org)
 */
class NewsletterRepository extends BaseRepository
{

    public function insert(Newsletter &$newsletter)
    {
        if ($newsletter->newsletter_srl) throw new Exception('A srl must NOT be specified for the insert operation!');
        $newsletter->newsletter_srl = getNextSequence();
        return $this->query('insertNewsletter', get_object_vars($newsletter));
    }

    public function update(Newsletter $newsletter)
    {
        if (!is_numeric($newsletter->newsletter_srl)) throw new Exception('You must specify a srl for the updated newsletter');
        return $this->query('updateNewsletter', get_object_vars($newsletter));
    }

    public function getList($module_srl)
    {
        $params = array('module_srl'=> $module_srl);
        $output = $this->query('getNewsletterList', $params);
        foreach ($output->data as $i=>$data) $output->data[$i] = new Newsletter((array) $data);
        return $output;
    }

    public function getNewsletter($newsletter_srl){
        $args = new stdClass();
        $args->newsletter_srl = $newsletter_srl;
        $output = $this->query('getNewsletter',$args);
        return new Newsletter($output->data);
    }

    public function deleteNewsletters($args)
    {
        if (!isset($args->newsletter_srls))throw new Exception("Please provide newsletter_srls or module_srl.");
        //delete newsletters
        return $this->query('deleteNewsletters',$args);
    }

    public function sendEmailsToSubscribers(Newsletter $newsletter, $site_srl){
        $shopModel = getModel('shop');
        $customerRepository = $shopModel->getCustomerRepository();
        $output = $customerRepository->getNewsletterCustomers($site_srl,'Y');
        $emails_list = "";
        foreach($output->customers as $customer){
            //add unsubscribe link to $newsletter->content;
            $newsletter_content = $newsletter->content."</br></br>".sprintf(Context::getLang('unsubscribe_message'),getUrl('','act','procShopUnsignToNewsletter','member_srl',$customer->member_srl,'email_address',$customer->email_address));

            $oMail = new Mail();
            $oMail->setTitle( $newsletter->subject );
            $oMail->setContent($newsletter_content);
            $oMail->setSender($newsletter->sender_name,$newsletter->sender_email);
            $oMail->setReceiptor( false, $customer->email_address );
            $oMail->send();
        }

    }

}