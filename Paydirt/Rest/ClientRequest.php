<?php
namespace Paydirt\Rest;
/**
 * Request class for handling REST requests
 *
 * @package paydirt
 * @subpackage rest
 */
class ClientRequest {
    /** @var array $config */
    public $config = array();
    /** @var string $url */
    public $url;
    /** @var string $method */
    public $method = 'GET';
    /** @var mixed $handle */
    public $handle;
    /** @var array $requestParameters */
    public $requestParameters = array();
    /** @var array $requestOptions */
    public $requestOptions = array();
    /** @var array $headers */
    public $headers = array();
    /** @var array $defaultRequestParameters */
    public $defaultRequestParameters = array();
    /** @var string $rootNode */
    public $rootNode = 'request';

    function __construct(array $config = array()) {
        $this->config = array_merge($this->config,$config);
        if (!empty($this->config['headers'])) {
            $this->setHeaders($this->config['headers']);
        }
        if (!empty($this->config['defaultParameters'])) {
            $this->defaultRequestParameters = $this->config['defaultParameters'];
        }
        $this->_setDefaultRequestOptions();
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setOption($key, $value){
        $this->config[$key] = $value;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getOption($key,$default = null) {
        return array_key_exists($key,$this->config) ? $this->config[$key] : $default;
    }

    /**
     * Set the root node of the request. Only used for XML requests.
     * @param string $node
     */
    public function setRootNode($node) {
        $this->rootNode = $node;
    }

    /**
     * Set the request parameters for the request.
     * @param array $parameters
     */
    public function setRequestParameters(array $parameters) {
        $this->requestParameters = array_merge($this->defaultRequestParameters,$parameters);
    }

    /**
     * Set the HTTP headers on the request
     *
     * @param array $headers
     * @param bool $merge
     */
    public function setHeaders($headers = array(),$merge = false) {
        $this->headers = $merge ? array_merge($this->headers,$headers) : $headers;
    }

    /**
     * Execute the request, properly preparing it, setting the URL and sending the request via cURL
     *
     * @param string $path
     * @param string $method
     * @param array $parameters
     * @param array $headers
     * @return ClientResponse
     */
    public function execute($path,$method = 'GET', $parameters = array(), $headers = array()) {
        $this->url = $path;
        $this->method = strtoupper($method);
        $this->setRequestParameters($parameters);
        if (!empty($headers)) $this->setHeaders($headers,true);

        $this->prepare();
        return $this->send();
    }

    /**
     * Prepare the request for sending
     */
    protected function prepare() {
        $this->prepareHandle();
        $this->prepareAuthentication();
        $this->prepareUrl();
        $this->preparePayload();
        $this->prepareHeaders();
        $this->prepareRequestOptions();
    }

    /**
     * Send the request over the wire
     * @return ClientResponse
     */
    protected function send() {
        curl_setopt_array($this->handle,$this->requestOptions);
        $result = curl_exec($this->handle);
        $headerSize = curl_getinfo($this->handle,CURLINFO_HEADER_SIZE);
        $response = new ClientResponse($result,$headerSize,$this->config);
        $info = (object) curl_getinfo($this->handle,CURLINFO_HTTP_CODE);
        $response->setResponseInfo($info);
        $error = curl_error($this->handle);
        $response->setResponseError($error);
        curl_close($this->handle);
        return $response;
    }

    /**
     * Load the request handle
     * @return mixed
     */
    protected function prepareHandle() {
        $this->handle = curl_init();
        return $this->handle;
    }

    /**
     * Set any authentication options for this request
     */
    protected function prepareAuthentication() {
        $username = $this->getOption('username');
        $password = $this->getOption('password','');
        if (!empty($username)) {
            $this->requestOptions[CURLOPT_USERPWD] = $username.(!empty($password) ? ':'.$password : '');
        }
    }

    /**
     * Set any HTTP headers and load them into the request options
     */
    protected function prepareHeaders() {
        if (!empty($this->headers)) {
            $this->requestOptions[CURLOPT_HTTPHEADER] = array();
            foreach ($this->headers as $key => $value) {
                $this->requestOptions[CURLOPT_HTTPHEADER][] = sprintf("%s: %s", $key, $value);
            }
        }
    }

    /**
     * Prepare the URL, prefixing the base_url if set, and setting the format suffix, if wanted
     * @return mixed
     */
    protected function prepareUrl() {
        $format = $this->getOption('format','json');
        $suppressSuffix = $this->getOption('suppressSuffix',false);
        if (!empty($format) && !$suppressSuffix) {
            $this->url .= '.'.$format;
        }

        if ($this->method != 'POST' && count($this->requestParameters)){
            $this->url .= strpos($this->url, '?') ? '&' : '?';
            $this->url .= $this->_formatQuery($this->requestParameters);
        }

        $baseUrl = $this->getOption('base_url',false);
        if (!empty($baseUrl)) {
            if ((!empty($this->url) && $this->url[0] != '/') || substr($baseUrl, -1) != '/') {
                $this->url = '/' . $this->url;
            }
            $this->url = $this->url != '/' ? $baseUrl . $this->url : $baseUrl;
        }
        $this->requestOptions[CURLOPT_URL] = $this->url;
        return $this->url;
    }

    /**
     * Prepare the payload of parameters to be sent with the request
     */
    protected function preparePayload() {
        if ($this->method != 'GET') {
            $format = $this->getOption('format','json');
            switch ($format) {
                case 'json':
                    if (empty($this->requestOptions[CURLOPT_HTTPHEADER])) $this->requestOptions[CURLOPT_HTTPHEADER] = array();
                    $this->requestOptions[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json; charset=utf-8';
                    if (!empty($this->requestParameters)) {
                        $params = $this->requestParameters;
                        if (!empty($this->config['useRootNodeInJSON'])) {
                            $params = array($this->rootNode => $params);
                        }
                        $json = json_encode($params);
                        $this->requestOptions[CURLOPT_POSTFIELDS] = $json;
                    }
                    break;
                case 'nvp':
                    $this->requestOptions[CURLOPT_POSTFIELDS] = $this->requestParameters;
                    break;
                case 'xml':
                    if (empty($this->requestOptions[CURLOPT_HTTPHEADER])) $this->requestOptions[CURLOPT_HTTPHEADER] = array();
                    $this->requestOptions[CURLOPT_HTTPHEADER][] = 'Content-Type: application/xml; charset=utf-8';
                    if (!empty($this->requestParameters)) {
                        $xml = $this->toXml($this->requestParameters,$this->rootNode);
                        $this->requestOptions[CURLOPT_POSTFIELDS] = $xml;
                    }
                    break;
                default:
                    $this->requestOptions[CURLOPT_POSTFIELDS] = $this->_formatQuery($this->requestParameters);
                    break;
            }
        }

        if ($this->method == 'POST' && !empty($this->config['setPostOptionIfPost'])) {
            $this->requestOptions[CURLOPT_POST] = true;
        } elseif ($this->method != 'GET') {
            $this->requestOptions[CURLOPT_CUSTOMREQUEST] = $this->method;
        }
    }

    /**
     * Prepare the request options to be sent, setting them on the cURL handle
     */
    protected function prepareRequestOptions() {
        $curlOptions = $this->getOption('curl_options');
        if (!empty($curlOptions) && is_array($curlOptions)) {
            foreach ($curlOptions as $key => $value) {
                $this->requestOptions[$key] = $value;
            }
        }
    }

    /**
     * Setup the default request options
     */
    private function _setDefaultRequestOptions() {
        $this->requestOptions = array(
            CURLOPT_HEADER => $this->getOption('header',true),
            CURLOPT_RETURNTRANSFER => $this->getOption('return_transfer',true),
            CURLOPT_FOLLOWLOCATION => $this->getOption('follow_location',true),
            CURLOPT_TIMEOUT => $this->getOption('timeout',240),
            CURLOPT_USERAGENT => $this->getOption('user_agent'),
            CURLOPT_CONNECTTIMEOUT => $this->getOption('connect_timeout',0),
            CURLOPT_DNS_CACHE_TIMEOUT => $this->getOption('dns_cache_timeout',120),
            CURLOPT_VERBOSE => $this->getOption('verbose',false),
            CURLOPT_SSL_VERIFYHOST => $this->getOption('ssl_verifyhost',2),
            CURLOPT_SSL_VERIFYPEER => $this->getOption('ssl_verifypeer',false),
            CURLOPT_ENCODING => $this->getOption('encoding',''),
            CURLOPT_REFERER => $this->getOption('referer',''),
            CURLOPT_NETRC => $this->getOption('netrc',false),
            CURLOPT_HTTPPROXYTUNNEL => $this->getOption('http_proxy_tunnel',false),
            CURLOPT_FRESH_CONNECT => $this->getOption('fresh_connect',false),
            CURLOPT_FORBID_REUSE => $this->getOption('forbid_reuse',false),
            CURLOPT_CRLF => $this->getOption('crlf',false),
            CURLOPT_AUTOREFERER => $this->getOption('autoreferer',false),
            CURLOPT_MAXREDIRS => $this->getOption('max_redirs',3),
        );
        $port = $this->getOption('port',false);
        if ($port && intval($port) != 80) {
            $this->requestOptions[CURLOPT_PORT] = $port;
        }
        $certificateInfo = $this->getOption('cainfo',false);
        if (!empty($certificateInfo)) {
            $this->requestOptions[CURLOPT_CAINFO] = $certificateInfo;
        }

        $cookieFile = $this->getOption('cookie_file','');
        if (!empty($cookieFile)) {
            $this->requestOptions[CURLOPT_COOKIEFILE] = $cookieFile;
        }

        $cookie = $this->getOption('cookie','');
        if (!empty($cookie)) {
            $this->requestOptions[CURLOPT_COOKIE] = $cookie;
        }

        $proxy = $this->getOption('proxy',false);
        if (!empty($proxy)) {
            $this->requestOptions = array_merge($this->requestOptions,array(
                CURLOPT_PROXY => $proxy,
                CURLOPT_PROXYAUTH => $this->getOption('proxy_auth',CURLAUTH_BASIC),
                CURLOPT_PROXYPORT => $this->getOption('proxy_port',80),
                CURLOPT_PROXYTYPE => $this->getOption('proxy_type',CURLPROXY_HTTP),
            ));
            $username = $this->getOption('proxy_username');
            $password = $this->getOption('proxy_password','');
            if (!empty($username)) {
                $this->requestOptions[CURLOPT_PROXYUSERPWD] = $username.':'.$password;
            }
        }
    }

    /**
     * Format an array of parameters into a query string
     * @param array $parameters
     * @return string
     */
    private function _formatQuery(array $parameters){
        $query = http_build_query($parameters);
        return rtrim($query);
    }

    /**
     * @param array $parameters
     * @param string $rootNode
     * @return string
     */
    public function toXml($parameters,$rootNode) {
        $doc = new DOMDocument("1.0",'UTF-8');
        $root = $doc->appendChild($doc->createElement($rootNode));
        $this->_populateXmlDoc($doc, $root, $parameters);
        return $doc->saveXML();
    }

    /**
     * @param \DOMDocument $doc
     * @param \DOMNode $node
     * @param array $parameters
     */
    protected function _populateXmlDoc(&$doc, &$node, &$parameters) {
        /** @var $val \DOMNode */
        foreach ($parameters as $key => $val) {
            if ($val instanceof \Recurly_CurrencyList) {
                $val->populateXmlDoc($doc, $node, $parameters);
            } else if ($val instanceof \Recurly_Resource) {
                $attribute_node = $node->appendChild($doc->createElement($key));
                $this->_populateXmlDoc($doc, $attribute_node, $val);
            } else if (is_array($val)) {
                if (empty($val)) {
                    continue;
                }
                $attribute_node = $node->appendChild($doc->createElement($key));
                foreach ($val as $child => $childValue) {
                    if (is_null($child) || is_null($childValue)) {
                        continue;
                    } elseif (is_string($child) && !is_null($childValue)) {
                        // e.g. "<discount_in_cents><USD>1000</USD></discount_in_cents>"
                        $attribute_node->appendChild($doc->createElement($child, $childValue));
                    } elseif (is_int($child) && !is_null($childValue)) {
                        if (is_object($childValue)) {
                            // e.g. "<subscription_add_ons><subscription_add_on>...</subscription_add_on></subscription_add_ons>"
                            $this->_populateXmlDoc($doc, $attribute_node, $childValue);
                        } elseif (substr($key, -1) == "s") {
                            // e.g. "<plan_codes><plan_code>gold</plan_code><plan_code>monthly</plan_code></plan_codes>"
                            $attribute_node->appendChild($doc->createElement(substr($key, 0, -1), $childValue));
                        }
                    }
                }
            } elseif (is_object($val)) {
                $this->_populateXmlDoc($doc,$node,$val);
            } else {
                $node->appendChild($doc->createElement($key, $val));
            }
        }
    }
}