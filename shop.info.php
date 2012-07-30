<?php
    /**
     * @class  ShopInfo
     * @author Arnia (xe_dev@arnia.ro)
     * @brief  shop module Shop info class
     **/

    class ShopInfo extends Object {

        var $site_srl = null;
        var $domain = null;
        var $shop_srl = null;
        var $module_srl = null;
        var $member_srl = null;
        var $shop_title = null;
        var $colorset = null;
        var $timezone = null;

        function ShopInfo($shop_srl = 0) {
            if(!$shop_srl) return;
            $this->setShop($shop_srl);
        }

        function setShop($shop_srl) {
            $this->module_srl = $this->shop_srl = $shop_srl;
            $this->_loadFromDB();
        }

        function _loadFromDB() {
            $oShopModel = &getModel('shop');

            if(!$this->shop_srl) return;
            $args->module_srl = $this->shop_srl;
            $output = executeQuery('shop.getShop', $args);
            if(!$output->toBool()||!$output->data) return;
            $this->setAttribute($output->data);

            $config = $oShopModel->getModulePartConfig($this->module_srl);
            if($config && count($config)) {
                foreach($config as $key => $val) {
                    $this->add($key, $val);
                }
            }
        }

        function setAttribute($attribute) {
            if(!$attribute->module_srl) {
                $this->shop_srl = null;
                return;
            }
            $this->module_srl = $this->shop_srl = $attribute->module_srl;
            $this->member_srl = $attribute->member_srl;
            $this->colorset = $attribute->colorset;
            $this->domain = $attribute->domain;
            $this->site_srl = $attribute->site_srl;
            $this->timezone = $attribute->timezone;
            $this->default_language = $attribute->default_language;

            $this->adds($attribute);
        }

        function isHome() {
            $module_info = Context::get('module_info');
            if($this->getModuleSrl() == $module_info->module_srl) return true;
            return false;
        }

        function getBrowserTitle() {
            if(!$this->isExists()) return;
            return $this->get('browser_title');
        }

        function getShopTitle() {
            if(!$this->isExists()) return;
            return $this->get('shop_title');
        }

        function getMid() {
            if(!$this->isExists()) return;
            return $this->get('mid');
        }

        function getMemberSrl() {
            if(!$this->isExists()) return;
            return $this->get('member_srl');
        }

        function getModuleSrl() {
            if(!$this->isExists()) return;
            return $this->getShopSrl();
        }

        function getShopSrl() {
            if(!$this->isExists()) return;
            return $this->shop_srl;
        }

        function getShopMid() {
            if(!$this->isExists()) return;
            return $this->get('mid');
        }

        function getNickName() {
            if(!$this->isExists()) return;
            $nick_name = $this->get('nick_name');
            if(!$nick_name) $nick_name = $this->getUserId();
            return $nick_name;
        }

        function getUserName() {
            if(!$this->isExists()) return;
            return $this->get('user_name');
        }
        function getProfileContent() {
            if(!$this->isExists()) return;
            return $this->get('profile_content');
        }
        function getShopContent() {
            if(!$this->isExists()) return;
            return $this->get('shop_content');
        }
        function getEmail() {
            if(!$this->isExists()) return;
            return $this->get('email_address');
        }

        function getInputEmail(){
            if(!$this->isExists()) return;
            return $this->get('input_email');
        }

        function getInputWebsite(){
            if(!$this->isExists()) return;
            return $this->get('input_website');
        }

        function getUserID() {
            if(!$this->isExists()) return;
            return $this->get('user_id');
        }


        function isExists() {
            return $this->shop_srl?true:false;
        }

        function getPermanentUrl() {
            if(!$this->isExists()) return;
            return getUrl('','mid',$this->getMid());
        }

   }
?>
