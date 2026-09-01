<?php

namespace Xenon\LaravelBDSms\Provider;

use Xenon\LaravelBDSms\Handler\RenderException;

class ElitBuzz extends AbstractProvider
{

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

      $formParams = [
            "api_key" => $config['api_key'],
            "type" => $config['type'],
            "senderid" => $config['senderid'],
            "contacts" => $mobile,
            "msg" => $text,
        ];

        $requestUrl = $config['url'] . "/smsapi";
        $requestObject = $this->makeRequest($requestUrl);
        $requestObject->setFormParams($formParams);
        return $this->respond($requestObject->post(false, 60));
    }

    /**
     * @throws RenderException
     * @version v1.0.32
     * @since v1.0.31
     */
    public function errorException()
    {
        $config = $this->senderObject->getConfig();

        if (!array_key_exists('url', $config)) {
            throw new RenderException('url key is absent in configuration');
        }

        if (!array_key_exists('api_key', $config)) {
            throw new RenderException('api_key key is absent in configuration');
        }

        if (!array_key_exists('senderid', $config)) {
            throw new RenderException('senderid key is absent in configuration');
        }

        if (!array_key_exists('type', $config)) {
            throw new RenderException('type key is absent in configuration');
        }
    }
}
