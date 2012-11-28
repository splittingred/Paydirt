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
namespace Paydirt;

interface ObjectInterface {
    public static function newInstance(\Paydirt\Driver $driver,array $fields = array());
    public static function load(\Paydirt\Driver $driver,$criteria);
    public static function loadCollection(\Paydirt\Driver $driver,$criteria = array());
}

/**
 * @package chargify
 * @subpackage payment
 */
abstract class Object implements ObjectInterface {
    /** @var Driver $driver */
    public $driver;
    /** @var array $fields */
    public $fields = array();
    /** @var boolean $_new */
    protected $_new = false;
    /** @var boolean $_dirty */
    protected $_dirty = false;
    /** @var array $_fieldMeta */
    protected $_fieldMeta = array();
    protected $_readOnlyAttributes = array();
    public static $uri = '';
    public static $primaryKeyField = 'id';
    public static $rootNode = '';

    /** @var array An array of errors */
    protected $errors = array();

    function __construct(\Paydirt\Driver &$driver) {
        $this->driver =& $driver;
    }

    public static function newInstance(\Paydirt\Driver $driver,array $fields = array()) {}
    public static function load(\Paydirt\Driver $driver,$criteria) {}
    public static function loadCollection(\Paydirt\Driver $driver,$criteria = array()) {}

    public function getOption($key, $options = null, $default = null, $skipEmpty = false) {
        return $this->driver->getOption($key,$options,$default,$skipEmpty);
    }

    /**
     * @param array $fields
     * @param string $keyPrefix
     * @param boolean $setPrimaryKeys
     */
    public function fromArray(array $fields,$keyPrefix= '',$setPrimaryKeys= false) {
        foreach ($fields as $key => $val) {
            if (!empty ($keyPrefix)) {
                $prefixPos= strpos($key, $keyPrefix);
                if ($prefixPos === 0) {
                    $key= substr($key, strlen($keyPrefix));
                } else {
                    continue;
                }
                $this->driver->log(LOG_LEVEL_DEBUG, "Stripped prefix {$keyPrefix} to produce key {$key}");
            }
            if ($key != static::$primaryKeyField || $setPrimaryKeys) {
                $this->fields[$key] = $val;
            }
        }
        $this->setDirty(true);
    }

    /**
     * @return array
     */
    public function toArray() {
        $array = $this->fields;
        /* allow for returning of editable values only */
        if (!empty($this->_fieldMeta)) {
            $diffArray = array();
            foreach ($this->_fieldMeta as $key => $type) {
                if (is_int($key)) $key = $type; /* handle keys with no type */

                if (array_key_exists($key,$this->fields)) {
                    $diffArray[$key] = $this->get($key);
                }
            }
            $array = $diffArray;
        }
        return $array;
    }

    /**
     * @return boolean
     */
    public function isNew() {
        return $this->_new;
    }
    /**
     * @param boolean $status
     */
    public function setNew($status) {
        $this->_new = (boolean)$status;
    }
    /**
     * @return array
     */
    public function __toString() {
        return $this->toArray();
    }
    /**
     * @param string $k
     * @param mixed $default
     * @return mixed
     */
    public function get($k,$default = null) {
        $v = array_key_exists($k,$this->fields) ? $this->fields[$k] : $default;
        $v = $this->_format($k,$v);
        return $v;
    }

    protected function _format($k,$v) {
        /* handle Recurly's wierd handling of null values */
        if (isset($this->fields[$k]) && is_array($this->fields[$k]) && !empty($this->fields[$k]['nil'])) {
            $v = null;
        }
        if (array_key_exists($k,$this->_fieldMeta)) {
            switch ($this->_fieldMeta[$k]) {
                case 'int':
                case 'integer':
                    $v = intval($v);
                    break;
                case 'float': $v = (float)$v; break;
                case 'string': $v = (string)$v; break;
                case 'boolean':
                    $v = is_string($v)
                        ? ($v == 'false' ? false : true)
                        : (boolean)$v;
                    break;
                case 'datetime':
                    $ts= false;
                    if (is_string($v) && !empty($v)) {
                        $ts= strtotime($v);
                    }
                    if ($ts !== false) {
                        $v = strftime(Driver::MYSQL_TIMESTAMP_FORMAT, $ts);
                    }
                    break;
                case 'date':
                    $ts= false;
                    if (is_string($v) && !empty($v)) {
                        $ts= strtotime($v);
                    }
                    if ($ts !== false) {
                        $v = strftime('%Y-%m-%d', $ts);
                    }
                    break;
                case 'json':
                    $js = json_decode($v,true);
                    if (is_array($js)) {
                        $v = $js;
                    }
                    break;
            }
        }
        return $v;
    }
    /**
     * @param string $k
     * @param mixed $v
     */
    public function set($k,$v) {
        $v = $this->_format($k,$v);
        $this->fields[$k] = $v;
        $this->setDirty(true);
    }
    /**
     * @param boolean $status
     */
    public function setDirty($status) {
        $this->_dirty = (boolean)$status;
    }

    /**
     * @return boolean
     */
    public function isDirty() {
        return $this->_dirty;
    }

    /**
     * @return string
     */
    public function toJSON() {
        $json= '';
        $array= $this->toArray();
        if ($array) {
            $json= json_encode($array);
        }
        return $json;
    }

    /**
     * See if the object has any errors
     * @return boolean
     */
    public function hasErrors() {
        return !empty($this->errors);
    }

    /**
     * Add a validation error for a specific field
     * @param string $key
     * @param string $message
     */
    public function addFieldError($key,$message) {
        $this->errors[$key] = $message;
    }

    /**
     * Get a field-specific error message
     *
     * @param string $key
     * @return mixed
     */
    public function getFieldError($key) {
        return $this->errors[$key];
    }

    /**
     * Get all the current errors
     *
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Clear all errors
     */
    public function clearErrors() {
        $this->errors = array();
    }
}