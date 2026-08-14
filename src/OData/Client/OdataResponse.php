<?php

namespace OData\Client;

//use GuzzleHttp\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface;

class OdataResponse
{
    /**
     * @var ResponseInterface|null
     */
    private $response;

    /**
     * @var array|null
     */
    private $array;

    public function __construct() {
    }

    public function make(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function toArray()
    {
        if (empty($this->array)) {
            $this->array = json_decode($this->response->getBody(), true);
        }
        return $this->array;
    }

    public function getResponseCode()
    {
        return $this->response->getStatusCode();
    }

    public function getResponsePhrase()
    {
        return $this->response->getReasonPhrase();
    }

    public function getOdataErrorCode()
    {
        $body = $this->toArray();

        if (isset($body['odata.error']['code'])) {
            return $body['odata.error']['code'];
        }

        return null;
    }

    public function getOdataErrorPhrase()
    {
        $body = $this->toArray();

        if (isset($body['odata.error']['message']['value'])) {
            return $body['odata.error']['message']['value'];
        }

        return null;
    }

    public function getLastId()
    {
        if ($this->response->hasHeader('Location')) {
            $matches = [];
            preg_match(
                '/guid\'(.*?)\'/',
                implode(
                    ' ',
                    $this->response->getHeader('Location')
                ), $matches
            );
            if ($matches) {
                /** @noinspection PhpArrayIsAlwaysEmptyInspection */
                return $matches[1];
            }
        }

        return null;
    }

    public function values()
    {
        $body = $this->toArray();

        if (isset($body['value'])) {
            return $body['value'];
        }

        if (isset($body['Ref_Key'])) {
            return [$body];
        }

        return $body;
    }
}