<?php

namespace Xenon\LaravelBDSms\Provider;

use GuzzleHttp\Exception\GuzzleException;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

class Viatech extends AbstractProvider
{
    private string $apiEndpoint = 'http://masking.viatech.com.bd/smsnet/bulk/api';

    /**
     * Viatech Constructor
     * @param Sender $sender
     * @version v1.0.38
     * @since v1.0.38
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

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
        $queue = $this->senderObject->getQueue();
        $queueName = $this->senderObject->getQueueName();
        $tries = $this->senderObject->getTries();
        $backoff = $this->senderObject->getBackoff();

        $query = [
            "api_key" => $config['api_key'],
            "mask" => $config['mask'],
            "recipient" => $number,
            "message" => $text,
        ];

        $requestObject = new Request($this->apiEndpoint, $query, $queue, [], $queueName, $tries, $backoff);
        $response = $requestObject->get();
        if ($queue) {
            return true;
        }

        $body = $response->getBody();
        $smsResult = $body->getContents();

        $data['number'] = $number;
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
        if (!array_key_exists('api_key', $config)) {
            throw new RenderException('api_key key is absent in configuration');
        }

        if (!array_key_exists('mask', $config)) {
            throw new RenderException('mask key is absent in configuration');
        }

    }
}
