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

class Customer extends Object implements \Paydirt\AccountInterface {
    public static $uri = 'customers';
    public static $rootNode = 'customer';
    public static $primaryKeyField = 'id';

    protected $_fieldMeta = array(
        'id'                 => 'int',
        'first_name'         => 'string',
        'last_name'          => 'string',
        'email'              => 'string',
        'address'            => 'string',
        'address_2'          => 'string',
        'city'               => 'string',
        'state'              => 'string',
        'zip'                => 'string',
        'country'            => 'string',
        'organization'       => 'string',
        'reference'          => 'string',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
        'payment_description'=> 'string',
    );
    protected $_readOnlyAttributes = array('created_at','updated_at','id','payment_description');

    public function getSubscriptions($state = 'active',$limit = 10,$start = 0) {
        $page = round($start / $limit)+1;
        $data = $this->client->get('customers/'.$this->get('id').'/subscriptions',array(
            'state' => $state,
            'per_page' => $limit,
            'page' => $page,
        ));
        $data = $data->process();

        $list = array();
        foreach ($data as $record) {
            $subscriptionArray = $record['subscription'];
            $subscription = $this->driver->newObject('Subscription');
            $subscription->fromArray($subscriptionArray,'',true);
            $list[] = $subscription;
        }
        return $list;
    }


    public function close() {
        return $this->remove();
    }

    public function open() {
        return true;
    }
}