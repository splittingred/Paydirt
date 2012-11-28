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

class Plan extends Object implements \Paydirt\PlanInterface {
    public static $uri = 'products';
    public static $rootNode = 'product';
    public static $getUri = 'products/handle';

    protected $_fieldMeta = array(
        'id'                       => 'int',
        'price_in_cents'           => 'int',
        'name'                     => 'string',
        'handle'                   => 'string',
        'description'              => 'string',
        'product_family'           => 'array',
        'accounting_code'          => 'string',
        'interval_unit'            => 'string',
        'interval'                 => 'int',
        'initial_charge_in_cents'  => 'int',
        'trial_price_in_cents'     => 'int',
        'trial_interval'           => 'int',
        'trial_interval_unit'      => 'string',
        'expiration_interval'      => 'int',
        'expiration_interval_unit' => 'string',
        'return_url'               => 'string',
        'return_params'            => 'string',
        'require_credit_card'      => 'boolean',
        'request_credit_card'      => 'boolean',
        'created_at'               => 'datetime',
        'updated_at'               => 'datetime',
        'archived_at'              => 'datetime',

        'price_in_dollars'  => 'float',
        'initial_charge_in_dollars'  => 'float',
        'trial_price_in_dollars'  => 'float',
    );

    public $_readOnlyAttributes = array(
        'id','created_at','updated_at',
    );

    public function toArray() {
        $array = parent::toArray();

        if (isset($array['price_in_cents'])) {
            $array['price_in_dollars'] = (float)($array['price_in_cents'] / 100);
            $array['initial_charge_in_dollars'] = (float)($array['initial_charge_in_cents'] / 100);
            $array['trial_price_in_dollars'] = (float)($array['trial_price_in_cents'] / 100);
        } else {
            $array['price_in_cents'] = 0;
            $array['price_in_dollars'] = 0;
            $array['initial_charge_in_cents'] = 0;
            $array['initial_charge_in_dollars'] = 0;
            $array['trial_price_in_cents'] = 0;
            $array['trial_price_in_dollars'] = 0;
        }
        return $array;
    }
}