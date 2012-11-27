<?php
namespace Paydirt\Chargify;

class Account extends Object implements \Paydirt\AccountInterface {
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

    public function getTransactions() {
        $data = $this->client->get('accounts/'.$this->get('account_code').'/transactions');
        $data = $data->process();

        $list = array();
        foreach ($data['transaction'] as $transactionArray) {
            /** @var \Paydirt\TransactionInterface|\Paydirt\Chargify\Transaction $transaction */
            $transaction = $this->driver->newObject('Transaction');
            $transaction->fromArray($transactionArray);
            $list[] = $transaction;
        }
        return $list;
    }

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

    public function getAdjustments() {
        $data = $this->client->get('accounts/'.$this->get('account_code').'/adjustments');
        $data = $data->process();

        $list = array();
        foreach ($data['adjustment'] as $adjustmentArray) {
            $adjustment = $this->driver->newObject('Adjustment');
            $adjustment->fromArray($adjustmentArray,'',true);
            $list[] = $adjustment;
        }
        return $list;
    }


    public function close() {
        return $this->remove();
    }

    public function open() {
        $result = $this->client->put('accounts/'.$this->get('account_code').'/reopen');
        $response = $result->process();
        if (empty($response)) {
            $this->driver->log(Driver::LOG_LEVEL_ERROR,'[Paydirt] Could not reopen account: '.print_r($this->toArray(),true));
            return false;
        }
        return true;
    }
}