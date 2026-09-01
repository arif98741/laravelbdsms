<?php

namespace Xenon\LaravelBDSms\Provider;

use GuzzleHttp\Exception\GuzzleException;
use Xenon\LaravelBDSms\Handler\RenderException;

class Viatech extends AbstractProvider
{
    private string $apiEndpoint = 'http://masking.viatech.com.bd/smsnet/bulk/api';


    /**
     * @return false|string
     * @throws GuzzleException
     * @throws RenderException
     * @version v1.0.38
     * @since v1.0.38
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        $query = [
            "api_key" => $config['api_key'],
            "mask" => $config['mask'],
            "recipient" => $number,
            "message" => $text,
        ];

        $requestObject = $this->makeRequest($this->apiEndpoint, $query);
        return $this->respond($requestObject->get());
    }

    /**
     * @throws RenderException
     * @version v1.0.32
     * @since v1.0.31
     */
    public function errorException()
    {
        $config = $this->senderObject->getConfig();
        if (!array_key_exists('api_key', $config)) {
            throw new RenderException('api_key key is absent in configuration');
        }

        if (!array_key_exists('mask', $config)) {
            throw new RenderException('mask key is absent in configuration');
        }

    }
}
