<?php

require_once dirname(__FILE__) . '/../model/Attribute.php';
require_once "BaseRepository.php";

/**
 * Handles database operations for Attribute
 *
 * @author Florin Ercus (dev@xpressengine.org)
 */
class AttributeRepository extends BaseRepository
{
    const
        TYPE_TEXTFIELD = 1,
        TYPE_TEXTAREA = 2,
        TYPE_DATE = 3,
        TYPE_BOOLEAN = 4,
        TYPE_SELECT = 5,
        TYPE_SELECT_MULTIPLE = 6;

	/**
	 * Insert a new attribute; returns the ID of the newly created record
	 *
	 * @author Florin Ercus (dev@xpressengine.org)
	 * @param $attribute Attribute
	 * @return int
	 */
	public function insertAttribute(Attribute &$attribute)
	{
        if ($attribute->attribute_srl) throw new Exception('A srl must NOT be specified');
        $attribute->attribute_srl = getNextSequence();
        if(count($attribute->values ) == 0) unset($attribute->values);
		$output = executeQuery('shop.insertAttribute', $attribute);
		if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
        else $this->insertAttributeScope($attribute);
        return $output;
	}

    /**
     * Insert attribute scope
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $attribute Attribute
     * @return boolean
     */
    public function  insertAttributeScope(Attribute &$attribute)
    {
        $args = new stdClass();
        $args->attribute_srl = $attribute->attribute_srl;
        foreach($attribute->category_scope as $category){
            $args->category_srl = $category;
            $output = executeQuery('shop.insertAttributeScope',$args);
            if(!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
        }
        return TRUE;
    }


    /**
     * Update an attribute
     * @author Florin Ercus (dev@xpressengine.org)
     * @param $attribute Attribute
     * @throws Exception
     * @return mixed
     */
    public function updateAttribute(Attribute $attribute)
    {
        if (!$attribute->attribute_srl) throw new Exception('Target srl must be specified');
        if (count($attribute->values ) == 0) unset($attribute->values);
        $output = executeQuery('shop.updateAttribute', $attribute);
        if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
        else $this->updateAttributeScope($attribute);
        return $output;
    }

    /**
     * Update attribute scope
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $attribute Attribute
     * @return boolean
     */
    public function updateAttributeScope(Attribute &$attribute)
    {
        $this->deleteAttributesScope($attribute);
        $this->insertAttributeScope($attribute);
        return TRUE;
    }


    /**
	 * Deletes one or more attributes by $attribute_srl or $module_srl
	 *
	 * @author Florin Ercus (dev@xpressengine.org)
	 * @param $args array
	 */
	public function deleteAttributes($args)
	{
        if (!isset($args->attribute_srls)) {
            if (!isset($args->module_srl)) throw new Exception("Please provide attribute_srls or module_srl.");
            if (!is_array($args->attribute_srls)) throw new Exception("This query expects an array of attribute srls");
        }
        //delete attributes
		$output = executeQuery('shop.deleteAttributes', $args);
		if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
        //delete attributes scope
        $output = executeQuery('shop.deleteAttributesScope',$args);
        if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
		//delete product attributes
		$output = executeQuery('shop.deleteProductAttributes',$args);
		if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
        return $output;
	}

    /**
     * Delete attribute scope
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $attribute Attribute
     * @return boolean
     */
    public function deleteAttributesScope(Attribute &$attribute)
    {
        $args = new stdClass();
        $args->attribute_srls[] = $attribute->attribute_srl;
        $output = executeQuery('shop.deleteAttributesScope',$args);
        if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
        return TRUE;
    }

	/**
	 * Retrieve an attribute object from the database given a list of attribute srls.
	 *
	 * @author Florin Ercus (dev@xpressengine.org)
	 * @param $srls array
	 * @return mixed|array of attribute objects or only one attribute object if count($srl) is 1
	 */
	public function getAttributes(array $srls)
	{
		$args = new stdClass();
		$args->attribute_srls = $srls;
		$output = executeQuery('shop.getAttributesBySrls', $args);
		if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
		$rez = array();
        if (count($srls) == 1) {
            $attribute = new Attribute($output->data);
            $this->getAttributeScope($attribute);
            return $attribute;
        }
        foreach ($output->data as $data) {
            $rez[] = new Attribute($data);
            $this->getAttributeScope($rez[key($rez)]);
        }
		if( empty($rez)) return false;
		else {
			foreach($rez as $att){
				$att->values  = explode('|',$att->values);
			}
			return $rez;
		}
	}

	/**
	 * Retrieve all value combinations of configurable attributes
	 *
	 * @author Dan Dragan(dev@xpressengine.org)
	 * @param $product array
	 * @return array of combinations
	 */
	public function getValuesCombinations(array $attributes,$i=0)
	{
		if (!isset($attributes[$i])) {
			return array();
		}
		if ($i == count($attributes) - 1) {
			return $attributes[$i];
		}

		// get combinations from subsequent arrays
		$tmp = $this->getValuesCombinations($attributes, $i + 1);

		$result = array();

		// concat each array from tmp with each element from $arrays[$i]
		foreach ($attributes[$i] as $v) {
			foreach ($tmp as $t) {
				$result[] = is_array($t) ?
					array_merge(array($v), $t) :
					array($v, $t);
			}
		}

		return $result;
	}

    /**
     * Retrieve attribute Scope
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $attribute Attribute
     * @return boolean
     */
    public function getAttributeScope(Attribute &$attribute)
    {
        $args = new stdClass();
        $args->attribute_srl = $attribute->attribute_srl;
        $output = executeQueryArray('shop.getAttributeScope',$args);
        if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
        foreach($output->data as $scope){
            $attribute->category_scope[] = $scope->category_srl;
        }
        return TRUE;
    }

    /**
     * Retrieve a list of Attributes object from the database by modul_srl
     * @author Florin Ercus (dev@xpressengine.org)
     * @param $module_srl int
     * @return Attribute list
     */
    public function getAttributesList($module_srl)
    {
        if (!is_numeric($module_srl)) throw new Exception('module_srl must be a valid int');
        $args = new stdClass();
        $args->page = Context::get('page');
        if (!$args->page) $args->page = 1;
        Context::set('page', $args->page);

        $args->module_srl = $module_srl;
        if (!isset($args->module_srl)) throw new Exception("Missing arguments for attributes list : please provide module_srl");

        $output = executeQueryArray('shop.getAttributesList', $args);
        $attributes = array();
        foreach ($output->data as $properties) {
            $o = new Attribute($properties);
            $attributes[] = $o;
        }
        $output->attributes = $attributes;
        return $output;
    }

    /**
     * Retrieve a list of configurable Attributes object from the database by modul_srl
     * @author Dan Dragan (dev@xpressengine.org)
     * @param $module_srl int
     * @return Attribute list
     */
    public function getConfigurableAttributesList($module_srl)
    {
        if (!is_numeric($module_srl)) throw new Exception('module_srl must be a valid int');
        $args = new stdClass();
        $args->module_srl = $module_srl;
        if (!isset($args->module_srl)) throw new Exception("Missing arguments for attributes list : please provide module_srl");
        $args->type = $this::TYPE_SELECT;

        $output = executeQueryArray('shop.getAttributesList', $args);
        $attributes = array();
        foreach ($output->data as $properties) {
            $o = new Attribute($properties);
            $attributes[] = $o;
        }
        $output->attributes = $attributes;
        return $output;
    }


    public function getTypes($lang, $id=null)
    {
        $arr = array(
            self::TYPE_TEXTFIELD       => $lang->types['text_field'],
            self::TYPE_TEXTAREA        => $lang->types['textarea'],
            self::TYPE_DATE            => $lang->types['date'],
            self::TYPE_BOOLEAN         => $lang->types['boolean'],
            self::TYPE_SELECT          => $lang->types['select'],
            self::TYPE_SELECT_MULTIPLE => $lang->types['select_multiple']
        );
        if (!$id) return $arr;
        if (!array_key_exists($id, $arr)) throw new Exception('Invalid type');
        return $arr[$id];
    }

}