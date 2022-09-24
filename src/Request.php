<?php

namespace Xenon\LaravelBDSms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Xenon\LaravelBDSms\Handler\RenderException;

class Request
{

    /**
     * @param false $verify
     * @throws GuzzleException
     * @throws RenderException
     */
    public function get($requestUrl, array $query, array $headers = [], bool $verify = false, $timeout = 10.0)
    {
        $client = new Client([
            'base_uri' => $requestUrl,
            'timeout' => $timeout,
        ]);

        try {

            return $client->request('get', $requestUrl, [
                'query'=> $query,
                'headers' => $headers,
                'verify' => $verify,
                'timeout' => $timeout,
            ]);

        } catch (GuzzleException|ClientException $e) {
            throw new RenderException($e->getMessage());
        }


    }

    /**
     * @param $requestUrl
     * @param array $query
     * @param array $headers
     * @param bool $verify
     * @param float $timeout
     * @return \Psr\Http\Message\ResponseInterface
     * @throws RenderException
     */
    public function post($requestUrl, array $query, array $headers = [], bool $verify = false, float $timeout = 20.0)
    {
        $client = new Client();
        try {

            return $client->request('post', $requestUrl, [
                RequestOptions::JSON => $query,
                'headers' => $headers,
                'verify' => $verify,
                'timeout' => $timeout,
            ]);

        } catch (GuzzleException|ClientException $e) {
            throw new RenderException($e->getMessage());
        }

    }


}
