<?php
class Newsletter extends BaseItem
{

    public
        $newsletter_srl,
        $module_srl,
        $subject,
        $sender_name,
        $sender_email,
        $content,
        $regdate;

    /** @var InvoiceRepository */
    public $repo;

    public function save()
    {
        return $this->newsletter_srl ? $this->repo->update($this) : $this->repo->insert($this);
    }

}