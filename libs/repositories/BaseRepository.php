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

    public function query($name, $params = null, $array=false)
    {
        if ($params instanceof BaseItem) $params = get_object_vars($params);
        if (!is_array($params) && !($params instanceof stdClass)) throw new Exception('Wrong $params type');
        if (!strpos($name, '.')) $name = "shop.$name";
        $name = str_replace('%E', $this->entity, $name);
        if ($params) $params = (object) $params;
        $function = 'executeQuery' . ($array ? 'Array' : '');
        $output = $function($name, $params);
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
     * @param null   $getAs Return entity
     *
     * @return mixed If $srls was a single numeric, the result should be an object of type $this->entity or $fetchAs. Else we'll return an array of objects corresponding to the $srls array
     * @throws Exception
     */
    public function get($srls, $query="get%E", $getAs=null)
    {
        $single = false;
        if (is_numeric($srls)) {
            $single = true;
            $srls = array($srls);
        } elseif (!is_array($srls)) throw new Exception('Invalid $srls input');
        if (!$getAs) $getAs = $this->entity;
        $output = $this->query($query, array('srls' => $srls), true);
        foreach ($output->data as $i=>$arr) {
            $output->data[$i] = new $getAs($arr);
        }
        return $single && count($output->data) == 1 ? $output->data[0] : $output->data;
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

}