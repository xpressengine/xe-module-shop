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
            if(!$oDB->isColumnExists("shop_products","is_featured")) return true;
            if(!$oDB->isColumnExists("shop","discount_min_amount")) return true;
            if(!$oDB->isColumnExists("shop","discount_type")) return true;
            if(!$oDB->isColumnExists("shop","discount_amount")) return true;
            if(!$oDB->isColumnExists("shop","discount_tax_phase")) return true;
            if(!$oDB->isColumnExists("shop","out_of_stock_products")) return true;
            if(!$oDB->isColumnExists("shop","minimum_order")) return true;
            if(!$oDB->isColumnExists("shop","show_VAT")) return true;
            if(!$oDB->isColumnExists("shop_order_products","member_srl")) return true;
            if(!$oDB->isColumnExists("shop_order_products","parent_product_srl")) return true;
            if(!$oDB->isColumnExists("shop_order_products","product_type")) return true;
            if(!$oDB->isColumnExists("shop_order_products","title")) return true;
            if(!$oDB->isColumnExists("shop_order_products","description")) return true;
            if(!$oDB->isColumnExists("shop_order_products","short_description")) return true;
            if(!$oDB->isColumnExists("shop_order_products","sku")) return true;
            if(!$oDB->isColumnExists("shop_order_products","weight")) return true;
            if(!$oDB->isColumnExists("shop_order_products","status")) return true;
            if(!$oDB->isColumnExists("shop_order_products","friendly_url")) return true;
            if(!$oDB->isColumnExists("shop_order_products","price")) return true;
            if(!$oDB->isColumnExists("shop_order_products","discount_price")) return true;
            if(!$oDB->isColumnExists("shop_order_products","qty")) return true;
            if(!$oDB->isColumnExists("shop_order_products","in_stock")) return true;
            if(!$oDB->isColumnExists("shop_order_products","primary_image_filename")) return true;
            if(!$oDB->isColumnExists("shop_order_products","related_products")) return true;
            if(!$oDB->isColumnExists("shop_order_products","regdate")) return true;
            if(!$oDB->isColumnExists("shop_order_products","last_update")) return true;
            if(!$oDB->isColumnExists("shop_cart_products","title")) return true;

            if($oDB->isColumnExists("shop_categories","order")) return true;
            if(!$oDB->isColumnExists("shop_categories","list_order")) return true;

            if(!$oDB->isColumnExists("shop_addresses","firstname")) return true;
            if(!$oDB->isColumnExists("shop_addresses","lastname")) return true;

            if(!$oDB->isColumnExists("shop_payment_methods","module_srl")) return true;
            if(!$oDB->isColumnExists("shop_shipping_methods","module_srl")) return true;

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

            if(!$oDB->isColumnExists("shop_products","is_featured")) {
                $oDB->addColumn('shop_products',"is_featured","char",1);
            }

            if(!$oDB->isColumnExists("shop","show_VAT")) {
                $oDB->addColumn('shop',"show_VAT","char",1);
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

            if(!$oDB->isColumnExists("shop","minimum_order")) {
                $oDB->addColumn('shop',"minimum_order","number",20);
            }

            if(!$oDB->isColumnExists("shop_order_products","member_srl")) {
                $oDB->addColumn('shop_order_products',"member_srl","number",11, null, true);
            }

            if(!$oDB->isColumnExists("shop_order_products","parent_product_srl")) {
                $oDB->addColumn('shop_order_products',"parent_product_srl","number", 11);
            }

            if(!$oDB->isColumnExists("shop_order_products","product_type")) {
                $oDB->addColumn('shop_order_products',"product_type","varchar", 250, null, true);
            }

            if(!$oDB->isColumnExists("shop_order_products","title")) {
                $oDB->addColumn('shop_order_products',"title","varchar", 250, null, true);
            }

            if(!$oDB->isColumnExists("shop_order_products","description")) {
                $oDB->addColumn('shop_order_products',"description","bigtext");
            }

            if(!$oDB->isColumnExists("shop_order_products","short_description")) {
                $oDB->addColumn('shop_order_products',"short_description","varchar", 500);
            }

            if(!$oDB->isColumnExists("shop_order_products","sku")) {
                $oDB->addColumn('shop_order_products',"sku","varchar", 250, null, true);
            }

            if(!$oDB->isColumnExists("shop_order_products","weight")) {
                $oDB->addColumn('shop_order_products',"weight","float", 10);
            }

            if(!$oDB->isColumnExists("shop_order_products","status")) {
                $oDB->addColumn('shop_order_products',"status","varchar", 50);
            }

            if(!$oDB->isColumnExists("shop_order_products","friendly_url")) {
                $oDB->addColumn('shop_order_products',"friendly_url","varchar", 50);
            }

            if(!$oDB->isColumnExists("shop_order_products","price")) {
                $oDB->addColumn('shop_order_products',"price","float", 20, null, true);
            }

            if(!$oDB->isColumnExists("shop_order_products","discount_price")) {
                $oDB->addColumn('shop_order_products',"discount_price","float", 20, null, true);
            }

            if(!$oDB->isColumnExists("shop_order_products","qty")) {
                $oDB->addColumn('shop_order_products',"qty","float", 10);
            }

            if(!$oDB->isColumnExists("shop_order_products","in_stock")) {
                $oDB->addColumn('shop_order_products',"in_stock","char", 1, 'N');
            }

            if(!$oDB->isColumnExists("shop_order_products","primary_image_filename")) {
                $oDB->addColumn('shop_order_products',"primary_image_filename","varchar", 250);
            }

            if(!$oDB->isColumnExists("shop_order_products","related_products")) {
                $oDB->addColumn('shop_order_products',"related_products","varchar", 500);
            }

            if(!$oDB->isColumnExists("shop_cart_products","title")) {
                $oDB->addColumn('shop_cart_products',"title","varchar", 255);
            }

            if(!$oDB->isColumnExists("shop_order_products","regdate")) {
                $oDB->addColumn('shop_order_products',"regdate","date");
            }

            if(!$oDB->isColumnExists("shop_order_products","last_update")) {
                $oDB->addColumn('shop_order_products',"last_update","date");
            }

            if($oDB->isColumnExists("shop_categories","order")) {
                $oDB->dropColumn('shop_categories',"order");
            }

            if(!$oDB->isColumnExists("shop_categories","list_order")) {
                $oDB->addColumn('shop_categories',"list_order","number", 11, 0, true);
                executeQuery('shop.fixCategoriesOrder');
            }

            if(!$oDB->isColumnExists("shop_addresses","firstname")) {
                $oDB->addColumn('shop_addresses',"firstname","varchar", 45);
            }

            if(!$oDB->isColumnExists("shop_addresses","lastname")) {
                $oDB->addColumn('shop_addresses',"lastname","varchar", 45);
            }

            if(!$oDB->isColumnExists("shop_payment_methods","module_srl")) {
                $oDB->addColumn('shop_payment_methods',"module_srl","number", 11, 0, true);
            }

            if(!$oDB->isColumnExists("shop_shipping_methods","module_srl")) {
                $oDB->addColumn('shop_shipping_methods',"module_srl","number", 11, 0, true);
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
