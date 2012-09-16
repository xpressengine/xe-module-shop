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
     * Get Favicon source
     *
     * @author Arnia (dev@xpressengine.org)
     * @param $module_srl
     * @return string
     */
    public function getShopFaviconSrc($module_srl) {
        $path = $this->getShopFaviconPath($module_srl);
        $filename = sprintf('%sfavicon.ico', $path);
        if(!is_dir($path) || !file_exists($filename)) return $this->getShopDefaultFaviconSrc();

        return Context::getRequestUri().$filename."?rnd=".filemtime($filename);
    }

    /**
     * Get Favicon path
     *
     * @author Arnia (dev@xpressengine.org)
     * @param $module_srl
     * @return string
     */
    public function getShopFaviconPath($module_srl) {
        return sprintf('files/attach/shop/favicon/%s', getNumberingPath($module_srl,3));
    }

    /**
     * Get default path
     *
     * @author Arnia (dev@xpressengine.org)
     * @return string
     */
    function getShopDefaultFaviconSrc(){
        return sprintf("%s%s", Context::getRequestUri(), 'modules/shop/tpl/img/favicon.ico');
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
		return new CategoryRepository();
	}

    /**
     * Returns an instance of the Payment Gateways repository
     *
     * @author Daniel Ionescu (dev@xpressengine.org)
     * @return PaymentMethodRepository
     */
    public function getPaymentMethodRepository()
    {
        return new PaymentMethodRepository();
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
        return new CartRepository();
    }

    /**
     * Returns an instance of the Order repository
     *
     * @author Florin Ercus (dev@xpressengine.org)
     * @return OrderRepository
     */
    public function getOrderRepository()
    {
        return new OrderRepository();
    }

    /**
     * Returns an instance of the Invoice repository
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @return InvoiceRepository
     */
    public function getInvoiceRepository()
    {
        return new InvoiceRepository();
    }

    /**
     * Returns an instance of the Shipment repository
     *
     * @author Dan Dragan (dev@xpressengine.org)
     * @return ShipmentRepository
     */
    public function getShipmentRepository()
    {
        return new ShipmentRepository();
    }

    /**
     * Returns an instance of the Address repository
     *
     * @author Florin Ercus (dev@xpressengine.org)
     * @return AddressRepository
     */
    public function getAddressRepository()
    {
        return new AddressRepository();
    }

    /**
     * Returns an instance of the Guest repository
     *
     * @author Florin Ercus (dev@xpressengine.org)
     * @return GuestRepository
     */
    public function getGuestRepository()
    {
        return new GuestRepository();
    }

    /**
     * Includes Zip Handler Class
     *
     * @author Dan Dragan(dev@xpressengine.org)
     * @return GuestRepository
     */
    public function includeZipHandler()
    {
        require_once dirname(__FILE__) . '/libs/ZipHandler.class.php';
    }

    /**
     * Returns an instance of the Shipping repository
     */
    public function getShippingRepository()
    {
        return new ShippingRepository();
    }

    /**
     * Returns an instance of the Customer repository
     */
    public function getCustomerRepository()
    {
        return new CustomerRepository();
    }

    // region Menu
    /**
	 * Get shop menu
	 * Menu structure can be seen in the php cache file
	 *
	 * @param int $site_srl Virtual side srl
	 *
	 * @return null
	 */
	public function getShopMenu($site_srl)
	{
		$shop_menu_srl = $this->getShopMenuSrl($site_srl);
		/**
		 * @var menuAdminModel $menuModel
		 */
		$menuModel = getAdminModel('menu');
		$shop_menu = $menuModel->getMenu($shop_menu_srl);
		if(!file_exists($shop_menu->php_file))
		{
			$menuAdminController = getAdminController('menu');
			$menuAdminController->makeXmlFile($shop_menu_srl);
		}

		$menu = NULL;
		@include($shop_menu->php_file); // Populates $menu with menu data
		return $menu;
	}


	/**
	 * Get shop menu srl
	 */
	public function getShopMenuSrl($site_srl)
	{
		/**
		 * @var menuModel $menuModel
		 */
		$menuAdminModel = getAdminModel('menu');
		$menus = $menuAdminModel->getMenus($site_srl);
		if(!$menus)
		{
			$menu_srl = $this->makeMenu($site_srl, "Shop", "Menu");
			return $menu_srl;
		}
		return $menus[0]->menu_srl;
	}

	/**
	 * Insert a menu
	 *
	 * @param $site_srl
	 * @param $title
	 * @param $menu_title
	 * @return object
	 */
	public function makeMenu($site_srl, $title, $menu_title) {
		$args = new stdClass();
		$args->site_srl = $site_srl;
		$args->title = $title.' - '.$menu_title;
		$args->menu_srl = getNextSequence();
		$args->listorder = $args->menu_srl * -1;

		$output = executeQuery('menu.insertMenu', $args);
		if(!$output->toBool()) return $output;

		return $args->menu_srl;
	}

	/**
	 * Insert a menu item
	 *
	 * @param     $menu_srl
	 * @param int $parent_srl
	 * @param     $mid
	 * @param     $name
	 * @return mixed
	 */
	public function insertMenuItem($menu_srl, $parent_srl = 0, $mid, $name) {
		// 변수를 다시 정리 (form문의 column과 DB column이 달라서)
		$args = new stdClass();
		$args->menu_srl = $menu_srl;
		$args->menu_item_srl = getNextSequence();
		$args->parent_srl = $parent_srl;
		$args->name = $name;
		$args->url = $mid;
		$args->open_window = 'N';
		$args->expand = 'N';
		$args->normal_btn = NULL;
		$args->hover_btn = NULL;
		$args->active_btn = NULL;
		$args->group_srls = NULL;
		$args->listorder = 0;
		$output = executeQuery('menu.insertMenuItem', $args);

		$menuAdminController = getAdminController('menu');
		$menuAdminController->makeXmlFile($menu_srl);

		return $args->menu_item_srl;
	}

	/**
	 * Update menu item
	 */
	public function updateMenuItem($menu_srl, $menu_item_srl, $menu_title)
	{
		$args = new stdClass();
		$args->menu_item_srl = $menu_item_srl;
		$args->name = $menu_title;
		$output = executeQuery('menu.updateMenuItem', $args);

		$menuAdminController = getAdminController('menu');
		$menuAdminController->makeXmlFile($menu_srl);
	}

	/**
	 * Delete menu item
	 */
	public function deleteMenuItem($menu_srl, $menu_item_srl)
	{
		$args = new stdClass();
		$args->menu_item_srl = $menu_item_srl;
		$output = executeQuery("menu.deleteMenuItem", $args);
		if(!$output->toBool())
        {
            throw new Exception($output->getMessage());
        }

		$menuAdminController = getAdminController('menu');
		$menuAdminController->makeXmlFile($menu_srl);
	}
	// endregion

}