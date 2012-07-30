<?php
    /**
     * @class  shop
     * @author Arnia (xe_dev@arnia.ro)
     * @brief  shop module main class
     **/

    require_once(_XE_PATH_.'modules/shop/shop.info.php');

    class shop extends ModuleObject {

        /**
         * @berif default mid
         **/
        var $shop_mid = 'shop';

        /**
         * @berif default skin
         **/
        var $skin = 'default';


        var $add_triggers = array(
            array('display', 'shop', 'controller', 'triggerMemberMenu', 'before'),
            array('moduleHandler.proc', 'shop', 'controller', 'triggerApplyLayout', 'after')
        );

        /**
         * @brief module install
         **/
        function moduleInstall() {
            $oModuleController = &getController('module');

            foreach($this->add_triggers as $trigger) {
                $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
            }

        }

        /**
         * @brief check for update method
         **/
        function checkUpdate() {
            $oDB = &DB::getInstance();
            $oModuleModel = &getModel('module');

            foreach($this->add_triggers as $trigger) {
                if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) return true;
            }
            return false;
        }

        /**
         * @brief module update
         **/
        function moduleUpdate() {
            $oDB = &DB::getInstance();
            $oModuleModel = &getModel('module');
            $oModuleController = &getController('module');

            foreach($this->add_triggers as $trigger) {
                if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) {
                    $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
                }
            }
           return new Object(0, 'success_updated');
        }

        /**
         * @brief recompile cache
         **/
        function recompileCache() {
        }


        function checkXECoreVersion($requried_version){
			$result = version_compare(__ZBXE_VERSION__,$requried_version,'>=');
			if($result != 1) return false;

			return true;
        }
    }
?>
