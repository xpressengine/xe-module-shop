<?php
    /**
     * @class  shopController
     * @author NHN (developers@xpressengine.com)
     * @brief  shop module Controller class
     **/

    class shopController extends shop {
        /**
         * @brief Initialization
         **/
        public function init() {
            $oShopModel = getModel('shop');
            $oModuleModel = getModel('module');

            $site_module_info = Context::get('site_module_info');
            $site_srl = $site_module_info->site_srl;
            if($site_srl) {
                $this->module_srl = $site_module_info->index_module_srl;
                $this->module_info = $oModuleModel->getModuleInfoByModuleSrl($this->module_srl);
                Context::set('module_info',$this->module_info);
                Context::set('mid',$this->module_info->mid);
                Context::set('current_module_info',$this->module_info);
            }

            $this->shop = $oShopModel->getShop($this->module_srl);
            $this->site_srl = $this->shop->site_srl;
            Context::set('shop',$this->shop);

        }

        public function procShopLogin() {
            $oMemberController = getController('member');

            if(!$user_id) $user_id = Context::get('user_id');
            $user_id = trim($user_id);

            if(!$password) $password = Context::get('password');
            $password = trim($password);

            if(!$keep_signed) $keep_signed = Context::get('keep_signed');

            $stat = 0;

            if(!$user_id) {
                $stat = -1;
                $msg = Context::getLang('null_user_id');
            }
            if(!$password) {
                $stat = -1;
                $msg = Context::getLang('null_password');
            }

            if(!$stat) {
                $output = $oMemberController->doLogin($user_id, $password, $keep_signed=='Y'?TRUE:FALSE);
                if(!$output->toBool()) {
                    $stat = -1;
                    $msg = $output->getMessage();
                }
            }

			if($stat == -1) return new Object(-1, $msg);

			$vid = Context::get('vid');
			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'act', 'dispShopToolDashboard', 'vid', $vid);
			$this->setRedirectUrl($returnUrl);
        }


        public function updateShop($args){
            $output = executeQuery('shop.updateShop', $args);
            return $output;
        }

        public function updateShopInfo($module_srl,$args){
            $args->module_srl = $module_srl;
            $output = executeQuery('shop.updateShop', $args);
            return $output;
        }

        public function procShopInfoUpdate(){
            $oModuleController = getController('module');
            $oModuleModel = getModel('module');
            $oShopModel = getModel('shop');

            if(in_array(strtolower('dispShopToolConfigInfo'),$this->custom_menu->hidden_menu)) return new Object(-1,'msg_invalid_request');

            $args = Context::gets('shop_title','shop_content','timezone');
			$args->module_srl = $this->module_srl;
            $output = executeQuery('shop.updateShopInfo',$args);
            if(!$output->toBool()) return $output;

            $module_info = $oModuleModel->getModuleInfoByModuleSrl($this->module_srl);
            $module_info->browser_title = $args->shop_title;
            $output = $oModuleController->updateModule($module_info);
            if(!$output->toBool()) return $output;

			unset($args);
            $args->index_module_srl = $this->module_srl;
            $args->default_language = Context::get('language');
            $args->site_srl = $this->site_srl;
            $output = $oModuleController->updateSite($args);
            if(!$output->toBool()) return $output;

            if(Context::get('delete_icon')=='Y') $this->deleteShopFavicon($this->module_srl);

            $favicon = Context::get('favicon');
            if(Context::isUploaded()&&is_uploaded_file($favicon['tmp_name'])) $this->insertShopFavicon($this->module_srl,$favicon['tmp_name']);

            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('move_myshop');
        }


        /**
         * @brief shop colorset modify
         **/
        public function procShopColorsetModify() {
            $oShopModel = getModel('shop');
            $myshop = $oShopModel->getMemberShop();
            if(!$myshop->isExists()) return new Object(-1, 'msg_not_permitted');

            $colorset = Context::get('colorset');
            if(!$colorset) return new Object(-1,'msg_invalid_request');

            $this->updateShopColorset($myshop->getModuleSrl(), $colorset);

            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('move_myshop');
        }

        /*
         * brief function for product insert
         * @author Dan Dragan (dev@xpressengine.org)
         */
        public function procShopToolInsertProduct(){
            $shopModel = getModel('shop');
            $repository = $shopModel->getProductRepository();

            $args = Context::getRequestVars();
            $logged_info = Context::get('logged_info');
            $args->member_srl = $logged_info->member_srl;
            $args->module_srl = $this->module_info->module_srl;

            $product = new Product($args);
            try
            {
                if($product->product_srl === NULL)
                {
                    $product_srl = $repository->insertProduct($product);
                    $this->setMessage("success_registed");
                }
                else
                {
                    $repository->updateProduct($product);
                    $this->setMessage("success_updated");
                }
            }
            catch(Exception $e)
            {
                return new Object(-1, $e->getMessage());
            }

            $returnUrl = getNotEncodedUrl('', 'act', 'dispShopToolManageProducts');
            $this->setRedirectUrl($returnUrl);
        }

        /*
         * @brief function for attribute insert
         * @author Florin Ercus (dev@xpressengine.org)
         */
        public function procShopToolInsertAttribute() {
            $shopModel = getModel('shop');
            $repository = $shopModel->getAttributeRepository();

            $args = Context::getRequestVars();
            $args->module_srl = $this->module_info->module_srl;
            $logged_info = Context::get('logged_info');
            $args->member_srl = $logged_info->member_srl;

            $attribute = new Attribute($args);
            $attribute->module_srl = $this->module_srl;
            try
            {
                if ($attribute->attribute_srl) {
                    $output = $repository->updateAttribute($attribute);
                    $this->setMessage("success_updated");
                }
                else {
                    $output = $repository->insertAttribute($attribute);
                    $this->setMessage("success_registed");
                }
            }
            catch(Exception $e) {
                return new Object(-1, $e->getMessage());
            }

            $returnUrl = getNotEncodedUrl('', 'act', 'dispShopToolManageAttributes');
            $this->setRedirectUrl($returnUrl);
        }


        /*
        * @brief function for product delete
        * @author Dan Dragan (dev@xpressengine.org)
        */
        public function procShopToolDeleteProduct(){
            $shopModel = getModel('shop');
            $repository = $shopModel->getProductRepository();

            $args = new stdClass();
            $args->product_srl = Context::get('product_srl');

            $repository->deleteProduct($args);
            $this->setMessage("success_deleted");
            $returnUrl = getNotEncodedUrl('', 'act', 'dispShopToolManageProducts');
            $this->setRedirectUrl($returnUrl);
        }

        /*
        * @brief function for multiple products delete
        * @author Dan Dragan (dev@xpressengine.org)
        */
        public function procShopToolDeleteProducts(){
            $shopModel = getModel('shop');
            $repository = $shopModel->getProductRepository();

            $args->product_srls = explode(',',Context::get('product_srls'));
            $repository->deleteProducts($args);
            $this->setMessage("success_deleted");
            $returnUrl = getNotEncodedUrl('', 'act', 'dispShopToolManageProducts');
            $this->setRedirectUrl($returnUrl);
        }

        /*
        * @brief function for multiple attributes delete
        * @author Florin Ercus (dev@xpressengine.org)
        */
        public function procShopToolDeleteAttributes(){
            $shopModel = getModel('shop');
            $repository = $shopModel->getAttributeRepository();
            $args = new stdClass();
            $args->attribute_srls = explode(',', Context::get('attribute_srls'));
            $repository->deleteAttributes($args);
            $this->setMessage("success_deleted");
            $this->setRedirectUrl(getNotEncodedUrl('', 'act', 'dispShopToolManageAttributes'));
        }

        public function procShopToolLayoutConfigSkin() {
            $oModuleModel = getModel('module');
            $oModuleController = getController('module');
            $oShopModel = getModel('shop');

            if(in_array(strtolower('dispShopToolLayoutConfigSkin'),$this->custom_menu->hidden_menu)) return new Object(-1,'msg_invalid_request');

            $skin = Context::get('skin');
            if(!is_dir($this->module_path.'skins/'.$skin)) return new Object();

            $module_info  = $oModuleModel->getModuleInfoByModuleSrl($this->module_srl);
            $module_info->skin = $skin;
            $output = $oModuleController->updateModule($module_info);
            if(!$output->toBool()) return $output;

            FileHandler::removeDir($oShopModel->getShopPath($this->module_srl));
            FileHandler::copyDir($this->module_path.'skins/'.$skin, $oShopModel->getShopPath($this->module_srl));
        }


        public function procShopToolLayoutResetConfigSkin() {
            $oModuleModel = getModel('module');
            $module_info  = $oModuleModel->getModuleInfoByModuleSrl($this->module_srl);
            $skin = $module_info->skin;

            $this->resetSkin($this->module_srl,$skin);
        }

        public function resetSkin($module_srl,$skin=NULL){
            if(!$skin) $skin = $this->skin;
            if(!file_exists($this->module_path.'skins/'.$skin)) $skin = $this->skin;
            $oShopModel = getModel('shop');
            FileHandler::removeDir($oShopModel->getShopPath($module_srl));
            FileHandler::copyDir($this->module_path.'skins/'.$skin, $oShopModel->getShopPath($module_srl));
        }


        public function procShopToolLayoutConfigEdit() {
            if(in_array(strtolower('dispShopToolLayoutConfigEdit'),$this->custom_menu->hidden_menu)) return new Object(-1,'msg_invalid_request');

            $oShopModel = getModel('shop');
            $skin_path = $oShopModel->getShopPath($this->module_srl);

            $skin_file_list = $oShopModel->getShopUserSkinFileList($this->module_srl);
            foreach($skin_file_list as $file){
				// Replace . with _
				// Request variable names that contain . are modified by PHP to replace the . with _
				// see http://php.net/manual/en/language.variables.external.php
                $content = Context::get(str_replace('.', '_', $file));
                if($this->_checkDisabledFunction($content)) return new Object(-1,'msg_used_disabled_function');
                FileHandler::writeFile($skin_path.$file, $content);
            }

			$vid = Context::get('vid');
			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'vid', $vid, 'act', 'dispShopToolLayoutConfigEdit');
			$this->setRedirectUrl($returnUrl);
        }

        public function procShopToolUserSkinExport(){
            if(!$this->module_srl) return new Object('-1','msg_invalid_request');

            $oShopModel = getModel('shop');
            $skin_path = FileHandler::getRealPath($oShopModel->getShopPath($this->module_srl));

            $tar_list = FileHandler::readDir($skin_path,'/(\.css|\.html|\.htm|\.js)$/');

            $img_list = FileHandler::readDir($skin_path."img",'/(\.png|\.jpeg|\.jpg|\.gif|\.swf)$/');
            for($i=0,$c=count($img_list);$i<$c;$i++) $tar_list[] = 'img/' . $img_list[$i];

            $userimages_list = FileHandler::readDir($skin_path."user_images",'/(\.png|\.jpeg|\.jpg|\.gif|\.swf)$/');
            for($i=0,$c=count($userimages_list);$i<$c;$i++) $tar_list[] = 'user_images/' . $userimages_list[$i];

            require_once(_XE_PATH_.'libs/tar.class.php');
            chdir($skin_path);
            $tar = new tar();

            $replace_path = getNumberingPath($this->module_srl,3);
            foreach($tar_list as $key => $file) $tar->addFile($file,$replace_path,'__TEXTYLE_SKIN_PATH__');

            $stream = $tar->toTarStream();
            $filename = 'ShopUserSkin_' . date('YmdHis') . '.tar';
            header("Cache-Control: ");
            header("Pragma: ");
            header("Content-Type: application/x-compressed");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header('Content-Disposition: attachment; filename="'. $filename .'"');
            header("Content-Transfer-Encoding: binary\n");
            echo $stream;

            Context::close();
            exit();
         }

        public function procShopToolUserSkinImport(){
            if(!$this->module_srl) exit();

            // check upload
            if(!Context::isUploaded()) exit();
            $file = Context::get('file');
            if(!is_uploaded_file($file['tmp_name'])) exit();
            if(!preg_match('/\.(tar)$/i', $file['name'])) exit();

            $oShopModel = getModel('shop');
            $skin_path = FileHandler::getRealPath($oShopModel->getShopPath($this->module_srl));

            $tar_file = $skin_path . 'shop_skin.tar';

            FileHandler::removeDir($skin_path);
            FileHandler::makeDir($skin_path);

            if(!move_uploaded_file($file['tmp_name'], $tar_file)) exit();

            require_once(_XE_PATH_.'libs/tar.class.php');

            $tar = new tar();
            $tar->openTAR($tar_file);

            if(!$tar->getFile('shop.html')) return;

            $replace_path = getNumberingPath($this->module_srl,3);
            foreach($tar->files as $key => $info) {
                FileHandler::writeFile($skin_path . $info['name'],str_replace('__TEXTYLE_SKIN_PATH__',$replace_path,$info['file']));
            }

            FileHandler::removeFile($tar_file);
        }


        public function _checkDisabledFunction($str){
            if(preg_match('!<\?.*\?>!is',$str,$match)) return TRUE;

            $disabled = array(
                    // file
                    'fopen','link','unlink','popen','symlink','touch','readfile','rmdir','mkdir','rename','copy','delete','file_get_contents','file_put_contents','tmpname','parse_ini_file'
                    // dir
                    ,'dir'
                   // database
                   ,'mysql','sqlite','PDO','cubird','ibase','pg_','_pconnect','_connect','oci'
                   // network /etc
                   ,'fsockopen','pfsockopen','shmop_','shm_','sem_','dl','ini_','php','zend','pear','header','create_function','call_*','imap','openlog','socket','ob_','cookie','eval','exec','shell_exec','passthru'
                   // XE
                   ,'filehandler','displayhandler','xehttprequest','context','getmodel','getcontroller','getview','getadminmodel','getadmincontroller','getadminview','getdbinfo','executequery','executequeryarray'
            );
            unset($match);

            $disabled = '/('.implode($disabled, '|').')/i';
            preg_match_all('!<\!--@(.*?)-->!is', $str, $match1);
            preg_match_all('/ ([^(^ ]*) ?\(/i', ' '.join(' ',$match1[1]),$match_func1);
            preg_match_all('/{([^{]*)}/i',$str,$match2);
            preg_match_all('/ ([^(^ ]*) ?\(/i', ' '.join(' ',$match2[1]),$match_func2);
            $match1 = array_unique($match_func1[1]);
            $match2 = array_unique($match_func2[1]);
            preg_match($disabled, implode('|', $match1), $matches1);
            preg_match($disabled, implode('|', $match2), $matches2);

            if(count($matches1) || count($matches2)) return TRUE;

            return FALSE;
        }

        /**
         * @brief shop insert config
         **/
        public function insertShopConfig($shop) {
            $oModuleController = getController('module');
            $oModuleController->insertModuleConfig('shop', $shop);
        }

        /**
         * @brief shop update browser title
         **/
        public function updateShopBrowserTitle($module_srl, $browser_title) {
            $args->module_srl = $module_srl;
            $args->browser_title = $browser_title;
            return executeQuery('shop.updateShopBrowserTitle', $args);
        }

        /**
         * @brief action forward apply layout
         **/
        public function triggerApplyLayout(&$oModule) {
            if(!$oModule || $oModule->getLayoutFile()=='popup_layout.html') return new Object();

            if(Context::get('module')=='admin') return new Object();

            if(in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) return new Object();

            if($oModule->act == 'dispMemberLogout') return new Object();

            $site_module_info = Context::get('site_module_info');
            if(!$site_module_info || !$site_module_info->site_srl || $site_module_info->mid != $this->shop_mid) return new Object();

            $oModuleModel = getModel('module');
            $xml_info = $oModuleModel->getModuleActionXml('shop');
            if($oModule->mid == $this->shop_mid && isset($xml_info->action->{$oModule->act})) return new Object();

            $oShopModel = getModel('shop');
            $oShopView = getView('shop');

            Context::set('layout',NULL);

            $oShopView->initTool($oModule, TRUE);
            // $oShopView->initService($oModule, true);
            return new Object();
        }


        public function procShopToolInit(){
            if(!$this->site_srl) return new Object(-1,'msg_invalid_request');

            $oShopAdminController = getAdminController('shop');
            $output = $oShopAdminController->initShop($this->site_srl);
            return $output;
        }

        public function procShopToolLive(){
			$_SESSION['live'] = time();
		}

		// region Product Category
		/**
		 * Inserts or updates a product category
		 *
		 * @author Corina Udrescu (dev@xpressengine.org)
		 * @return Object
		 */
		public function procShopToolInsertCategory()
		{
			$args = Context::gets('category_srl', 'module_srl', 'parent_srl', 'filename', 'title', 'description', 'friendly_url', 'include_in_navigation_menu');
			$file_info = Context::get('file_info');

			$delete_image = Context::get('delete_image');
			$vid = Context::get('vid');

			$shopModel = getModel('shop');
			$repository = $shopModel->getCategoryRepository();

			// Upload image
			if($file_info)
			{
				// If a previous picture exists, we delete it
				if($args->filename)
				{
					$repository->deleteCategoryImage($args->filename);
				}
				// Then we add the new one and update the filename
				$args->filename = $repository->saveCategoryImage(
					$args->module_srl
					, $file_info['name']
					, $file_info['tmp_name']
				);
			}
			else if($delete_image && $args->filename)
			{
				$repository->deleteCategoryImage($args->filename);
				$args->filename = '';
			}

			$category = new Category($args);
			try
			{
				if($category->category_srl === NULL)
				{
					$repository->insertCategory($category);
					$this->setMessage("success_registed");
				}
				else
				{
					$repository->updateCategory($category);
					$this->setMessage("success_updated");
				}
			}
			catch(Exception $e)
			{
				return new Object(-1, $e->getMessage());
			}

			$returnUrl = getNotEncodedUrl('', 'vid', $vid, 'act', 'dispShopToolManageCategories');
			$this->setRedirectUrl($returnUrl);
		}

		/**
		 * Returns product category details
		 * Called through AJAX
		 *
		 * @author Corina Udrescu (dev@xpressengine.org)
		 * @return Object
		 */
		public function procShopServiceGetCategory()
		{
			$category_srl = Context::get('category_srl');
			if(!isset($category_srl)) return new Object(-1, 'msg_invalid_request');

			$shopModel = getModel('shop');
			$repository = $shopModel->getCategoryRepository();
			$category = $repository->getCategory($category_srl);

			$this->add('category', $category);
		}

		/**
		 * Deletes a product category
		 * Called through AJAX
		 *
		 * @author Corina Udrescu (dev@xpressengine.org)
		 * @return Object
		 */
		public function procShopServiceDeleteCategory()
		{
			$category_srl = Context::get('category_srl');
			if(!isset($category_srl)) return new Object(-1, 'msg_invalid_request');

			$shopModel = getModel('shop');
			$repository = $shopModel->getCategoryRepository();
			$args = new stdClass();
			$args->category_srl = $category_srl;

			try{
				$repository->deleteCategory($args);
				$this->setMessage('success_deleted');
			}
			catch(Exception $e)
			{
				$this->setError(-1);
				$this->setMessage($e->getMessage());
			}
		}

		// endregion

        // region Payment Gateway

        /**
         * Activates a gateway
         *
         * @author Daniel Ionescu (dev@xpressengine.org)
         * @throws exception
         * @return object
         */
        public function procUpdateShopActivateGateway() {

            $name = Context::get('name');

            if ($name != '') {

                $shopModel = getModel('shop');
                $repository = $shopModel->getPaymentGatewayRepository();

                $pg = new PaymentGateway();
                $pg->status = 1;
                $pg->name = $name;

                $args = new stdClass();
                $args->name = $name;
                $gateway_exists = $repository->getGateway($args);

                // if we cannot find the selected gateway we insert it else we update it
                if ($gateway_exists) {

                    $repository->updatePaymentGatewayStatus($pg);

                } else {

                    $repository->insertPaymentGateway($pg);

                }

            }

            $returnUrl = getNotEncodedUrl('', 'vid', $vid, 'act', 'dispShopToolManagePaymentGateways');
            $this->setRedirectUrl($returnUrl);
        }

        public function procUpdateShopDeactivateGateway() {

            $name = Context::get('name');

            if ($name != '') {

                $shopModel = getModel('shop');
                $repository = $shopModel->getPaymentGatewayRepository();

                $gateway = new PaymentGateway();
                $gateway->name = $name;
                $gateway->status = 0;

                $repository->updatePaymentGatewayStatus($gateway);

            }

            $returnUrl = getNotEncodedUrl('', 'vid', $vid, 'act', 'dispShopToolManagePaymentGateways');
            $this->setRedirectUrl($returnUrl);
        }

        // end region

    }
?>
