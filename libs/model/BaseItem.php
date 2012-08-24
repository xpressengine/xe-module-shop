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
        /**
         * Look for Item repository.
         * For IDE purposes like code completion $this->repo's type should be hinted in each Item the way I did in Cart.
         */
        $this->repo = $this->getRepo();
	}

    /**
     * Override this for custom repositories
     */
    public function getRepo()
    {
        $reflection = new ReflectionClass($repoClass = get_called_class() . 'Repository');
        return $reflection->isInstantiable() ? new $repoClass : null;
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
        if (!isset($this->repo)) throw new Exception(get_called_class() . " doesn't have a repository.");
        return $this->repo->query($name, $params, $array);
    }

}