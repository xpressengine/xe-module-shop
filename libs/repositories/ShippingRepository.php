<?php

require_once dirname(__FILE__) . '/BaseRepository.php';

/**
 * Handles logic for Shipping
 *
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class ShippingRepository extends BaseRepository
{
    public static $SHIPPING_METHODS_DIR;

    public function __construct()
    {
        self::$SHIPPING_METHODS_DIR = _XE_PATH_ . 'modules/shop/plugins_shipping';

    }

    private function getShippingMethodInstanceByFolderName($shipping_extension)
    {
        // Skip files (we are only interested in the folders)
        if(!is_dir(self::$SHIPPING_METHODS_DIR . DIRECTORY_SEPARATOR . $shipping_extension))
        {
            throw new Exception("Given folder name is not a directory");
        }

        // Convert from under_scores to CamelCase in order to get class name
        $shipping_class_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $shipping_extension)));
        $shipping_class_path = self::$SHIPPING_METHODS_DIR
            . DIRECTORY_SEPARATOR . $shipping_extension
            . DIRECTORY_SEPARATOR . $shipping_class_name . '.php';

        if(!file_exists($shipping_class_path)) {
            throw new Exception("Shipping class was not found in given folder");
        };

        // Include class and check if it implements the required interface (Shipping)
        require_once $shipping_class_path;

        $shipping_instance = new $shipping_class_name;
        if(!($shipping_instance instanceof ShippingMethodAbstract))
        {
            throw new Exception("Shipping class does not extend required ShippingMethodAbstract");
        };

        return $shipping_instance;
    }

    /**
     * Returns all available shipping methods
     */
    public function getAvailableShippingMethods()
    {
        // Scan through the shipping extension directory to retrieve available methods
        $shipping_extensions = FileHandler::readDir(self::$SHIPPING_METHODS_DIR);

        $shipping_methods = array();
        foreach($shipping_extensions as $shipping_extension)
        {
            try
            {
                $shipping_instance = $this->getShippingMethodInstanceByFolderName($shipping_extension);
                $shipping_methods[] = $shipping_instance;
            }
            catch(Exception $e)
            {
                continue;
            }
        }

        return $shipping_methods;
    }

    /**
     * Get a certain shipping method instance
     *
     * @param string $code Folder name of the shipping method
     *
     * @return ShippingMethodAbstract
     */
    public function getShippingMethod($code)
    {
        try
        {
            return $this->getShippingMethodInstanceByFolderName($code);
        }
        catch(Exception $e)
        {
            return null;
        }
    }

    public function updateShippingMethod($shipping_info)
    {
        if(!isset($shipping_info->code))
        {
            throw new Exception("You must provide a value for code when updating");
        }

        $shipping_method = $this->getShippingMethod($shipping_info->code);
        if(!$shipping_method)
        {
            throw new Exception("No shipping method exists for the code " . $shipping_info->code);
        }

        $shipping_method->save($shipping_info);
    }


}