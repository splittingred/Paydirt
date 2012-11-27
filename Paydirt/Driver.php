<?php
namespace Paydirt;
spl_autoload_register(function ($className) {
    $className = ltrim($className, "\\");
    preg_match('/^(.+)?([^\\\\]+)$/U', $className, $match);
    $className = str_replace("\\", "/", $match[1]) . str_replace(["\\", "_"], "/", $match[2]) . ".php";
    try {
        include_once $className;
    } catch (\Exception $e) {
        print_r($e->getTrace());
    }
});
defined('PAYDIRT_PATH') or define('PAYDIRT_PATH',dirname(dirname(__FILE__)).'/');

$paths = explode(PATH_SEPARATOR, get_include_path());
$paths[] = PAYDIRT_PATH;
set_include_path(implode(PATH_SEPARATOR,$paths));

/**
 * @package paydirt
 * @subpackage payments
 */
abstract class Driver {
    const MYSQL_TIMESTAMP_FORMAT = '%Y-%m-%d %H:%M:%S';
    const LOG_LEVEL_DEBUG = 0;
    const LOG_LEVEL_INFO = 0;
    const LOG_LEVEL_WARN = 0;
    const LOG_LEVEL_ERROR = 0;
    const LOG_LEVEL_FATAL = 0;

    /** @var array $config */
    public $config = array();
    /** @var array $loadedClasses */
    public $loadedClasses = array();
    /** @var string $driverName */
    public $driverName = '';
    /** @var string $path */
    public $path = '';

    /**
     * Get the proper driver instance based on the desired payment API
     *
     * @static
     * @param string $driverName
     * @param array $config
     * @return Driver|null
     */
    public static function getInstance($driverName = 'Chargify',array $config = array()) {
        $driver = null;
        $driverName = strtolower($driverName);
        $className = '\\Paydirt\\'.ucfirst($driverName).'\\Driver';
        if (class_exists($className)) {
            /** @var Driver $driver */
            $driver = new $className($config);
            $driver->initialize();
            $driver->setPath(dirname(__FILE__).'/'.$driverName.'/');
        }
        return $driver;
    }

    function __construct(array $config = array()) {
        $this->config = array_merge($this->config,$config);
    }

    /**
     * Initialize the driver
     */
    public function initialize() {}

    public function getOption($key, $options = null, $default = null, $skipEmpty = false) {
        $option= $default;
        if (is_array($key)) {
            if (!is_array($option)) {
                $default= $option;
                $option= array();
            }
            foreach ($key as $k) {
                $option[$k]= $this->getOption($k, $options, $default);
            }
        } elseif (is_string($key) && !empty($key)) {
            if (is_array($options) && !empty($options) && array_key_exists($key, $options) && (!$skipEmpty || ($skipEmpty && $options[$key] !== ''))) {
                $option= $options[$key];
            } elseif (is_array($this->config) && !empty($this->config) && array_key_exists($key, $this->config) && (!$skipEmpty || ($skipEmpty && $this->config[$key] !== ''))) {
                $option= $this->config[$key];
            }
        }
        return $option;
    }

    public function log($level,$message) {
        return true;
    }


    /**
     * Set the file path of the driver
     * @param string $path
     */
    public function setPath($path) {
        $this->path = $path;
    }

    /**
     * Get an object. Example:
     * $subscription = $driver->getObject('Subscription',$id);
     *
     * @param string $className
     * @param mixed $criteria
     * @return null|\Paydirt\Object
     */
    public function getObject($className,$criteria = array()) {
        $object = null;
        if ($classConst = $this->loadClass($className)) {
            $object = $classConst::load($this,$criteria);
        }
        return $object;
    }

    /**
     * Get a new instance of an object. Example:
     * $subs = $driver->getCollection('Subscription',$params);
     *
     * @param string $className
     * @param mixed $criteria
     * @return \Paydirt\Object|null
     */
    public function newObject($className,$criteria = array()) {
        $object = null;
        if ($classConst = $this->loadClass($className)) {
            /** @var \Paydirt\Object $object */
            $object = $classConst::newInstance($this,$criteria);
            if ($object) {
                $object->fromArray($criteria);
            }
        }
        return $object;
    }

    public function getCollection($className,$criteria = array()) {
        $collection = null;
        if ($classConst = $this->loadClass($className)) {
            $collection = $classConst::loadCollection($this,$criteria);
        }
        return $collection;
    }

    /**
     * Load an object class
     *
     * @param string $className
     * @return bool|string
     */
    public function loadClass($className) {
        $driverName = ucfirst(strtolower($this->driverName));

        if (!in_array($className,$this->loadedClasses)) {
            $this->loadedClasses[] = $className;
        }

        $trueClassName = '\\Paydirt\\'.$driverName.'\\'.$className;
        if (!class_exists($trueClassName)) {
            $this->log(Driver::LOG_LEVEL_ERROR,'[Paydirt] Payment object class '.$trueClassName.' not found!');
            return false;
        }
        return $trueClassName;
    }
}


interface AccountInterface {
    public function close();
    public function open();
    public function getTransactions();
    public function getSubscriptions($state = 'active',$limit = 10,$start = 0);
    public function getAdjustments();
}
interface AdjustmentInterface {

}
interface CouponInterface {
}
interface RedemptionInterface {}
interface InvoiceInterface {

}
interface LineItemInterface {
}
interface PlanInterface {

}
interface SubscriptionInterface {
    public function cancel();
    public function terminate();
    public function reactivate();
}
interface TransactionInterface {

}
interface BillingInfoInterface {
}
interface StatsInterface {}
interface ProductInterface {}