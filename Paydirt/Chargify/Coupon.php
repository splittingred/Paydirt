<?php
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