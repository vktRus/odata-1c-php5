<?php

namespace OData\Client;

use ArrayAccess;
use Exception;
use GuzzleHttp\Client;

class OdataConnection implements ArrayAccess
{
    /**
     * @var array
     */
    private $container = [];

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $options;

    private function endsWith($haystack, $needle) {
        $length = strlen($needle);
        return $length > 0 ? substr($haystack, -$length) === $needle : true;
    }

    /**
     * @param string $url
     * @param array|null $options
     */
    public function __construct($url, $options = [])
    {
        $this->client = new Client();

        if (!empty($url) && !self::endsWith($url, '/')) {
            $url .= '/';
        }
        $this->url = $url;
        $this->options = array_merge_recursive(
            $options,
            [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'timeout' => 300
            ]
        );
    }

    /**
     * Параметры авторизации интерфейса OData
     *
     * @param string $username
     * @param string $password
     */
    public function setAuth($username, $password)
    {
        $this->options = array_merge_recursive(
            $this->options,
            [
                'auth' => [
                    $username,
                    $password
                ]
            ]
        );
    }

    /**
     * Параметры использования прокси сервиса
     *
     * @param string $proxyHost
     * @param string $proxyPort
     * @param bool|null $isSecured
     */
    public function setProxy($proxyHost, $proxyPort, $isSecured = false)
    {
        $this->options = array_merge_recursive(
            $this->options,
            [
                'proxy' => sprintf(
                    ($isSecured ? 'https' : 'http') . '://%s:%s',
                    $proxyHost,
                    $proxyPort
                )
            ]
        );
    }

    /**
     * Переопределение таймаута интерфейса OData
     *
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->options = array_merge_recursive(
            $this->options,
            [
                'timeout' => $timeout
            ]
        );
    }

    public function __get($name)
    {
        if (in_array($name, ['client', 'url', 'options'])) {
            return $this->{$name};
        } elseif (isset($this->container[$name])) {
            unset($this->container[$name]);
        }

        $this->container[$name] = new OdataContainer($this, $name);

        return $this->container[$name];
    }

    /**
     * @param $offset
     * @param $value
     * @throws Exception
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * @param $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * @param $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
}