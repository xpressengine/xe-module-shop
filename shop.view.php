<?php

    /**
     * @class  shopView
     * @author Arnia (xe_dev@arnia.ro)
     * @brief  shop module View class
     **/

    class shopView extends shop {

        /**
         * @brief Initialization
         **/
        public function init() {
            $oShopModel = getModel('shop');
            if(preg_match("/ShopTool/",$this->act) ) {
                $this->initTool($this);

            } else {
                $this->initService($this);
            }
        }

        /**
         * @brief Shop common init
         **/
        public function initCommon($is_other_module = false){
            if(!$this->checkXECoreVersion('1.4.3')) return $this->stop(sprintf(Context::getLang('msg_requried_version'),'1.4.3'));

            $oShopModel = getModel('shop');
            $oShopController = getController('shop');
            $oModuleModel = getModel('module');

            $site_module_info = Context::get('site_module_info');
            if(!$this->module_srl) {
                $site_module_info = Context::get('site_module_info');
                $site_srl = $site_module_info->site_srl;
                if($site_srl) {
                    $this->module_srl = $site_module_info->index_module_srl;
                    $this->module_info = $oModuleModel->getModuleInfoByModuleSrl($this->module_srl);
                    if (!$is_other_module){
                        Context::set('module_info',$this->module_info);
                        Context::set('mid',$this->module_info->mid);
                        Context::set('current_module_info',$this->module_info);
                    }
                }
            }

            if(!$this->module_info->skin) $this->module_info->skin = $this->skin;

            $preview_skin = Context::get('preview_skin');
            if($oModuleModel->isSiteAdmin(Context::get('logged_info'))&&$preview_skin) {
                if(is_dir($this->module_path.'skins/'.$preview_skin)) {
                    $shop_config->skin = $this->module_info->skin = $preview_skin;
                }
            }

            if (!$is_other_module){
                Context::set('module_info',$this->module_info);
                Context::set('current_module_info', $this->module_info);
            }

            $this->shop = $oShopModel->getShop($this->module_info->module_srl);
            $this->site_srl = $this->shop->site_srl;
            Context::set('shop',$this->shop);

            if($this->shop->timezone) $GLOBALS['_time_zone'] = $this->shop->timezone;



        }

        /**
         * @brief Shop init tool
         **/
        public function initTool(&$oModule, $is_other_module = false){
            if (!$oModule) $oModule = $this;

            $this->initCommon($is_other_module);

            $oShopModel = getModel('shop');

            $site_module_info = Context::get('site_module_info');
            $shop = $oShopModel->getShop($site_module_info->index_module_srl);

            $info = Context::getDBInfo();




            if ($is_other_module){
                $oModule->setLayoutPath($this->module_path.'tpl');
                $oModule->setLayoutFile('_tool_layout');
            }else{
                $template_path = sprintf("%stpl",$this->module_path);
                $this->setTemplatePath($template_path);
                $this->setTemplateFile(str_replace('dispShopTool','',$this->act));
            }

            if($_COOKIE['tclnb']) Context::addBodyClass('lnbClose');
            else Context::addBodyClass('lnbToggleOpen');

            // set browser title 
            Context::setBrowserTitle($shop->get('browser_title') . ' - admin');
        }

        /**
         * @brief shop init service
         **/
        public function initService(&$oModule, $is_other_module = false, $isMobile = false){
            if (!$oModule) $oModule = $this;

            $oShopModel = getModel('shop');

            $this->initCommon($is_other_module);

            Context::addJsFile($this->module_path.'tpl/js/shop_service.js');

            $preview_skin = Context::get('preview_skin');
			if(!$isMobile)
			{
				if($is_other_module){
					$path_method = 'setLayoutPath';
					$file_method = 'setLayoutFile';
					$css_path_method = 'getLayoutPath';
					Context::set('shop_mode', 'module');
				}else{
					$path_method = 'setTemplatePath';
					$file_method = 'setTemplateFile';
					$css_path_method = 'getTemplatePath';
				}

				if(!$preview_skin){
					$oShopModel->checkShopPath($this->module_srl, $this->module_info->skin);
					$oModule->{$path_method}($oShopModel->getShopPath($this->module_srl));
				}else{
					$oModule->{$path_method}($this->module_path.'skins/'.$preview_skin);
				}

				$oModule->{$file_method}('shop');
				Context::addCssFile($oModule->{$css_path_method}().'shop.css',true,'all','',100);
			}

            Context::set('root_url', Context::getRequestUri());
            Context::set('home_url', getFullSiteUrl($this->shop->domain));
            Context::set('profile_url', getSiteUrl($this->shop->domain,'','mid',$this->module_info->mid,'act','dispShopProfile'));
            if(Context::get('is_logged')) Context::set('admin_url', getSiteUrl($this->shop->domain,'','mid',$this->module_info->mid,'act','dispShopToolDashboard'));
            else Context::set('admin_url', getSiteUrl($shop->domain,'','mid','shop','act','dispShopToolLogin'));
            Context::set('shop_title', $this->shop->get('shop_title'));


            // set browser title 
            Context::setBrowserTitle($this->shop->get('browser_title'));
        }


        /**
         * @brief Tool dashboard
         **/
        public function dispShopToolDashboard(){
            set_include_path(_XE_PATH_."libs/PEAR");
            require_once('PEAR.php');
            require_once('HTTP/Request.php');

            $oCounterModel = getModel('counter');
            $oDocumentModel = getModel('document');
            $oCommentModel = getModel('comment');
            $oShopModel = getModel('shop');

            $url = sprintf("http://news.shop.kr/%s/news.php", Context::getLangType());
            $cache_file = sprintf("%sfiles/cache/shop/news/%s%s.cache.xml", _XE_PATH_,getNumberingPath($this->module_srl),Context::getLangType());
            if(!file_exists($cache_file) || filemtime($cache_file)+ 60*60 < time()) {
                FileHandler::writeFile($cache_file,'');

                if(__PROXY_SERVER__!==null) {
                    $oRequest = new HTTP_Request(__PROXY_SERVER__);
                    $oRequest->setMethod('POST');
                    $oRequest->_timeout = $timeout;
                    $oRequest->addPostData('arg', serialize(array('Destination'=>$url)));
                } else {
                    $oRequest = new HTTP_Request($url);
                    if(!$content_type) $oRequest->addHeader('Content-Type', 'text/html');
                    else $oRequest->addHeader('Content-Type', $content_type);
                    if(count($headers)) {
                        foreach($headers as $key => $val) {
                            $oRequest->addHeader($key, $val);
                        }
                    }
                    $oRequest->_timeout = 2;
                }
                if(isSiteID($this->shop->domain)) $oRequest->addHeader('REQUESTURL', Context::getRequestUri().$this->shop->domain);
                else $oRequest->addHeader('REQUESTURL', $this->shop->domain);
                $oResponse = $oRequest->sendRequest();
                $body = $oRequest->getResponseBody();
                FileHandler::writeFile($cache_file, $body);
            }

            if(file_exists($cache_file)) {
                $oXml = new XmlParser();
                $buff = $oXml->parse(FileHandler::readFile($cache_file));

                $item = $buff->news->item;
                if($item) {
                    if(!is_array($item)) $item = array($item);

                    foreach($item as $key => $val) {
                        $obj = null;
                        $obj->title = $val->body;
                        $obj->date = $val->attrs->date;
                        $obj->url = $val->attrs->url;
                        $news[] = $obj;
                    }
                    Context::set('news', $news);
                }
            }

            $time = time();
            $w = date("D");
            while(date("D",$time) != "Sun") {
                $time += 60*60*24;
            }
            $time -= 60*60*24;
            while(date("D",$time)!="Sun") {
                $thisWeek[] = date("Ymd",$time);
                $time -= 60*60*24;
            }
            $thisWeek[] = date("Ymd",$time);
            asort($thisWeek);
            $thisWeekCounter = $oCounterModel->getStatus($thisWeek, $this->site_srl);

            $time -= 60*60*24;
            while(date("D",$time)!="Sun") {
                $lastWeek[] = date("Ymd",$time);
                $time -= 60*60*24;
            }
            $lastWeek[] = date("Ymd",$time);
            asort($lastWeek);
            $lastWeekCounter = $oCounterModel->getStatus($lastWeek, $this->site_srl);

            $max = 0;
            foreach($thisWeek as $day) {
                $v = (int)$thisWeekCounter[$day]->unique_visitor;
                if($v && $v>$max) $max = $v;
                $status->week[date("D",strtotime($day))]->this = $v;
            }
            foreach($lastWeek as $day) {
                $v = (int)$lastWeekCounter[$day]->unique_visitor;
                if($v && $v>$max) $max = $v;
                $status->week[date("D",strtotime($day))]->last = $v;
            }
            $status->week_max = $max;
            $idx = 0;
            foreach($status->week as $key => $val) {
                $_item[] = sprintf("<item id=\"%d\" name=\"%s\" />", $idx, $thisWeek[$idx]);
                $_thisWeek[] = $val->this;
                $_lastWeek[] = $val->last;
                $idx++;
            }

            $buff = '<?xml version="1.0" encoding="utf-8" ?><Graph><gdata title="Shop Counter" id="data2"><fact>'.implode('',$_item).'</fact><subFact>';
            $buff .= '<item id="0"><data name="'.Context::getLang('this_week').'">'.implode('|',$_thisWeek).'</data></item>';
            $buff .= '<item id="1"><data name="'.Context::getLang('last_week').'">'.implode('|',$_lastWeek).'</data></item>';
            $buff .= '</subFact></gdata></Graph>';
            Context::set('xml', $buff);

            $counter = $oCounterModel->getStatus(array(0,date("Ymd")),$this->site_srl);
            $status->total_visitor = $counter[0]->unique_visitor;
            $status->visitor = $counter[date("Ymd")]->unique_visitor;


            Context::set('status', $status);

            unset($args);
            $args->module_srl = $this->module_srl;
            $args->page = 1;
            $args->list_count = 5;
        }

        /**
         * @brief Login
         **/
        public function dispShopToolLogin() {
            Context::addBodyClass('logOn');
        }


        public function dispShopToolLayoutConfigSkin() {
            $oModuleModel = getModel('module');

            $skins = $oModuleModel->getSkins($this->module_path);
            if(count($skins)) {
                foreach($skins as $skin_name => $info) {
                    $large_screenshot = $this->module_path.'skins/'.$skin_name.'/screenshots/large.jpg';
                    if(!file_exists($large_screenshot)) $large_screenshot = $this->module_path.'tpl/img/@large.jpg';
                    $small_screenshot = $this->module_path.'skins/'.$skin_name.'/screenshots/small.jpg';
                    if(!file_exists($small_screenshot)) $small_screenshot = $this->module_path.'tpl/img/@small.jpg';

                    unset($obj);
                    $obj->title = $info->title;
                    $obj->description = $info->description;
                    $_arr_author = array();
                    for($i=0,$c=count($info->author);$i<$c;$i++) {
                        $name =  $info->author[$i]->name;
                        $homepage = $info->author[$i]->homepage;
                        if($homepage) $_arr_author[] = '<a href="'.$homepage.'" onclick="window.open(this.href); return false;">'.$name.'</a>';
                        else $_arr_author[] = $name;
                    }
                    $obj->author = implode(',',$_arr_author);
                    $obj->large_screenshot = $large_screenshot;
                    $obj->small_screenshot = $small_screenshot;
                    $obj->date = $info->date;
                    $output[$skin_name] = $obj;
                }
            }
            Context::set('skins', $output);
            Context::set('cur_skin', $output[$this->module_info->skin]);
        }

        public function dispShopToolLayoutConfigEdit() {
            $oShopModel = getModel('shop');
            $skin_path = $oShopModel->getShopPath($this->module_srl);

            $skin_file_list = $oShopModel->getShopUserSkinFileList($this->module_srl);
            $skin_file_content = array();
            foreach($skin_file_list as $file){
				if(preg_match('/^shop/',$file)){
					$skin_file_content[$file] = FileHandler::readFile($skin_path . $file);
				}
            }
            foreach($skin_file_list as $file){
				if(!in_array($file,$skin_file_content)){
					$skin_file_content[$file] = FileHandler::readFile($skin_path . $file);
				}
            }

            Context::set('skin_file_content',$skin_file_content);

            $user_image_path = sprintf("%suser_images/", $oShopModel->getShopPath($this->module_srl));
            $user_image_list = FileHandler::readDir($user_image_path);
            Context::set('user_image_path',$user_image_path);
            Context::set('user_image_list',$user_image_list);
        }

        public function dispShopToolManageAttributes()
        {
            $shopModel = getModel('shop');
            $repository = $shopModel->getAttributeRepository();
            $output = $repository->getAttributesList($this->module_info->module_srl);

            Context::set('attributes_list', $output->attributes);
            Context::set('page_navigation', $output->page_navigation);
        }

        /**
         * @brief attribute add page
         */
        public function dispShopToolAddAttribute()
        {
            $shopModel = getModel('shop');
            $attributeRepository = $shopModel->getAttributeRepository();
            Context::set('types', $attributeRepository->getTypes(Context::get('lang')));

            // Retrieve existing categories
            $categoryRepository = $shopModel->getCategoryRepository();
            $tree = $categoryRepository->getCategoriesTree($this->module_srl);

            // Prepare tree for display
            $flat_tree = $tree->toFlatStructure();
            Context::set('flat_tree', $flat_tree);
        }

        public function dispShopToolEditAttribute()
        {
            $shopModel = getModel('shop');
            $attributeRepository = $shopModel->getAttributeRepository();
            $srl = Context::get('attribute_srl');
            if (!$attribute = $attributeRepository->getAttributes(array($srl))) throw new Exception("Attribute doesn't exist");
            Context::set('attribute', $attribute);
            Context::set('types', $attributeRepository->getTypes(Context::get('lang')));

            // Retrieve existing categories
            $categoryRepository = $shopModel->getCategoryRepository();
            $tree = $categoryRepository->getCategoriesTree($this->module_srl);

            // Prepare tree for display
            $flat_tree = $tree->toFlatStructure();
            Context::set('flat_tree', $flat_tree);

            $this->setTemplateFile('AddAttribute');
        }

        /**
         * @brief Shop display product tool page
         */
        public function dispShopToolManageProducts(){
            $shopModel = getModel('shop');

            $product_repository = $shopModel->getProductRepository();
            $module_srl = $this->module_info->module_srl;
            $output = $product_repository->getProductList($module_srl);
            Context::set('product_list',$output->products);

			$category_repository = $shopModel->getCategoryRepository();
			$tree = $category_repository->getCategoriesTree($module_srl);
			$flat_tree = $tree->toFlatStructure();
			Context::set('category_list', $flat_tree);

            Context::set('page_navigation',$output->page_navigation);
        }

        /**
         * @brief Shop display product tool page
         */
        public function dispShopToolEditProduct(){
            $shopModel = getModel('shop');
            $productRepository = $shopModel->getProductRepository();
            $product_srl = Context::get('product_srl');
            $product = $productRepository->getProduct($product_srl);
            Context::set('product',$product);
            $this->setTemplateFile('AddProduct');

            // Retrieve existing categories
            $categoryRepository = $shopModel->getCategoryRepository();
            $tree = $categoryRepository->getCategoriesTree($this->module_srl);

            // Prepare tree for display
            $flat_tree = $tree->toFlatStructure();
            Context::set('flat_tree', $flat_tree);
        }

        /**
         * @brief Shop display product add page
         */
        public function dispShopToolAddProduct(){
            $shopModel = getModel('shop');

			// Retrieve all attributes
            $attributeRepository = $shopModel->getAttributeRepository();
            $output = $attributeRepository->getAttributesList($this->module_info->module_srl);
			foreach($output->attributes as $attribute)
			{
				$attributeRepository->getAttributeScope($attribute);
			}
			Context::set('attributes_list', $output->attributes);

            // Retrieve existing categories
            $categoryRepository = $shopModel->getCategoryRepository();
            $tree = $categoryRepository->getCategoriesTree($this->module_srl);

            // Prepare tree for display
            $flat_tree = $tree->toFlatStructure();
            Context::set('flat_tree', $flat_tree);


        }

        /**
         * @brief Shop home
         **/
        public function dispShop() {
        }

		// region Product category
		public function dispShopToolManageCategories()
		{
			// Retrieve existing categories
			$shopModel = getModel('shop');
			$repository = $shopModel->getCategoryRepository();
			$tree = $repository->getCategoriesTree($this->module_srl);

			// Prepare tree for display
			$flat_tree = $tree->toFlatStructure();
			Context::set('flat_tree', $flat_tree);

			// Initialize new empty Category object
			require_once('libs/model/Category.php');
			$category = new Category();
			$category->module_srl = $this->module_srl;
			Context::set('category', $category);
		}

		public function dispShopToolAddCategory()
		{
		}

		// endregion

        // startregion Payment Gateways

        /*
         * Displays the PG management page
         */
        public function dispShopToolManagePaymentGateways()
        {

            // base directory
            $baseDir = dirname(__FILE__) . "/payment_gateways/";
            $dirHandle = opendir($baseDir);

            // get gateways
            $shopModel = getModel('shop');
            $repository = $shopModel->getPaymentGatewayRepository();
            $output = $repository->getAllGateways();
            Context::set('pg',$output->data);

            // Payment gateway list
            $pg_dirs = array();

            while( $file = readdir($dirHandle) ) {
                if(is_dir($baseDir.$file) && $file != '.' && $file != '..') {
                    $pg_dirs[] = $file;
                }
            }

            Context::set('pg_dirs',$pg_dirs);

        }

        // endregion


    }
?>
