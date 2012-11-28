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