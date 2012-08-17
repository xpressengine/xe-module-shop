<?php
/**
 * @class  shopModel
 * @author Arnia (xe_dev@arnia.ro)
 * @brief  shop module Model class
 */
class shopModel extends shop
{

	/**
	 * Initialization
	 * @author Arnia (dev@xpressengine.org)
	 */
	public function init()
	{
	}


	/**
	 * Get member shop
	 * @author Arnia (dev@xpressengine.org)
	 */
	public function getMemberShop($member_srl = 0)
	{
		if(!$member_srl && !Context::get('is_logged'))
		{
			return new ShopInfo();
		}

        $args = new stdClass();
        if(!$member_srl)
		{
			$logged_info = Context::get('logged_info');
			$args->member_srl = $logged_info->member_srl;
		}
		else
		{
            $args->member_srl = $member_srl;
		}

		$output = executeQueryArray('shop.getMemberShop', $args);
		if(!$output->toBool() || !$output->data)
		{
			return new ShopInfo();
		}

		$shop = $output->data[0];

		$oShop = new ShopInfo();
		$oShop->setAttribute($shop);

		return $oShop;
	}

	/**
	 * Shop return list
	 *
	 * @author Arnia (dev@xpressengine.org)
	 * @param $args array
	 */
	public function getShopList($args)
	{
		$output = executeQueryArray('shop.getShopList', $args);
		if(!$output->toBool())
		{
			return $output;
		}

		if(count($output->data))
		{
			foreach($output->data as $key => $val)
			{
				$oShop = NULL;
				$oShop = new ShopInfo();
				$oShop->setAttribute($val);
				$output->data[$key] = NULL;
				$output->data[$key] = $oShop;
			}
		}
		return $output;
	}

	/**
	 * Shop return
	 *
	 * @author Arnia (dev@xpressengine.org)
	 * @param $module_srl int
	 * @return ShopInfo
	 */
	public function getShop($module_srl = 0)
	{
		static $shops = array();
		if(!isset($shops[$module_srl]))
		{
			$shops[$module_srl] = new ShopInfo($module_srl);
		}
		return $shops[$module_srl];
	}

	/**
	 * Return shop count
	 *
	 * @author Arnia (dev@xpressengine.org)
	 * @param $member_srl int
	 * @return int
	 */
	public function getShopCount($member_srl = NULL)
	{
		if(!$member_srl)
		{
			$logged_info = Context::get('logged_info');
			$member_srl = $logged_info->member_srl;
		}
		if(!$member_srl)
		{
			return NULL;
		}

        $args = new stdClass();
        $args->member_srl = $member_srl;
		$output = executeQuery('shop.getShopCount', $args);

		return $output->data->count;
	}

	/**
	 * Get shop path
	 *
	 * @author Arnia (dev@xpressengine.org)
	 * @param $module_srl int
	 * @return string
	 */
	public function getShopPath($module_srl)
	{
		return sprintf("./files/attach/shop/%s", getNumberingPath($module_srl));
	}

	/**
	 *
	 * @author Arnia (dev@xpressengine.org)
	 * @param $module_srl int
	 * @param $skin
	 * @return bool
	 */
	function checkShopPath($module_srl, $skin = NULL)
	{
		$path = $this->getShopPath($module_srl);
		if(!file_exists($path))
		{
			$oShopController = getController('shop');
			$oShopController->resetSkin($module_srl, $skin);
		}
		return TRUE;
	}

	/**
	 * Get shop user skin file list
	 *
	 * @author Arnia (dev@xpressengine.org)
	 * @param $module_srl
	 * @return string[]
	 */
	public function getShopUserSkinFileList($module_srl)
	{
		$skin_path = $this->getShopPath($module_srl);
		$skin_file_list = FileHandler::readDir($skin_path, '/(\.html|\.htm|\.css)$/');
		return $skin_file_list;
	}

	/**
	 * Get module part config
	 *
	 * @author Arnia (dev@xpressengine.org)
	 * @param $module_srl int
	 * @return mixed
	 */
	public function getModulePartConfig($module_srl = 0)
	{
		static $configs = array();

		$oModuleModel = getModel('module');
		$config = $oModuleModel->getModuleConfig('shop');
		if(!$config || !$config->allow_service)
		{
			$config->allow_service = array('board' => 1, 'page' => 1);
		}

		if($module_srl)
		{
			$part_config = $oModuleModel->getModulePartConfig('shop', $module_srl);
			if(!$part_config)
			{
				$part_config = $config;
			}
			else
			{
				$vars = get_object_vars($part_config);
				if($vars)
				{
					foreach($vars as $k => $v)
					{
						$config->{$k} = $v;
					}
				}
			}
		}

		$configs[$module_srl] = $config;

		return $configs[$module_srl];
	}

    /**
     * Returns an instance of the Product repository
     *
     * @author Dan Dragan(dev@xpressengine.org)
     * @return ProductRepository
     */
    public function getProductRepository()
    {
        require_once dirname(__FILE__) . '/libs/repositories/ProductRepository.php';
        return new ProductRepository();
    }

	/**
	 * Returns an instance of the Image repository
	 *
	 * @author Dan Dragan(dev@xpressengine.org)
	 * @return ImageRepository
	 */
	public function getImageRepository()
	{
		require_once dirname(__FILE__) . '/libs/repositories/ImageRepository.php';
		return new ImageRepository();
	}

    /**
     * Returns an instance of the Attribute repository
     *
     * @author Dan Dragan(dev@xpressengine.org)
     * @return AttributeRepository
     */
    public function getAttributeRepository()
    {
        require_once dirname(__FILE__) . '/libs/repositories/AttributeRepository.php';
        return new AttributeRepository();
    }

	/**
	 * Returns an instance of the Product Category repository
	 *
	 * @author Corina Udrescu (dev@xpressengine.org)
	 * @return CategoryRepository
	 */
    public function getCategoryRepository()
	{
		require_once dirname(__FILE__) . '/libs/repositories/CategoryRepository.php';
		return new CategoryRepository();
	}

    /**
     * Returns an instance of the Payment Gateways repository
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @return PaymentGatewayRepository
     */
    public function getPaymentGatewayRepository()
    {
        require_once dirname(__FILE__) . '/libs/repositories/PaymentGatewayRepository.php';
        return new PaymentGatewayRepository();
    }

    /**
     * Returns an instance of the Product Category Manager
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @return PaymentGatewayManager
     */
    public function getPaymentGatewayManager()
    {
        require_once dirname(__FILE__) . '/libs/model/PaymentGatewayManager.php';
        return new PaymentGatewayManager();
    }

    /**
     * Returns an instance of the Cart repository
     *
     * @author Florin Ercus (dev@xpressengine.org)
     * @return CartRepository
     */
    public function getCartRepository()
    {
        require_once dirname(__FILE__) . '/libs/repositories/CartRepository.php';
        return new CartRepository();
    }

    /**
     * Returns an instance of the Guest repository
     *
     * @author Florin Ercus (dev@xpressengine.org)
     * @return CartRepository
     */
    public function getGuestRepository()
    {
        require_once dirname(__FILE__) . '/libs/repositories/GuestRepository.php';
        return new GuestRepository();
    }

}