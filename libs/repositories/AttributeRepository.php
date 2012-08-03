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
	public static function insertAttribute(Attribute &$attribute)
	{
        if ($attribute->attribute_srl) throw new Exception('A srl must NOT be specified');
        $attribute->attribute_srl = getNextSequence();
		$output = executeQuery('shop.insertAttribute', $attribute);
		if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
        return $output;
	}


    /**
     * Update an attribute
     * @author Florin Ercus (dev@xpressengine.org)
     * @param $attribute Attribute
     * @throws Exception
     * @return mixed
     */
    public static function updateAttribute(Attribute $attribute)
    {
        if (!$attribute->attribute_srl) throw new Exception('Target srl must be specified');
        $output = executeQuery('shop.updateAttribute', $attribute);
        if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
        return $output;
    }


    /**
	 * Deletes one or more attributes by $attribute_srl or $module_srl
	 *
	 * @author Florin Ercus (dev@xpressengine.org)
	 * @param $args array
	 */
	public static function deleteAttributes($args)
	{
        if (!isset($args->attribute_srls)) {
            if (!isset($args->module_srl)) throw new Exception("Please provide attribute_srls or module_srl.");
            if (!is_array($args->attribute_srls)) throw new Exception("This query expects an array of attribute srls");
        }
		$output = executeQuery('shop.deleteAttributes', $args);
		if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
        return $output;
	}

	/**
	 * Retrieve an attribute object from the database given a list of attribute srls.
	 *
	 * @author Florin Ercus (dev@xpressengine.org)
	 * @param $srls array
	 * @return boolean|array of attribute objects
	 */
	public static function getAttributes(array $srls)
	{
		$args = new stdClass();
		$args->attribute_srls = $srls;
		$output = executeQuery('shop.getAttributesBySrls', $args);
		if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
		$rez = array();
        if (!is_array($output->data)) return array(new Attribute($output->data));
        //TODO: watch this when selecting more than 1 entity
        foreach ($output->data as $data) $rez[] = new Attribute($data);
		return empty($rez) ? false : $rez;
	}

    /**
     * Retrieve a list of Attributes object from the database by modul_srl
     * @author Florin Ercus (dev@xpressengine.org)
     * @param $module_srl int
     * @return Attribute list
     */
    public static function getAttributesList($module_srl)
    {
        if (!is_numeric($module_srl)) throw new Exception('module_srl must be a valid int');
        $args = new stdClass();
        $args->page = Context::get('page');
        if (!$args->page) $args->page = 1;
        Context::set('page', $args->page);

        $args->module_srl = $module_srl;
        if (!isset($args->module_srl)) throw new Exception("Missing arguments for attributes list : please provide module_srl");

        $output = executeQuery('shop.getAttributesList', $args);
        $attributes = array();
        foreach ($output->data as $properties) {
            $o = new Attribute($properties);
            $attributes[] = $o;
        }
        $output->attributes = $attributes;
        return $output;
    }


    public static function getTypes($lang, $id=null)
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