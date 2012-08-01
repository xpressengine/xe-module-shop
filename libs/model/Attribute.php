<?php
require_once dirname(__FILE__) . '/BaseItem.php'
;
class Attribute extends BaseItem
{
    public
        $attribute_srl,
        $member_srl,
        $title,
        $type,
        $required,
        $status,
        $values = array(),
        $default_value,
        $regdate,
        $last_update;

    public function getList()
    {

    }
}