<?php
namespace Test\Paydirt;

class Start extends \Test\TestCase {
	public function test_getInstance() {
	    $driver = \Paydirt\Driver::getInstance('Chargify',$this->getConfig());
        $this->assertInstanceOf('\Paydirt\Chargify\Driver',$driver);
	}

}