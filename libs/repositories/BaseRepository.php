<?php
abstract class BaseRepository
{
    public $entity;

    public function __construct()
    {
        $this->entity = $this->getEntityName();
    }

    protected function getEntityName()
    {
        return str_replace('Repository', '', get_class($this));
    }

    public function query($name, $params = null, $asArray=false)
    {
        if (!$params) $params = array();
        if ($params instanceof BaseItem) $params = get_object_vars($params);
        if (!is_array($params) && !($params instanceof stdClass)) throw new Exception('Wrong $params type');
        if (!strpos($name, '.')) $name = "shop.$name";
        $name = str_replace('%E', $this->entity, $name);
        if ($params) $params = (object) $params;
        $function = 'executeQuery' . ($asArray ? 'Array' : '');
        $output = $function($name, $params);
        if (is_string($asArray) && class_exists($asArray) && !empty($output->data)) {
            self::rowsToEntities($output->data, $asArray);
        }
        if ($output->getMessage() == 'Specified query ID value is invalid.') {
            $output->setMessage("Query $name not found");
        }
        return self::check($output);
    }

    public static function check($output)
    {
        if (!is_object($output)) throw new Exception('A valid query output is expected here');
        if (!$output->toBool()) {
            throw new Exception($output->getMessage(), $output->getError());
        }
        return $output;
    }

    /**
     * Mass CRUD operations
     * mass update is not (yet?) supported in XE
     */

    /**
     * @param mixed  $srls A single numeric srl or an array of srls
     * @param string $query Query to use
     * @param null   $entity Return entity
     *
     * @return mixed If $srls was a single numeric, the result should be an object of type $this->entity or $fetchAs. Else we'll return an array of objects corresponding to the $srls array
     * @throws Exception
     */
    public function get($srls, $query="get%E", $entity=null, array $extraParams=array())
    {
        $single = false;
        if (is_numeric($srls)) {
            $single = true;
            $srls = array($srls);
        } elseif (!is_array($srls)) throw new Exception('Invalid $srls input');
        if (!$entity) $entity = $this->entity;
        if (!class_exists($entity)) throw new Exception("Class $entity doesn't exist");
        $output = $this->query($query, array_merge(array('srls'=>$srls), $extraParams), true);
        self::rowsToEntities($output->data, $entity);
        return $single && count($output->data) == 1 ? $output->data[0] : $output->data;
    }

    protected static function rowsToEntities(array &$data, $getAs)
    {
        foreach ($data as $i=>$row) {
            $data[$i] = new $getAs($row);
        }
    }

    /**
     * @param mixed  $srls  A single numeric srl or an array of srls
     * @param string $query Query to use
     *
     * @return object
     * @throws Exception
     */
    public function delete($srls, $query="delete%Es")
    {
        if (is_numeric($srls)) {
            $srls = array($srls);
        } elseif (!is_array($srls)) throw new Exception('Invalid $srls input');
        return $this->query($query, array('srls'=>$srls));
    }

    public function count($query="count%Es", array $extraParams=array())
    {
        return $this->query($query, $extraParams)->data->count;
    }

    public function getList($query='list%Es', $page=null, array $params=array(), $entity = null)
    {
        $params['page'] = ($page ? $page : 1);
        $entity = ($entity ? $entity : $this->entity);
        if (!class_exists($entity)) throw new Exception("Class $entity doesn't exist");
        $output = $this->query($query, $params, $entity);
        return $output;
    }

}