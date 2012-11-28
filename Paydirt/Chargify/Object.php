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

abstract class Object extends \Paydirt\Object implements \Paydirt\ObjectInterface {
    /** @var \Paydirt\Rest\Client $client */
    public $client;
    /** @var string $getUri */
    public static $getUri = '';
    /** @var string $listUri */
    public static $listUri = '';
    /** @var string $postUri */
    public static $postUri = '';
    /** @var string $putUri */
    public static $putUri = '';
    /** @var string $deleteUri */
    public static $deleteUri = '';

    /** @var array An array of errors */
    protected $errors = array();

    /**
     * Initialize the object, loading the client drivers.
     */
    public function initialize() {
        $this->getClient();
    }

    /**
     * Load the Client driver for this object
     */
    public function getClient() {
        $this->client = new \Paydirt\Rest\Client(array(
            'base_url' => 'https://'.$this->driver->config['domain'].'.chargify.com',
            'supressSuffix' => false,
            'format' => 'json',
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json',
                'User-Agent' => 'Paydirt/1.0.0; PHP ' . phpversion() . ' [' . php_uname('s') . ']',
            ),
            'follow_location' => true,
            'connect_timeout' => 10,
            'timeout' => 45,
            'sendToken' => false,
            'addMethodParameter' => false,
            'useRootNodeInJSON' => true,
            'username' => $this->driver->config['api_key'],
            'password' => 'x',
        ));
    }

    /**
     * @static
     * @param \Paydirt\Driver $driver
     * @param array $fields
     * @return \Paydirt\Chargify\Object
     */
    public static function newInstance(\Paydirt\Driver $driver,array $fields = array()) {
        /** @var \Paydirt\Chargify\Object $object */
        $object = new static($driver);
        $object->initialize();
        $object->fromArray($fields,'',true);
        $object->setNew(true);
        return $object;
    }
    /**
     * @static
     * @param \Paydirt\Driver $driver
     * @param array $criteria
     * @return bool|\Paydirt\Chargify\Object
     */
    public static function load(\Paydirt\Driver $driver,$criteria) {
        $uri = static::getGetUri($criteria);

        /** @var \Paydirt\Chargify\Object $object */
        $object = new static($driver);
        $object->initialize();
        $finalUri = static::processGetUri($uri,$criteria);
        $c = is_array($criteria) ? $criteria : array();
        $data = $object->client->get($finalUri,$c);
        $data = $data->process();
        if (!empty($data)) {
            /** @var array $data */
            $data = isset($data[static::$rootNode]) ? $data[static::$rootNode] : $data;
            $object->fromArray($data,'',true);
            $object->setDirty(false);
        } else {
            return false;
        }
        return $object;
    }
    public static function getGetUri($criteria = array()) {
        return !empty(static::$getUri) ? static::$getUri : static::$uri;
    }
    public static function processGetUri($uri,$criteria) {
        return rtrim($uri,'/').((is_string($criteria) || is_numeric($criteria)) && !empty($criteria) ? '/'.$criteria : '');
    }

    /**
     * @static
     * @param \Paydirt\Driver $driver
     * @param array $criteria
     * @return array
     */
    public static function loadCollection(\Paydirt\Driver $driver,$criteria = array()) {
        $uri = static::getListUri($criteria);

        $object = new static($driver);
        $object->initialize();
        $data = $object->client->get($uri,$criteria);
        $data = $data->process();
        $collection = array();

        if (empty($data)) return $collection;

        /* @todo consider using an Iterator class here instead of returning an array */
        foreach ($data as $record) {
            $object = new static($driver);
            $object->initialize();
            $object->fromArray($record[static::$rootNode],'',true);
            $object->setDirty(false);
            $collection[] = $object;
        }
        return $collection;
    }
    public static function getListUri($criteria = array()) {
        return !empty(static::$listUri) ? static::$listUri : static::$uri;
    }

    /**
     * Save the object
     * @return bool
     */
    public function save() {
        $dataArray = $this->toArray();
        $objectArray = array();
        foreach ($dataArray as $key => $value) {
            $objectArray[$key] = $value;
        }
        if ($this->isNew()) {
            $uri = static::getPostUri($objectArray);
            $result = $this->client->post($uri,$objectArray,array(
                'rootNode' => static::$rootNode,
            ));
            $response = $result->process();
            if (empty($response)) {
                if (is_string($result->responseBody) && !empty($result->responseBody)) {
                    $this->addFieldError(static::$primaryKeyField,$result->responseBody);
                }
                return false;
            }
            if (!empty($response['errors'])) {
                $this->_handleErrors($response);
                return false;
            }
            $this->afterSave($response);

            $this->setNew(false);
        } else {
            unset($objectArray[static::$primaryKeyField]);
            foreach ($this->_readOnlyAttributes as $k) {
                unset($objectArray[$k]);
            }
            $uri = static::getPutUri($objectArray).'/'.$this->get(static::$primaryKeyField);
            $result = $this->client->put($uri,$objectArray,array(
                'rootNode' => static::$rootNode,
            ));
            $response = $result->process();
            if (empty($response)) return false;
            if (!empty($response['errors'])) {
                $this->_handleErrors($response);
                return false;
            }
            $this->afterSave($response);
        }
        $this->setDirty(false);
        return true;
    }

    public static function getPostUri($criteria = array()) {
        return !empty(static::$postUri) ? static::$postUri : static::$uri;
    }

    public static function getPutUri($criteria = array()) {
        return !empty(static::$putUri) ? static::$putUri : static::$uri;
    }

    public function afterSave(array $response = array()) {
        $this->driver->log(Driver::LOG_LEVEL_INFO,'[Paydirt] AfterSave with data '.print_r($response,true));
        if (isset($response[static::$rootNode])) {
            $this->fromArray($response[static::$rootNode],'',$this->isNew());
        } else {
            $this->fromArray($response,'',$this->isNew());
        }
    }

    public function remove() {
        $uri = $this->getRemoveUri();
        $result = $this->client->delete($uri);
        $response = $result->process();
        $this->driver->log(Driver::LOG_LEVEL_INFO,'[Paydirt] DELETE to '.$this->getRemoveUri().' : '.print_r($result->responseBody,true));
        if (empty($response)) return false;
        if (!empty($response['error'])) {
            $this->_handleErrors($response);
            return false;
        }
        return true;
    }

    protected function getRemoveUri($criteria = array()) {
        return rtrim(static::getDeleteUri($criteria),'/').'/'.$this->get(static::$primaryKeyField);
    }
    public static function getDeleteUri($criteria = array()) {
        return !empty(static::$deleteUri) ? static::$deleteUri : static::$uri;
    }

    protected function _handleErrors($response) {
        foreach ($response['errors'] as $idx => $error) {
            $this->addError((string)$error);
        }
    }

    public function isOK($result) {
         return !empty($result->responseInfo) && !empty($result->responseInfo->http_code) && $result->responseInfo->http_code != 200;
    }
}