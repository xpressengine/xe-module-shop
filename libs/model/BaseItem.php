<?php
abstract class BaseItem
{
    public $repo;

	public function __construct($data = NULL)
	{
		if(!is_null($data) && !is_array($data) && !($data instanceof stdClass)) {
			throw new Exception('invalid $data type');
		}
		if ($data) {
			$this->loadFromArray((array)$data);
		}
        $repoClass = get_called_class() . 'Repository';
        if (class_exists($repoClass)) {
            $this->repo = new $repoClass;
        }
	}

	protected function loadFromArray(array $data)
	{
		foreach($data as $field=> $value)
		{
			if (property_exists(get_called_class(), $field)) {
				$this->$field = $value;
			}
		}
	}

}