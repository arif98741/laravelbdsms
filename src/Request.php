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
     */
    public function get($requestUrl, array $query, bool $verify = false, $timeout = 10.0)
    {
        $client = new Client([
            'base_uri' => $requestUrl,
            'timeout' => $timeout,
        ]);

        return $client->request('GET', '', [
            'query' => $query,
            'verify' => $verify
        ]);

    }

    /**
     * @param false $verify
     * @throws RenderException
     */
    public function post($requestUrl, array $query, bool $verify = false, $timeout = 10.0)
    {
        $client = new Client();

        try {

            return $client->post($requestUrl, [
                RequestOptions::JSON => $query
            ]);

        } catch (GuzzleException|ClientException $e) {
            throw new RenderException($e->getMessage());
        }

    }


}
