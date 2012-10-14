<?php

class ShopAutoloader
{
    protected $bulkPath = '../classes';

    public function __construct($bulkPath=null)
    {
        //todo: test against static methods
        spl_autoload_register(array($this, 'loader'));
        if ($bulkPath) $this->bulkPath = $bulkPath;
    }

    protected function loader($class)
    {
        //if the class is in the /classes dir then we load it
        if (file_exists($path = __DIR__ . "/{$this->bulkPath}/$class.php")) {
            $this->getFile($path, $class);
        }
        else //else use the model and repo
        {
            if (substr($class, strlen($class) - strlen('Repository'), strlen($class)) === 'Repository') {
                $class = substr($class, 0, strlen($class) - strlen('Repository'));
            }
            if ($this->isCalledFromShop()) {
                if ($class === 'Base' || $class === 'BaseItem') {
                    $itemClass = 'BaseItem';
                    $repoClass = 'BaseRepository';
                }
                else {
                    if(in_array($class, array('SimpleProduct', 'ConfigurableProduct', 'DownloadableProduct', 'ICartItemProduct', 'ProductFactory')))
                        $class = 'Product';

                    $itemClass = $class;
                    $repoClass = $class . 'Repository';
                }

                if (!in_array($class, array('ShippingMethod','PaymentMethod'))) {
                    $this->getFile(__DIR__ . "/../model/$itemClass.php", $itemClass);
                }
                else
                {
                    // TODO Remove this include hack; should add a map or something
                    if(strpos($itemClass, 'Payment') !== false)
                    {
                        $itemClass = $itemClass . 'Abstract';
                        $this->getFile(__DIR__ . "/../../plugins_payment/$itemClass.php", $itemClass);
                    }
                    elseif(strpos($itemClass, 'Shipping') !== false)
                    {
                        $itemClass = $itemClass . 'Abstract';
                        $this->getFile(__DIR__ . "/../../plugins_shipping/$itemClass.php", $itemClass);
                    }
                }
                if(in_array($class, array('ShopMenu', 'CartProduct', 'OrderProduct'))) return;
                $this->getFile(__DIR__ . "/../repositories/$repoClass.php", $repoClass);
            }
        }
    }

    protected function getFile($file, $classToCheck=null)
    {
        if (!file_exists($file)) {
            throw new Exception("File $file not found");
        }
        if (!is_readable($file)) {
            throw new Exception("File $file exists, but it's not readable");
        }
        require_once $file;
        if ($classToCheck === 'Product') $classToCheck = array('ConfigurableProduct', 'SimpleProduct', 'DownloadableProduct', 'ICartItemProduct', 'ProductFactory');
        if ($classToCheck) {
            if (!is_array($classToCheck)) $classToCheck = array($classToCheck);
            foreach ($classToCheck as $class) {
                $reflection = new ReflectionClass($class);
                if (!$reflection->isInstantiable()) {
                    if (!$reflection->isAbstract() && !$reflection->isInterface()) {
                        throw new Exception("$class class is not instantiable");
                    }
                }
            }
        }
    }

    protected function isCalledFromShop()
    {
        $backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        foreach ($backTrace as $i=>$node) {
            if ($node['function'] == 'loader' && $node['class'] == get_called_class()) break;
        }
        $callerFile = self::changeWinSlashes($backTrace[$i+1]['file']);
        $shopRoot = self::changeWinSlashes(realpath(_XE_PATH_ . 'modules/shop'));
        return substr($callerFile, 0, strlen($shopRoot)) === $shopRoot;
    }

    public static function changeWinSlashes($str)
    {
        return str_replace('\\', '/', $str);
    }

    public static function isInstantiable($class)
    {
        $reflection = new ReflectionClass($class);
        return $reflection->isInstantiable();
    }

}