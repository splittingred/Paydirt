<?php
/*
 * Paydirt
 *
 * Copyright 2012 by Shaun McCormick
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 */
namespace Paydirt\Chargify;

class Statement extends Object implements \Paydirt\StatementInterface {
    public static $uri = 'statements';
    public static $rootNode = 'statement';

    public static function getListUri($criteria = array()) {
        return 'subscriptions/'.$criteria['subscription_id'].'/statements';
    }

    protected $_fieldMeta = array(
        'id'                          => 'int',
        'subscription_id'             => 'int',
        'opened_at'                   => 'datetime',
        'closed_at'                   => 'datetime',
        'settled_at'                  => 'datetime',
        'text_view'                   => 'string',
        'basic_html_view'             => 'string',
        'html_view'                   => 'string',
        'future_payments'             => 'array',
        'starting_balance_in_cents'   => 'int',
        'ending_balance_in_cents'     => 'int',

        'customer_first_name'         => 'string',
        'customer_last_name'          => 'string',
        'customer_shipping_address'   => 'string',
        'customer_shipping_address_2' => 'string',
        'customer_shipping_city'      => 'string',
        'customer_shipping_state'     => 'string',
        'customer_shipping_country'   => 'string',
        'customer_shipping_zip'       => 'string',
        'customer_billing_address'    => 'string',
        'customer_billing_address_2'  => 'string',
        'customer_billing_city'       => 'string',
        'customer_billing_state'      => 'string',
        'customer_billing_country'    => 'string',
        'customer_billing_zip'        => 'string',

        'transactions'                => 'array',
        'events'                      => 'array',
        'created_at'                  => 'datetime',
        'updated_at'                  => 'datetime',
    );

    public function toArray() {
        $array = parent::toArray();
        $array['starting_balance'] = (float)($array['starting_balance_in_cents'] / 100);
        $array['ending_balance'] = (float)($array['ending_balance_in_cents'] / 100);
        return $array;
    }
}