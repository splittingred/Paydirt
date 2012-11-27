<?php
namespace Paydirt\Chargify;

class Product extends \Paydirt\Chargify\Object implements \Paydirt\ProductInterface {
    public static $uri = 'products';
    public static $getUri = 'products/handle/';
    public static $rootNode = 'product';
    public static $primaryKeyField = 'id';

    protected $_fieldMeta = array(
        'id'                          => 'int',
        'price_in_cents'              => 'int',
        'name'                        => 'string',
        'handle'                      => 'string',
        'description'                 => 'string',
        'product_family'              => 'array',
        'interval'                    => 'int',
        'interval_unit'               => 'string',

        'accounting_code'             => 'string',
        'initial_charge_in_cents'     => 'string',
        'trial_price_in_cents'        => 'array',
        'trial_interval'              => 'int',
        'trial_interval_unit'         => 'string',

        'expiration_interval'         => 'int',
        'expiration_interval_unit'    => 'string',
        'return_url'                  => 'string',
        'return_params'               => 'string',
        'require_credit_card'         => 'boolean',
        'request_credit_card'         => 'boolean',
        'require_billing_address'     => 'boolean',
        'request_billing_address'     => 'boolean',
        'taxable'                     => 'boolean',

        'created_at'                  => 'datetime',
        'updated_at'                  => 'datetime',
        'archived_at'                 => 'datetime',

        /* POST/PUT ONLY */
        'product_family_id'           => 'int',
    );

    protected $_readOnlyAttributes = array(
        'id',
        'created_at','updated_at',
        'price','initial_charge',
    );

    public static function getPostUri($criteria = array()) {
        return 'product_families/'.CHARGIFY_CLOUD_PRODUCT_FAMILY_ID.'/products';
    }

    public function save() {
        if ($this->isNew()) {
            //$this->set('product_family_id',CHARGIFY_CLOUD_PRODUCT_FAMILY_ID);
        }
        return parent::save();
    }

    public function toArray() {
        $array = parent::toArray();
        $array['price'] = (float)($array['price_in_cents'] / 100);
        $array['initial_charge'] = !empty($array['initial_charge_in_cents']) ? (float)($array['initial_charge_in_cents'] / 100) : 0;
        return $array;
    }
}