<?php
namespace Paydirt\Chargify;

class Transaction extends Object implements \Paydirt\TransactionInterface {
    public static $uri = 'transactions';
    public static $rootNode = 'transaction';

    public static function getListUri($criteria = array()) {
        return 'subscriptions/'.$criteria['subscription_id'].'/transactions';
    }

    protected $_fieldMeta = array(
        'id'                      => 'int',
        'transaction_type'        => 'string',
        'amount_in_cents'         => 'int',
        'ending_balance_in_cents' => 'int',
        'memo'                    => 'string',
        'subscription_id'         => 'int',
        'product_id'              => 'int',
        'success'                 => 'boolean',
        'payment_id'              => 'int',
        'created_at'              => 'datetime',
    );
}