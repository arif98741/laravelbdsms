<?php

namespace Xenon\LaravelBDSms\Provider;

use GuzzleHttp\Exception\GuzzleException;
use Xenon\LaravelBDSms\Handler\RenderException;

class Infobip extends AbstractProvider
{

    /**
     * @param $config
     * @return string[]
     * @version v1.0.32
     * @since v1.0.31
     */
    private function getHeaders($config): array
    {
        return [
            'accept' => "application/json",
            'authorization' => 'Basic ' . base64_encode($config['user'] . ':' . $config['password']),
            'content-type' => 'application/json'
        ];
    }

    /**
     * @return false|string
     * @throws RenderException
     * @version v1.0.32
     * @since v1.0.31
     */
    public function sendRequest()
    {
        $mobile = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        $url = $config['base_url'] . "/sms/2/text/single";
        $headers = $this->getHeaders($config);
        $query = [
            'from' => $config['from'],
            'to' => "+88" . $mobile,
            'text' => $text
        ];

        $requestObject = $this->makeRequest($url, $query);
        $requestObject->setHeaders($headers)
            ->setContentTypeJson(true);
        return $this->respond($requestObject->post());
    }

    /**
     * @throws RenderException
     * @version v1.0.32
     * @since v1.0.31
     */
    public function errorException()
    {
        $config = $this->senderObject->getConfig();

        if (!array_key_exists('base_url', $config)) {
            throw new RenderException('base_url key is absent in configuration');
        }

        if (!array_key_exists('from', $config)) {
            throw new RenderException('from key is absent in configuration');
        }

        if (!array_key_exists('user', $config)) {
            throw new RenderException('user key is absent in configuration');
        }

        if (!array_key_exists('password', $config)) {
            throw new RenderException('password key is absent in configuration');
        }
    }
}
