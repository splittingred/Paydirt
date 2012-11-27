<?php
namespace Paydirt\Chargify;

class Stats extends \Paydirt\Chargify\Object implements \Paydirt\StatsInterface {
    public static $uri = 'stats';
    public static $rootNode = 'stats';

    protected $_fieldMeta = array(
        'revenue_this_month'    => 'string',
        'total_subscriptions'   => 'int',
        'subscriptions_today'   => 'int',
        'revenue_today'         => 'string',
        'total_revenue'         => 'string',
        'revenue_this_year'     => 'string',
    );
}