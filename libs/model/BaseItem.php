<?php
abstract class BaseItem
{
    public $repo;
    protected $meta = array();

	public function __construct($data = NULL)
	{
		if(!is_null($data) && !is_array($data) && !($data instanceof stdClass)) {
			throw new Exception('invalid $data type');
		}
		if ($data) {
			$this->loadFromArray((array)$data);
		}
        /**
         * Look for Item repository.
         * For IDE purposes like code completion $this->repo's type should be hinted in each Item the way I did in Cart.
         */
        $repoClass = $this->getRepo();
        $reflection = new ReflectionClass($repoClass);
        $repoClass = (string) $reflection->getName();
        $this->repo = ( $reflection->isInstantiable() ? new $repoClass : null );

        /**
         * Look for srl field if it's not already set in class $meta
         */
        if ($srlField = $this->getMeta('srl')) {
            $reflection = new ReflectionClass($this);
            if (!$reflection->hasProperty($srlField)) {
                throw new Exception("Srl field '$srlField' doesn't exist");
            }
        }
        else {
            foreach ($this as $field=> $value) {
                if (substr($field, strlen($field) - 4, strlen($field)) === '_srl') {
                    $this->setMeta('srl', $field);
                    break;
                }
            }
        }

    }

    protected function getRepo()
    {
        return $this->getMeta('repo') ? $this->getMeta('repo') : get_called_class() . 'Repository';
    }

	protected function loadFromArray(array $data)
	{
		foreach ($data as $field=> $value)
		{
			if (property_exists(get_called_class(), $field)) {
				$this->$field = $value;
			}
		}
	}

    public function query($name, $params = null, $array = false)
    {
        if (!isset($this->repo)) {
            throw new Exception(get_called_class() . " doesn't have a repository.");
        }
        return $this->repo->query($name, $params, $array);
    }

    public function isPersisted()
    {
        $srl = $this->getMeta('srl');
        return is_numeric($this->$srl);
    }

    public function getMeta($key)
    {
        return isset($this->meta[$key]) ? $this->meta[$key] : null;
    }

    public function setMeta($key, $val)
    {
        $this->meta[$key] = $val;
    }

}