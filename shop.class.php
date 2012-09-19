<?php
    /**
     * @class  shop
     * @author Arnia (xe_dev@arnia.ro)
     * @brief  shop module main class
     **/

    require_once(_XE_PATH_.'modules/shop/shop.info.php');
    require_once(__DIR__ . '/libs/autoload/autoload.php');

    class shop extends ModuleObject {

        /**
         * @brief default mid
         **/
        public $shop_mid = 'shop';

        /**
         * @brief default skin
         **/
        public $skin = 'default';

        public $add_triggers = array(
            array('display', 'shop', 'controller', 'triggerMemberMenu', 'before'),
            array('moduleHandler.proc', 'shop', 'controller', 'triggerApplyLayout', 'after'),
            array('member.doLogin', 'shop', 'controller', 'triggerLoginBefore', 'before'),
            array('member.doLogin', 'shop', 'controller', 'triggerLoginAfter', 'after')
        );

        /**
         * @brief module install
         **/
        public function moduleInstall() {
            $oModuleController = getController('module');

            foreach($this->add_triggers as $trigger) {
                $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
            }

        }

        /**
         * @brief check for update method
         **/
        public function checkUpdate() {
            $oDB = &DB::getInstance();
            $oModuleModel = getModel('module');

            foreach($this->add_triggers as $trigger) {
                if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) return true;
            }

            if(!$oDB->isColumnExists("shop_orders","transaction_id")) return true;
            if(!$oDB->isColumnExists("shop","currency_symbol")) return true;
            if(!$oDB->isColumnExists("shop_products","discount_price")) return true;
            if(!$oDB->isColumnExists("shop","discount_min_amount")) return true;
            if(!$oDB->isColumnExists("shop","discount_type")) return true;
            if(!$oDB->isColumnExists("shop","discount_amount")) return true;
            if(!$oDB->isColumnExists("shop","discount_tax_phase")) return true;
            if(!$oDB->isColumnExists("shop","out_of_stock_products")) return true;

            return false;
        }

        /**
         * @brief module update
         **/
        public function moduleUpdate() {
            $oDB = &DB::getInstance();
            $oModuleModel = getModel('module');
            $oModuleController = getController('module');

            foreach($this->add_triggers as $trigger) {
                if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) {
                    $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
                }
            }

            if(!$oDB->isColumnExists("shop_orders","transaction_id")) {
                $oDB->addColumn('shop_orders',"transaction_id","varchar",128);
            }

            if(!$oDB->isColumnExists("shop","currency_symbol")) {
                $oDB->addColumn('shop',"currency_symbol","varchar",5);
            }

            if(!$oDB->isColumnExists("shop_products","discount_price")) {
                $oDB->addColumn('shop_products',"discount_price","float",20);
            }

            if(!$oDB->isColumnExists("shop","discount_min_amount")) {
                $oDB->addColumn('shop',"discount_min_amount","number",20);
            }

            if(!$oDB->isColumnExists("shop","discount_type")) {
                $oDB->addColumn('shop',"discount_type","varchar",40);
            }

            if(!$oDB->isColumnExists("shop","discount_amount")) {
                $oDB->addColumn('shop',"discount_amount","number",20);
            }

            if(!$oDB->isColumnExists("shop","discount_tax_phase")) {
                $oDB->addColumn('shop',"discount_tax_phase","varchar",40);
            }

            if(!$oDB->isColumnExists("shop","out_of_stock_products")) {
                $oDB->addColumn('shop',"out_of_stock_products","char",1);
            }

           return new Object(0, 'success_updated');
        }

        /**
         * @brief recompile cache
         **/
        public function recompileCache() {
        }


        public function checkXECoreVersion($requried_version){
			$result = version_compare(__XE_VERSION__, $requried_version, '>=');
			if ($result != 1) return false;
			return true;
        }
    }
?>
