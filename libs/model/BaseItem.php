<?php
abstract class BaseItem
{

    public function __construct($data = null)
    {
        if (!is_null($data) && !is_array($data) && !($data instanceof stdClass)) {
            throw new Exception('invalid $data type');
        }
        if ($data) $this->loadFromArray((array) $data);
    }

    protected function loadFromArray(array $data)
    {
        foreach ($data as $field=>$value) if (isset($this->$field)) $this->$field = $value;
    }

}