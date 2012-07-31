<?php
    /**
     * @class  shopModel
     * @author Arnia (xe_dev@arnia.ro)
     * @brief  shop module Model class
     **/

    class shopModel extends shop {

        /**
         * @brief Initialization
         **/
        public function init() {
        }


        /**
         * @brief get member shop
         **/
        public function getMemberShop($member_srl = 0) {
            if(!$member_srl && !Context::get('is_logged')) return new ShopInfo();

            if(!$member_srl) {
                $logged_info = Context::get('logged_info');
                $args->member_srl = $logged_info->member_srl;
            } else {
                $args->member_srl = $member_srl;
            }

            $output = executeQueryArray('shop.getMemberShop', $args);
            if(!$output->toBool() || !$output->data) return new ShopInfo();

            $shop = $output->data[0];

            $oShop = new ShopInfo();
            $oShop->setAttribute($shop);

            return $oShop;
        }

        /**
         * @brief Shop return list
         **/
        public function getShopList($args) {
            $output = executeQueryArray('shop.getShopList', $args);
            if(!$output->toBool()) return $output;

            if(count($output->data)) {
                foreach($output->data as $key => $val) {
                    $oShop = null;
                    $oShop = new ShopInfo();
                    $oShop->setAttribute($val);
                    $output->data[$key] = null;
                    $output->data[$key] = $oShop;
                }
            }
            return $output;
        }

        /**
         * @brief Shop return
         **/
        public function getShop($module_srl=0) {
            static $shops = array();
            if(!isset($shops[$module_srl])) $shops[$module_srl] = new ShopInfo($module_srl);
            return $shops[$module_srl];
        }

        /**
         * @brief return shop count
         **/
        public function getShopCount($member_srl = null) {
            if(!$member_srl) {
                $logged_info = Context::get('logged_info');
                $member_srl = $logged_info->member_srl;
            }
            if(!$member_srl) return null;

            $args->member_srl = $member_srl;
            $output = executeQuery('shop.getShopCount',$args);

            return $output->data->count;
        }


        public function getShopPath($module_srl) {
            return sprintf("./files/attach/shop/%s",getNumberingPath($module_srl));
        }

        function checkShopPath($module_srl, $skin = null) {
            $path = $this->getShopPath($module_srl);
            if(!file_exists($path)){
                $oShopController = getController('shop');
                $oShopController->resetSkin($module_srl, $skin);
            }
            return true;
        }

        public function getShopUserSkinFileList($module_srl){
            $skin_path = $this->getShopPath($module_srl);
            $skin_file_list = FileHandler::readDir($skin_path,'/(\.html|\.htm|\.css)$/');
            return $skin_file_list;
        }


        public function getModulePartConfig($module_srl=0){
			static $configs = array();

            $oModuleModel = getModel('module');
			$config = $oModuleModel->getModuleConfig('shop');
			if(!$config || !$config->allow_service) {
				$config->allow_service = array('board'=>1,'page'=>1);
			} 

			if($module_srl){
				$part_config = $oModuleModel->getModulePartConfig('shop', $module_srl);
				if(!$part_config){
					$part_config = $config;
				}else{
					$vars = get_object_vars($part_config);
					if($vars){
						foreach($vars as $k => $v){
							$config->{$k} = $v;
						}
					}
				}
			}

			$configs[$module_srl] = $config;

			return $configs[$module_srl];
		}
	}
?>
