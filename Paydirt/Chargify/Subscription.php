<?php
namespace Paydirt\Chargify;
class Subscription extends Object implements \Paydirt\SubscriptionInterface {
    public static $uri = 'subscriptions';
    public static $rootNode = 'subscription';
    public static $primaryKeyField = 'id';

    protected $_fieldMeta = array(
        'id'                        => 'string',
        'state'                     => 'string',
        'balance_in_cents'          => 'int',
        'balance'                   => 'float',
        'current_period_started_at' => 'datetime',
        'current_period_ends_at'    => 'datetime',
        'trial_started_at'          => 'datetime',
        'trial_ended_at'            => 'datetime',
        'activated_at'              => 'datetime',
        'expires_at'                => 'datetime',
        'created_at'                => 'datetime',
        'updated_at'                => 'datetime',

        'customer'                  => 'array',
        'product'                   => 'array',
        'credit_card'               => 'array',

        'cancellation_message'      => 'string',
        'cancelled_at'              => 'datetime',
        'signup_revenue'            => 'float',
        'signup_payment_id'         => 'int',
        'cancel_at_end_of_period'   => 'boolean',
        'delayed_cancel_at'         => 'datetime',
        'previous_state'            => 'string',
        'coupon_code'               => 'string',

        /* POST/PUT only */
        'product_id'                => 'int',
        'product_handle'            => 'string',
        'product_price_in_cents'    => 'int',
        'product_price'             => 'float',
        'payment_profile_id'        => 'int',
        'payment_profile_attributes'=> 'array',
        'customer_id'               => 'int',
        'customer_reference'        => 'string',
        'customer_attributes'       => 'array',
        'next_billing_at'           => 'datetime',
        'vat_number'                => 'string',
        'credit_card_attributes'    => 'array',
        'components'                => 'array',
    );


    protected $_readOnlyAttributes = array(
        'id',
        'state','previous_state','balance_in_cents','balance',
        'current_period_started_at','current_period_ends_at','trial_started_at','trial_ended_at',
        'activated_at','expires_at','created_at','updated_at','cancelled_at','delayed_cancel_at',
        'cancel_at_end_of_period',
        'signup_revenue','signup_payment_id',
        'product','credit_card','customer',
        'product_price',
    );

    public function cancel() {
        return $this->remove();
    }

    public function remove() {
        $result = $this->client->put('subscriptions/'.$this->get('id'),array(
            'cancel_at_end_of_period' => 1,
            'cancellation_message' => 'Expiring at EOM',
        ),array(
            'rootNode' => 'subscription',
        ));
        $response = $result->process();
        if (empty($response)) {
            $this->driver->log(Driver::LOG_LEVEL_ERROR,'[Paydirt] Could not expire Subscription in Chargify: '.print_r($result->responseBody,true));
            return false;
        }
        return true;
    }

    public function unexpire() {
        $result = $this->client->put('subscriptions/'.$this->get('id'),array(
            'cancel_at_end_of_period' => 0,
            'cancellation_message' => '',
        ),array(
            'rootNode' => 'subscription',
        ));
        $response = $result->process();
        if (empty($response)) {
            $this->driver->log(Driver::LOG_LEVEL_ERROR,'[Paydirt] Could not unexpire Subscription in Chargify: '.print_r($result->responseBody,true));
            return false;
        }
        return true;
    }

    public function terminate() {
        $result = $this->client->delete('subscriptions/'.$this->get('id'),array(),array(
            'rootNode' => 'subscription',
            'cancellation_message' => 'Terminated immediately.',
        ));
        $response = $result->process();
        if (empty($response)) {
            $this->driver->log(Driver::LOG_LEVEL_ERROR,'[Paydirt] Could not terminate Subscription in Chargify: '.print_r($result->responseBody,true));
            return false;
        }
        return true;
    }

