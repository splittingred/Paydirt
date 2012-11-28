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
namespace Paydirt\Rest;
/**
 * Response class for REST requests
 *
 * @package paydirt
 * @subpackage rest
 */
class ClientResponse {
    /** @var array $config */
    public $config = array();
    /** @var string $response */
    public $response;
    /** @var int $headerSize */
    public $headerSize = 0;
    /** @var string $responseBody */
    public $responseBody;
    /** @var string $responseInfo */
    public $responseInfo;
    /** @var string $responseError */
    public $responseError;
    /** @var mixed $responseHeaders */
    public $responseHeaders;

    function __construct($response = '',$headerSize = 0,array $config = array()) {
        $this->config = array_merge($this->config,$config);
        $this->response = $response;
        $this->headerSize = $headerSize;
        $this->setResponseBody($response);
    }

    /**
     * Set and parse the response body
     * @param string $result
     */
    public function setResponseBody($result) {
        $this->responseBody = $this->_parse($result);
    }

    /**
     * Set the response info
     * @param string $info
     */
    public function setResponseInfo($info) {
        $this->responseInfo = $info;
    }

    /**
     * Set the response error, if any
     * @param string $error
     */
    public function setResponseError($error) {
        $this->responseError = $error;
    }

    public function getFormat() {
        return !empty($this->config['response_format']) ? $this->config['response_format'] : $this->config['format'];
    }

    /**
     * Return the processed result based on the format the response was returned in
     * @return array
     */
    public function process() {
        switch ($this->getFormat()) {
            case 'xml':
                $result = $this->fromXML($this->responseBody);
                break;
            case 'json':
            default:
                $result = json_decode($this->responseBody,true);
                break;
        }
        return !empty($result) ? $result : array();
    }

    /**
     * Parse the result
     * @param string $result
     * @return string
     */
    public function _parse($result) {
        $headers = array();
        $httpVer = strtok($result, "\n");

        while($line = strtok("\n")){
            if(strlen(trim($line)) == 0) break;

            list($key, $value) = explode(':', $line, 2);
            $key = trim(strtolower(str_replace('-', '_', $key)));
            $value = trim($value);
            if(empty($headers[$key])){
                $headers[$key] = $value;
            }
            elseif(is_array($headers[$key])){
                $headers[$key][] = $value;
            }
            else {
                $headers[$key] = array($headers[$key], $value);
            }
        }

        $this->responseHeaders = (object) $headers;
        return substr($result,$this->headerSize);
    }

    /**
     * Convert XML into a string
     *
     * @param string|\SimpleXMLElement $xml
     * @param mixed $attributesKey
     * @param mixed $childrenKey
     * @param mixed $valueKey
     * @return array
     */
    protected function fromXML($xml,$attributesKey=null,$childrenKey=null,$valueKey=null){
        if (is_string($xml)) {
            $xml = simplexml_load_string($xml,null,LIBXML_NOCDATA);
        }
        if (empty($xml)) return '';
        if($childrenKey && !is_string($childrenKey)){$childrenKey = '@children';}
        if($attributesKey && !is_string($attributesKey)){$attributesKey = '@attributes';}
        if($valueKey && !is_string($valueKey)){$valueKey = '@values';}

        $return = array();
        $name = $xml->getName();
        $_value = trim((string)$xml);
        if(!strlen($_value)){$_value = null;};

        if($_value!==null){
            if($valueKey){$return[$valueKey] = $_value;}
            else{$return = $_value;}
        }

        $children = array();
        $first = true;
        /** @var \SimpleXMLElement $child */
        foreach($xml->children() as $elementName => $child){
            if (!empty($this->config['skipRecursiveParseXml'])) {
                $value = (string)$child;
            } else {
                $value = $this->fromXML($child,$attributesKey, $childrenKey,$valueKey);
            }
            if(isset($children[$elementName])){
                if(is_array($children[$elementName])){
                    if($first){
                        $temp = $children[$elementName];
                        unset($children[$elementName]);
                        $children[$elementName][] = $temp;
                        $first=false;
                    }
                    $children[$elementName][] = $value;
                }else{
                    $children[$elementName] = array($children[$elementName],$value);
                }
            }
            else{
                $children[$elementName] = $value;
            }
        }
        if($children){
            if($childrenKey){$return[$childrenKey] = $children;}
            else{$return = array_merge($return,$children);}
        }

        $attributes = array();
        foreach($xml->attributes() as $name=>$value){
            $attributes[$name] = trim($value);
        }
        if($attributes){
            if($attributesKey){$return[$attributesKey] = $attributes;}
            else if(is_array($attributes) && is_array($return)) {
                $return = array_merge($return, $attributes);
            }
        }

        return $return;
    }
}