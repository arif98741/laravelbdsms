<?php

namespace Xenon\LaravelBDSms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

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
}