    public function reactivate() {
        $result = $this->client->put('subscriptions/'.$this->get('id').'/reactivate',array(),array(
            'rootNode' => 'subscription',
        ));
        $response = $result->process();
        if (empty($response)) return false;
        return true;
    }

    public function toArray() {
        $array = parent::toArray();
        if (!empty($array['balance_in_cents'])) {
            $array['balance'] = (float)($array['balance_in_cents'] / 100);
        } else {
            $array['balance_in_cents'] = 0;
            $array['balance'] = 0;
        }
        if (isset($array['product']['price_in_cents'])) {
            $array['product_price'] = (float)($array['product']['price_in_cents'] / 100);
        }
        return $array;
    }

    /**
     * Updates the allotted quantity of a component on a subscription
     * @param int $componentId
     * @param int $quantity
     * @return bool
     */
    public function updateComponentQuantity($componentId,$quantity) {
        $result = $this->client->put('subscriptions/'.$this->get('id').'/components/'.$componentId,array(
            'allocated_quantity' => intval($quantity),
        ),array(
            'rootNode' => 'component',
        ));
        $response = $result->process();
        if (empty($response)) {
            $this->driver->log(Driver::LOG_LEVEL_ERROR,'[Paydirt] Failed to update component quantity. Error: '.print_r($result->responseBody,true));
            return false;
        }
        return true;
    }

    /**
     * Migrate this Subscription to a different Plan
     *
     * @param string $planCode
     * @param array $properties
     * @return bool
     */
    public function migrateTo($planCode,array $properties = array()) {
        $this->client->put('subscriptions/'.$this->get('id'),$properties,array(
            'rootNode' => 'subscription',
        ));

        $result = $this->client->post('subscriptions/'.$this->get('id').'/migrations',array(
            'product_handle' => $planCode,
        ),array(
            'rootNode' => 'migration',
        ));
        $response = $result->process();
        if (empty($response)) {
            $this->driver->log(Driver::LOG_LEVEL_ERROR,'[Paydirt] Failed to migrate subscription. Error: '.print_r($result->responseBody,true));
            return false;
        }
        return true;
    }

    /**
     * @return boolean
     */
    public function handleErrors() {
        $errors = $this->getErrors();
        foreach ($errors as $idx => $error) {
            $key = $idx;
            if (is_int($idx)) {
                $value = strtolower(trim(substr($error,0,strpos($error,':')),':'));
                switch ($value) {
                    case 'credit card':
                        $key = 'cc_expiration_year';
                        break;
                    case 'billing first name':
                        $key = 'billing_first_name';
                        break;
                    case 'billing last name':
                        $key = 'billing_last_name';
                        break;
                    case 'billing address':
                        $key = 'billing_address';
                        break;
                    case 'billing city':
                        $key = 'billing_city';
                        break;
                    case 'billing state':
                        $key = 'billing_state';
                        break;
                    case 'billing country':
                        $key = 'billing_country';
                        break;
                    case 'billing zip code':
                        $key = 'billing_zip';
                        break;
                    case 'coupon code':
                        $key = 'coupon_code';
                        break;
                    case 'credit card number':
                    default:
                        $key = 'cc_number';
                        break;
                }
            }
            $this->addFieldError($key,$error);
        }
        return !empty($errors);
    }

    public function refund($transactionId,$amount,$memo = 'Refund') {
        $memo = empty($memo) ? 'Refund' : $memo;
        $uri = 'subscriptions/'.$this->get('id').'/refunds';
        $data = array(
            'payment_id' => $transactionId,
            'amount_in_cents' => $amount*100,
            'memo' => $memo,
        );
        $result = $this->client->post($uri,$data,array(
            'rootNode' => 'refund',
        ));
        $response = $result->process();
        if (empty($response)) {
            $this->driver->log(Driver::LOG_LEVEL_ERROR,'[Paydirt] Failed to refund subscription. Error: '.print_r($result->responseBody,true));
            return false;
        }
        return true;
    }
}