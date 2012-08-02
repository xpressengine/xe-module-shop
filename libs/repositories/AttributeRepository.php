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
	/**
	 * Insert a new attribute; returns the ID of the newly created record
	 *
	 * @author Florin Ercus (dev@xpressengine.org)
	 * @param $attribute Attribute
	 * @return int
	 */
	public static function insertAttribute(Attribute $attribute)
	{
		$attribute->attribute_srl = getNextSequence();
		$output = executeQuery('shop.insertProductCategory', $attribute);
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
            if (!isset($args->module_srls)) throw new Exception("Please provide attribute_srls or module_srl.");
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
	 * @param $attribute_srls array
	 * @return array of attribute objects
	 */
	public static function getAttributes(array $attribute_srls)
	{
		$args = new stdClass();
		$args->attribute_srls = $attribute_srls;
		$output = executeQuery('shop.getAttributesBySrls', $args);
		if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
		$rez = array();
        foreach ($output->data as $data) {
            $attribute = new Attribute();
            $rez[] = $attribute->loadFromXeDataObject($data);
        }
		return $rez;
	}
}
