<?php

require_once dirname(__FILE__) . '/BaseRepository.php';

/**
 * Handles logic for Shipping
 *
 * @author Corina Udrescu (dev@xpressengine.org)
 */
class ShippingRepository extends BaseRepository
{
    const SHIPPING_METHODS_DIR = 'modules/shop/shipping';

    /**
     * Returns all available shipping methods
     */
    public function getAvailableShippingMethods()
    {
        // Scan through the shipping extension directory to retrieve available methods
        $shipping_dir = _XE_PATH_ . self::SHIPPING_METHODS_DIR;
        $shipping_extensions = FileHandler::readDir($shipping_dir);

        $shipping_methods = array();
        foreach($shipping_extensions as $shipping_extension)
        {
            // Skip files (we are only interested in the folders)
            if(!is_dir($shipping_dir . DIRECTORY_SEPARATOR . $shipping_extension)) continue;

            // Convert from under_scores to CamelCase in order to get class name
            $shipping_class_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $shipping_extension)));
            $shipping_class_path = $shipping_dir
                                        . DIRECTORY_SEPARATOR . $shipping_extension
                                        . DIRECTORY_SEPARATOR . $shipping_class_name . '.php';

            if(!file_exists($shipping_class_path)) continue;

            // Include class and check if it implements the required interface (Shipping)
            require_once $shipping_class_path;

            $shipping_instance = new $shipping_class_name;
            if(!($shipping_instance instanceof ShippingMethodInterface)) continue;

            $shipping_methods[] = $shipping_instance;
        }

        return $shipping_methods;
    }


}