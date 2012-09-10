<?php
class Attribute extends BaseItem
{

    public
        $attribute_srl,
        $module_srl,
        $member_srl,
        $title,
        $type,
        $required,
        $status,
        $values = array(),
        $default_value,
        $regdate,
        $last_update,
        $category_scope = array();

    public function save()
    {
        $model = getModel('shop'); /* @var shopModel $model */
        return $model->getAttributeRepository()->insertAttribute($this);
    }

    public function getType($lang)
    {
        return AttributeRepository::getTypes($lang, $this->type);
    }

}