<?php
namespace Test;

class TestCase extends \PHPUnit_Framework_TestCase {
    public function getConfig() {
        require_once dirname(dirname(__FILE__)).'/config.inc.php';

        return array(
            'api_key' => CHARGIFY_API_KEY,
            'domain' => CHARGIFY_DOMAIN,
        );
    }

}