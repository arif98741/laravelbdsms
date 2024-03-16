<?php

namespace Xenon\LaravelBDSms\Provider;

use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

class SMSNoc extends AbstractProvider
{
    private string $apiEndpoint = 'https://app.smsnoc.com/api/v3/sms/send';

    /**
     * Infobip Constructor
     * @param Sender $sender
     * @version v1.0.32
     * @since v1.0.31
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * @param $config
     * @return string[]
     * @version v1.0.32
     * @since v1.0.31
     */
    private function getHeaders($config): array
    {
        return [
            'Authorization' => 'Bearer ' . $config['bearer_token'],
            'Content-Type' => 'application/json',
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
        $config = $this->senderObject->getConfig();
        $queue = $this->senderObject->getQueue();
        $queueName = $this->senderObject->getQueueName();
        $tries=$this->senderObject->getTries();
        $backoff=$this->senderObject->getBackoff();
        $text = $this->senderObject->getMessage();
        $number = $this->senderObject->getMobile();

        $query = [
            'recipient' => '+88'.$number,
            'message' => $text,
            'type' => "plain",
            'sender_id' => $config['sender_id'],
        ];

        $requestObject = new Request($this->apiEndpoint, $query, $queue, [], $queueName,$tries,$backoff);
        $requestObject->setHeaders($this->getHeaders($config))->setContentTypeJson(true);

        $response = $requestObject->post();
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

        if (!array_key_exists('sender_id', $config)) {
            throw new RenderException('sender_id key is absent in configuration');
        }
        if (!array_key_exists('bearer_token', $config)) {
            throw new RenderException('bearer_token key is absent in configuration');
        }
    }
}
