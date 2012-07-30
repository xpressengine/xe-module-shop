<?php
    /**
     * @class  shopAdminController
     * @author Arnia (xe_dev@arnia.ro)
     * @brief  shop module admin controller class
     **/

    class shopAdminController extends shop {

        /**
         * @brief Initialization
         **/
        function init() {
        }

        /**
         * @brief Shop Admin Create
         **/
        function procShopAdminCreate() {
            $oModuleModel = &getModel('module');

            $user_id = Context::get('user_id');
            $domain = preg_replace('/^(http|https):\/\//i','', trim(Context::get('domain')));
            $vid = trim(Context::get('site_id'));

            if($domain && $vid) unset($vid);
            if(!$domain && $vid) $domain = $vid;

            if(!$user_id) return new Object(-1,'msg_invalid_request');
            if(!$domain) return new Object(-1,'msg_invalid_request');

            $tmp_user_id_list = explode(',',$user_id);
            $user_id_list = array();
            foreach($tmp_user_id_list as $k => $v){
                $v = trim($v);
                if($v) $user_id_list[] = $v;
            }
            if(count($user_id_list)==0) return new Object(-1,'msg_invalid_request');

            $output = $this->insertShop($domain, $user_id_list);
            if(!$output->toBool()) return $output;

            $this->add('module_srl', $output->get('module_srl'));
            $this->setMessage('msg_create_shop');
        }

        function insertShop($domain, $user_id_list, $settings = null) {
            if(!is_array($user_id_list)) $user_id_list = array($user_id_list);

            $oAddonAdminController = &getAdminController('addon');
            $oMemberModel = &getModel('member');
            $oModuleModel = &getModel('module');
            $oModuleController = &getController('module');

            $oShopModel = &getModel('shop');
            $oShopController = &getController('shop');
            $oDocumentController = &getController('document');
			
            $memberConfig = $oMemberModel->getMemberConfig();
            foreach($memberConfig->signupForm as $item){
            	if($item->isIdentifier) $identifierName = $item->name;
            }
            if($identifierName == "user_id") {
            	$member_srl = $oMemberModel->getMemberSrlByUserID($user_id_list[0]);
            	}
            else {
            	$member_srl = $oMemberModel->getMemberSrlByEmailAddress($user_id_list[0]);
            }
            if(!$member_srl) return new Object(-1,'msg_not_user');

            $member_info = $oMemberModel->getMemberInfoByMemberSrl($member_srl);

            if(strpos($domain, '.') !== false) $domain = strtolower($domain);
            $output = $oModuleController->insertSite($domain, 0);
            if(!$output->toBool()) return $output;
            $site_srl = $output->get('site_srl');

            $shop->site_srl = $site_srl;
            $shop->mid = $this->shop_mid;
            $shop->module = 'shop';
            $shop->module_srl = getNextSequence();
            $shop->skin = ($settings->skin) ? $settings->skin : $this->skin;
            $shop->browser_title = ($settings->title) ? $settings->title : sprintf("%s's Shop", $member_info->nick_name);
            $output = $oModuleController->insertModule($shop);

            if(!$output->toBool()) return $output;
            //$module_srl = $output->get('module_srl');
            $module_srl = $shop->module_srl;

            $site->site_srl = $site_srl;
            $site->index_module_srl = $module_srl;
			$site->domain = $domain;
            $output = $oModuleController->updateSite($site);

            $output = $oModuleController->insertSiteAdmin($site_srl, $user_id_list);

            $args->shop_title = $shop->browser_title;
            $args->module_srl = $module_srl;
            $args->member_srl = $member_srl;
            $args->timezone = $GLOBALS['_time_zone'];
            $output = executeQuery('shop.insertShop', $args);
            if(!$output->toBool()) return $output;


            $oAddonAdminController->doInsert('autolink', $site_srl);
            $oAddonAdminController->doInsert('counter', $site_srl);
            $oAddonAdminController->doInsert('member_communication', $site_srl);
            $oAddonAdminController->doInsert('member_extra_info', $site_srl);
            $oAddonAdminController->doInsert('mobile', $site_srl);
            $oAddonAdminController->doInsert('smartphone', $site_srl);
            $oAddonAdminController->doInsert('referer', $site_srl);
            $oAddonAdminController->doInsert('resize_image', $site_srl);
            $oAddonAdminController->doActivate('autolink', $site_srl);
            $oAddonAdminController->doActivate('counter', $site_srl);
            $oAddonAdminController->doActivate('member_communication', $site_srl);
            $oAddonAdminController->doActivate('member_extra_info', $site_srl);
            $oAddonAdminController->doActivate('mobile', $site_srl);
            $oAddonAdminController->doActivate('smartphone', $site_srl);
            $oAddonAdminController->doActivate('referer', $site_srl);
            $oAddonAdminController->doActivate('resize_image', $site_srl);
            $oAddonAdminController->makeCacheFile($site_srl);


            FileHandler::copyDir($this->module_path.'skins/'.$shop->skin, $oShopModel->getShopPath($module_srl));

            foreach($user_id_list as $k => $v){
                $output = $oModuleController->insertAdminId($module_srl, $v);
                if(!$output->toBool()) return $output;
            }

            $langType = Context::getLangType();
            $file = sprintf('%ssample/%s.html',$this->module_path,$langType);
            if(!file_exists(FileHandler::getRealPath($file))){
                $file = sprintf('%ssample/ko.html',$this->module_path);
            }
            $oMemberModel = &getModel('member');
            $member_info = $oMemberModel->getMemberInfoByEmailAddress($user_id_list[0]);


            $output = new Object();
            $output->add('module_srl',$module_srl);
            return $output;
        }

        function procShopAdminUpdate(){
            $vars = Context::gets('site_srl','user_id','domain','access_type','vid','module_srl','member_srl');
            if(!$vars->site_srl) return new Object(-1,'msg_invalid_request');

            if($vars->access_type == 'domain') $args->domain = strtolower($vars->domain);
            else $args->domain = $vars->vid;
            if(!$args->domain) return new Object(-1,'msg_invalid_request');

            $oMemberModel = &getModel('member');
			$member_config = $oMemberModel->getMemberConfig();
			
            $tmp_member_list = explode(',',$vars->user_id);
            $admin_list = array();
            $admin_member_srl = array();
            foreach($tmp_member_list as $k => $v){
                $v = trim($v);
                if($v){
	                if($member_config->identifier == "user_id") {
		            	$member_srl = $oMemberModel->getMemberSrlByUserID($v);
		            	}
		            else {
		            	$member_srl = $oMemberModel->getMemberSrlByEmailAddress($v);
		            }
                    if($member_srl){
                        $admin_list[] = $v;
                        $admin_member_srl[] = $member_srl;
                    }else{
                        return new Object(-1,'msg_not_user');
                    }
                }
            }

            $oModuleModel = &getModel('module');
            $site_info = $oModuleModel->getSiteInfo($vars->site_srl);
            if(!$site_info) return new Object(-1,'msg_invalid_request');

            $oModuleController = &getController('module');
            $output = $oModuleController->insertSiteAdmin($vars->site_srl, $admin_list);
            if(!$output->toBool()) return $output;

            $oModuleController->deleteAdminId($vars->module_srl);

            foreach($admin_list as $k => $v){
                $output = $oModuleController->insertAdminId($vars->module_srl, $v);
                // TODO : insertAdminId return value
                if(!$output) return new Object(-1,'msg_not_user');
                if(!$output->toBool()) return $output;
            }

            $args->site_srl = $vars->site_srl;
            $output = $oModuleController->updateSite($args);
            if(!$output->toBool()) return $output;

            unset($args);
            $args->module_srl = $vars->module_srl;
            $args->member_srl = $admin_member_srl[0];
            $output = executeQuery('shop.updateShop', $args);
            if(!$output->toBool()) return $output;

            $output = new Object(1,'success_updated');
            $output->add('module_srl',$vars->module_srl);
            return $output;
        }

        function procShopAdminDelete() {
            $oModuleController = &getController('module');
            $oCounterController = &getController('counter');
            $oAddonController = &getController('addon');
            $oEditorController = &getController('editor');
            $oShopModel = &getModel('shop');
            $oModuleModel = &getModel('module');

            $site_srl = Context::get('site_srl');
            if(!$site_srl) return new Object(-1,'msg_invalid_request');

            $site_info = $oModuleModel->getSiteInfo($site_srl);
            $module_srl = $site_info->index_module_srl;

            $oShop = new ShopInfo($module_srl);
            if($oShop->module_srl != $module_srl) return new Object(-1,'msg_invalid_request');

            $output = $oModuleController->deleteModule($module_srl);
            if(!$output->toBool()) return $output;

            $args->site_srl = $oShop->site_srl;
            executeQuery('module.deleteSite', $args);
            executeQuery('module.deleteSiteAdmin', $args);
            executeQuery('member.deleteMemberGroup', $args);
            executeQuery('member.deleteSiteGroup', $args);
            executeQuery('module.deleteLangs', $args);
            
        	//clear cache for default mid
            $vid = $site_info->domain;
            $mid = $site_info->mid;
            $oCacheHandler = &CacheHandler::getInstance('object');
            if($oCacheHandler->isSupport()){
            	$cache_key = 'object_default_mid:'.$vid.'_'.$mid;
            	$oCacheHandler->delete($cache_key);
            	$cache_key = 'object_default_mid:'.$vid.'_';
            	$oCacheHandler->delete($cache_key);
            }
            
            $lang_supported = Context::get('lang_supported');
            foreach($lang_supported as $key => $val) {
                $lang_cache_file = _XE_PATH_.'files/cache/lang_defined/'.$args->site_srl.'.'.$key.'.php';
                FileHandler::removeFile($lang_cache_file);
            }
            $oCounterController->deleteSiteCounterLogs($args->site_srl);
            $oAddonController->removeAddonConfig($args->site_srl);
            $oEditorController->removeEditorConfig($args->site_srl);

            $args->module_srl = $module_srl;
            executeQuery('shop.deleteShop', $args);
            executeQuery('shop.deleteShopFavorites', $args);
            executeQuery('shop.deleteShopTags', $args);
            executeQuery('shop.deleteShopVoteLogs', $args);
            executeQuery('shop.deleteShopMemos', $args);
            executeQuery('shop.deleteShopReferer', $args);
            executeQuery('shop.deleteShopGuestbook', $args);
            executeQuery('shop.deleteShopSupporters', $args);
            executeQuery('shop.deleteShopDenies', $args);
            executeQuery('shop.deleteShopSubscriptions', $args);
            executeQuery('shop.deletePublishLogs', $args);

            @FileHandler::removeFile(sprintf("files/cache/shop/shop_deny/%d.php",$module_srl));

            FileHandler::removeDir($oShopModel->getShopPath($module_srl));

            $this->add('module','shop');
            $this->add('page',Context::get('page'));
            $this->setMessage('success_deleted');
        }



        function initShop($site_srl){
            $oCounterController = &getController('counter');
            $oDocumentController = &getController('document');
            $oCommentController = &getController('comment');
            $oTagController = &getController('tag');
            $oAddonController = &getController('addon');
            $oEditorController = &getController('editor');
            $oTrackbackController = &getController('trackback');
            $oModuleModel = &getModel('module');
            $oShopModel = &getModel('shop');
            $oMemberModel = &getModel('member');

            $site_info = $oModuleModel->getSiteInfo($site_srl);
            $module_srl = $site_info->index_module_srl;
            $args->site_srl = $site_srl;

            $oShop = new ShopInfo($module_srl);
            if($oShop->module_srl != $module_srl) return new Object(-1,'msg_invalid_request');

            $oCounterController->deleteSiteCounterLogs($args->site_srl);
            $oAddonController->removeAddonConfig($args->site_srl);

            $args->module_srl = $module_srl;
            $output = executeQuery('shop.deleteShopFavorites', $args);
            $output = executeQuery('shop.deleteShopTags', $args);
            $output = executeQuery('shop.deleteShopVoteLogs', $args);
            $output = executeQuery('shop.deleteShopMemos', $args);
            $output = executeQuery('shop.deleteShopReferer', $args);
            $output = executeQuery('shop.deleteShopGuestbook', $args);
            $output = executeQuery('shop.deleteShopSupporters', $args);
            $output = executeQuery('shop.deletePublishLogs', $args);

            FileHandler::removeFile(sprintf("./files/cache/shop/shop_deny/%d.php",$module_srl));
            FileHandler::removeDir($oShopModel->getShopPath($module_srl));

            // delete document comment tag
            $output = $oDocumentController->triggerDeleteModuleDocuments($args);
            $output = $oCommentController->triggerDeleteModuleComments($args);
            $output = $oTagController->triggerDeleteModuleTags($args);
            $output = $oTrackbackController->triggerDeleteModuleTrackbacks($args);
            $args->module_srl = $args->module_srl *-1;

            $output = $oDocumentController->triggerDeleteModuleDocuments($args);
            $output = $oCommentController->triggerDeleteModuleComments($args);
            $output = $oTagController->triggerDeleteModuleTags($args);
            $args->module_srl = $args->module_srl *-1;

            // set category
            $obj->module_srl = $module_srl;
            $obj->title = Context::getLang('init_category_title');
            $oDocumentController->insertCategory($obj);

            FileHandler::copyDir($this->module_path.'skins/'.$this->skin, $oShopModel->getShopPath($module_srl));

            $langType = Context::getLangType();
            $file = sprintf('%ssample/%s.html',$this->module_path,$langType);
            if(!file_exists(FileHandler::getRealPath($file))){
                $file = sprintf('%ssample/ko.html',$this->module_path);
            }

            $member_info = $oMemberModel->getMemberInfoByEmailAddress($oShop->getUserId());

            $doc->module_srl = $module_srl;
            $doc->title = Context::getLang('sample_title');
            $doc->tags = Context::getLang('sample_tags');
            $doc->content = FileHandler::readFile($file);
            $doc->member_srl = $member_info->member_srl;
            $doc->user_id = $member_info->user_id;
            $doc->user_name = $member_info->user_name;
            $doc->nick_name = $member_info->nick_name;
            $doc->email_address = $member_info->email_address;
            $doc->homepage = $member_info->homepage;
            $output = $oDocumentController->insertDocument($doc, true);

            return new Object(1,'success_shop_init');
        }


    }
?>
