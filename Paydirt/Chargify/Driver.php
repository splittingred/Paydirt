<?php
namespace Paydirt\Chargify;
/**
 * @package paydirt
 * @subpackage chargify
 */
class Driver extends \Paydirt\Driver {
    /** @var string $driverName */
    public $driverName = 'Chargify';
    /** @var array $config */
    public $config = array(
        'apiKey' => CHARGIFY_API_KEY,
    );

    /**
     * Initialize the Chargify driver
     */
    public function initialize() {
    }
}