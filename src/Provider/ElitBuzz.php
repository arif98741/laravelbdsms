<?php

namespace Xenon\LaravelBDSms\Provider;

use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

class ElitBuzz extends AbstractProvider
{
    /**
     * Elitbuzz Constructor
     * @param Sender $sender
     * @version v1.0.32
     * @since v1.0.31
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
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
        $queue = $this->senderObject->getQueue();
        $queueName = $this->senderObject->getQueueName();
        $tries=$this->senderObject->getTries();
        $backoff=$this->senderObject->getBackoff();

        $formParams = [
            "api_key" => $config['api_key'],
            "type" => "text",
            "senderid" => $config['senderid'],
            "contacts" => $mobile,
            "msg" => urlencode($text),
        ];

        $requestUrl = $config['url'] . "/smsapi";
        $requestObject = new Request($requestUrl, [], $queue, [], $queueName,$tries,$backoff);
        $requestObject->setFormParams($formParams);
        $response = $requestObject->post(false, 60);
        if ($queue) {
            return true;
        }


        $body = $response->getBody();
        $smsResult = $body->getContents();

        $data['number'] = $mobile;
        $data['message'] = $text;
        return $this->generateReport($smsResult, $data)->getContent();
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
    }
}
