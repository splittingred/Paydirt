<?php
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

ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);
$dir = realpath(dirname(__FILE__));

