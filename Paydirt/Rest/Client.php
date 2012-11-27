<?php
namespace Paydirt\Rest;
/**
 * REST Client service class with XML/JSON/QS support
 *
 * @package paydirt
 * @subpackage rest
 */
class Client {
    /** @var array $config */
    public $config = array();
    /** @var object $handle cURL resource handle. */
    public $handle;

    /** @var object $response Response body. */
    public $response;
    /** @var object $headers Parsed response header object */
    public $headers;
    /** @var object $info Response info object */
    public $info;
    /** @var string $error Response error string. */
    public $error;
    /** @var string $url The URL to query */
    public $url;

	public function __construct(array $config = array()) {
		$this->config = array_merge(array(
            'headers' => array(),
            'curl_options' => array(),
            'user_agent' => "PHP RestClient/0.1",
            'base_url' => NULL,
            'format' => NULL,
            'username' => NULL,
            'password' => NULL,
            'sendToken' => true,
            'setPostOptionIfPost' => true,
            'skipRecursiveParseXml' => false,
		),$config);
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
     * @param string $url
     * @param array $parameters
     * @param array $headers
     * @return ClientResponse
     */
    public function get($url, $parameters=array(), $headers=array()){
        return $this->execute($url, 'GET', $parameters, $headers);
    }

    /**
     * @param string $url
     * @param array $parameters
     * @param array $headers
     * @return ClientResponse
     */
    public function post($url, $parameters=array(), $headers=array()){
        return $this->execute($url, 'POST', $parameters, $headers);
    }

    /**
     * @param string $url
     * @param array $parameters
     * @param array $headers
     * @return ClientResponse
     */
    public function put($url, $parameters=array(), $headers=array()){
        if (!empty($this->config['addMethodParameter'])) {
            $parameters['_method'] = "PUT";
        }
        return $this->execute($url,'PUT',$parameters, $headers);
    }

    /**
     * @param string $url
     * @param array $parameters
     * @param array $headers
     * @return ClientResponse
     */
    public function delete($url, $parameters=array(), $headers=array()){
        if (!empty($this->config['addMethodParameter'])) {
            $parameters['_method'] = "DELETE";
        }
        return $this->execute($url,'DELETE', $parameters, $headers);
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $parameters
     * @param array $headers
     * @return ClientResponse
     */
    protected function execute($url, $method='GET', $parameters=array(), $headers=array()){
        if (isset($_REQUEST['token']) && !empty($this->config['sendToken'])) {
            $parameters['token'] = $_REQUEST['token'];
        } else { unset($parameters['token']); }

        if (isset($_REQUEST['api_key']) && !empty($this->config['sendToken'])) {
            $parameters['api_key'] = $_REQUEST['api_key'];
        } else { unset($parameters['api_key']); }

        $request = new ClientRequest($this->config);
        if (!empty($headers['rootNode'])) {
            $request->setRootNode($headers['rootNode']);
        }
        return $request->execute($url,$method,$parameters,$headers);
    }
}