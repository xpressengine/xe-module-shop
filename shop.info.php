<?php
    /**
     * @class  ShopInfo
     * @author Arnia (xe_dev@arnia.ro)
     * @brief  shop module Shop info class
     **/

    class ShopInfo extends Object {

        public $site_srl = null,
            $domain = null,
            $shop_srl = null,
            $module_srl = null,
            $member_srl = null,
            $shop_title = null,
            $colorset = null,
            $timezone = null;

        public function ShopInfo($shop_srl = 0) {
            if(!$shop_srl) return;
            $this->setShop($shop_srl);
        }

        public function setShop($shop_srl) {
            $this->module_srl = $this->shop_srl = $shop_srl;
            $this->_loadFromDB();
        }

        public function _loadFromDB() {
            $oShopModel = getModel('shop');

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

        public function setAttribute($attribute) {
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

        public function isHome() {
            $module_info = Context::get('module_info');
            if($this->getModuleSrl() == $module_info->module_srl) return true;
            return false;
        }

        public function getBrowserTitle() {
            if(!$this->isExists()) return;
            return $this->get('browser_title');
        }

        public function getShopTitle() {
            if(!$this->isExists()) return;
            return $this->get('shop_title');
        }

        function getFaviconSrc(){
            if(!$this->isExists()) return;
            $oShopModel = &getModel('shop');
            return $oShopModel->getShopFaviconSrc($this->module_srl);
        }

        function getDefaultFaviconSrc(){
            $oShopModel = &getModel('shop');
            $src = $oShopModel->getShopDefaultFaviconSrc();
            return $src;
        }

        public function getMid() {
            if(!$this->isExists()) return;
            return $this->get('mid');
        }

        public function getMemberSrl() {
            if(!$this->isExists()) return;
            return $this->get('member_srl');
        }

        public function getModuleSrl() {
            if(!$this->isExists()) return;
            return $this->getShopSrl();
        }

        public function getShopSrl() {
            if(!$this->isExists()) return;
            return $this->shop_srl;
        }

        public function getShopMid() {
            if(!$this->isExists()) return;
            return $this->get('mid');
        }

        public function getNickName() {
            if(!$this->isExists()) return;
            $nick_name = $this->get('nick_name');
            if(!$nick_name) $nick_name = $this->getUserId();
            return $nick_name;
        }

        public function getUserName() {
            if(!$this->isExists()) return;
            return $this->get('user_name');
        }

        public function getProfileContent() {
            if(!$this->isExists()) return;
            return $this->get('profile_content');
        }

        public function getShopContent() {
            if(!$this->isExists()) return;
            return $this->get('shop_content');
        }

        public function getTelephone() {
            if(!$this->isExists()) return;
            return $this->get('telephone');
        }

        public function getAddress() {
            if(!$this->isExists()) return;
            return $this->get('address');
        }

        public function getCurrency() {
            if(!$this->isExists()) return;
            return $this->get('currency');
        }

        public function getVAT() {
            if(!$this->isExists()) return;
            return $this->get('VAT');
        }

        public function getEmail() {
            if(!$this->isExists()) return;
            return $this->get('email_address');
        }

        public function getInputEmail(){
            if(!$this->isExists()) return;
            return $this->get('input_email');
        }

        public function getInputWebsite(){
            if(!$this->isExists()) return;
            return $this->get('input_website');
        }

        public function getUserID() {
            if(!$this->isExists()) return;
            return $this->get('user_id');
        }


        public function isExists() {
            return $this->shop_srl?true:false;
        }

        public function getPermanentUrl() {
            if(!$this->isExists()) return;
            return getUrl('','mid',$this->getMid());
        }

   }
?>
