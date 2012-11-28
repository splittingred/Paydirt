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

class Coupon extends Object implements \Paydirt\CouponInterface {
    public static $uri = 'coupons';
    public static $rootNode = 'coupon';
    public static $getUri = 'coupons/find';

    protected $_fieldMeta = array(
        'name'                         => 'string',
        'code'                         => 'string',
        'description'                  => 'string',
        'percentage'                   => 'string',
        'recurring'                    => 'boolean',
        'product_family_id'            => 'int',

        'amount'                       => 'float',
        'allow_negative_balance'       => 'boolean',
        'coupon_duration_period_count' => 'int',
        'coupon_end_date'              => 'datetime',
    );
    public static function processGetUri($uri,$criteria) {
        return rtrim($uri,'/');
    }

    public function redeem($accountCode,$currency = 'USD') {
        $result = $this->client->put('coupons/'.$this->get('coupon_code').'/redeem',array(
            'account_code' => $accountCode,
            'currency' => $currency,
        ),array(
            'rootNode' => 'redemption',
        ));
        $response = $result->process();
        return !empty($response) && !empty($response['created_at']);
    }

    public function getSavings($planCode) {
        $savings = $this->driver->cacheManager->get('coupon/'.$this->get('code').'/savings/'.$planCode);
        if (empty($savings)) {
            /** @var \Paydirt\Chargify\Plan $plan */
            $plan = $this->driver->getObject('Product',$planCode);
            if (!$plan) return false;

            $savings = false;
            $result = $this->client->get('coupons/'.$this->get('id').'/usage',array(
                'coupon_id' => $this->get('id'),
            ));
            $response = $result->process();
            if (!empty($response)) {
                foreach ($response as $ar => $s) {
                    if ($plan->get('id') == $s['id']) {
                        $savings = $s;
                        break;
                    }
                }
            }
            if (!empty($savings)) {
                $this->driver->cacheManager->set('coupon/'.$this->get('code').'/savings/'.$planCode,$savings,3600);
            }
        }
        return $savings;
    }
}