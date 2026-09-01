<?php

namespace Xenon\LaravelBDSms\Provider;

use GuzzleHttp\Exception\GuzzleException;
use Xenon\LaravelBDSms\Handler\RenderException;

class BulkSmsDhaka extends AbstractProvider
{
    private string $apiEndpoint = 'https://bulksmsdhaka.net/api/sendtext';


    /**
     * @return false|string
     * @throws GuzzleException
     * @throws RenderException
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        $query = [
            'api_key' => $config['api_key'],
            'number' => $number,
            'message' => $text,
            'callerID' => $config['callerID'],
        ];

        $requestObject = $this->makeRequest($this->apiEndpoint, $query);

        return $this->respond($requestObject->get());
    }

    /**
     * @throws RenderException
     */
    public function errorException()
    {
        $config = $this->senderObject->getConfig();

        if (!array_key_exists('api_key', $config)) {
            throw new RenderException('api_key key is absent in configuration');
        }

        if (!array_key_exists('callerID', $config)) {
            throw new RenderException('callerID key is absent in configuration');
        }
    }
}
