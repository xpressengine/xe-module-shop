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
        $is_filter,
        $regdate,
        $last_update,
        $category_scope = array();

    /** @var AttributeRepository */
    public $repo;

    public function save()
    {
        $model = getModel('shop'); /* @var shopModel $model */
        return $model->getAttributeRepository()->insertAttribute($this);
    }

    public function getType($lang)
    {
        $repo = new AttributeRepository();
        return $repo->getTypes($lang, $this->type);
    }

    public function getValues($delimiter='|', $trim=true)
    {
        $values = explode($delimiter, $this->values);
        if ($trim) foreach ($values as $i=>$val) $values[$i] = trim($val);
        return $values;
    }

    public function isNumeric()
    {
        $repo = $this->repo; return $this->type == $repo::TYPE_NUMERIC;
    }

    public function isSelect()
    {
        $repo = $this->repo; return $this->type == $repo::TYPE_SELECT;
    }

    public function isMultipleSelect()
    {
        $repo = $this->repo; return $this->type == $repo::TYPE_SELECT_MULTIPLE;
    }

    public function getMinValue()
    {
        return $this->repo->getAttributeMinValue($this->module_srl, $this->attribute_srl);
    }

    public function getMaxValue()
    {
        return $this->repo->getAttributeMaxValue($this->module_srl, $this->attribute_srl);
    }

}