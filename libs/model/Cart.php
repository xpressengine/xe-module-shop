<?php
require_once dirname(__FILE__) . '/BaseItem.php';

class Cart extends BaseItem
{

    public
        $cart_srl,
        $module_srl,
        $member_srl,
        $guest_srl,
        $session_id,
        $items = 0,
        $regdate,
        $last_update;

    public function save()
    {
        /* @var shopModel $model */
        $model = getModel('shop');
        /* @var CartRepository $repo */
        $repo = $model->getCartRepository();
        return $repo->insertCart($this);
    }

}