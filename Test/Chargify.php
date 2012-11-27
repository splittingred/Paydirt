<?php
/**
 * @package paydirt
 */
require_once dirname(dirname(__FILE__)).'/config.inc.php';
$config = array(
    'api_key' => CHARGIFY_API_KEY,
    'domain' => CHARGIFY_DOMAIN,
);
require_once dirname(dirname(__FILE__)).'/Paydirt/Driver.php';
$driver = \Paydirt\Driver::getInstance('Chargify',$config);

if ($driver instanceof \Paydirt\Chargify\Driver) {
    $plan = $driver->getObject('Plan','truck');
    if (!empty($plan)) {
        print_r($plan->toArray());
    }
}
