<?php
/*
 *  Last Modified: 09/16/24, 12:14 AM
 *  Copyright (c) 2024
 *  -created by Ariful Islam
 *  -All Rights Preserved By
 *  -If you have any query then knock me at
 *  arif98741@gmail.com
 *  See my profile @ https://github.com/arif98741
 */

namespace Xenon\LaravelBDSms\Provider;

use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

/**
 * Dhorola Class
 * api endpoint https://api.dhorolasms.net/smsapiv3
 */
class DhorolaSms extends AbstractProvider
{
    private string $apiEndpoint = 'https://api.dhorolasms.net/smsapiv3';

    /**
     * DhorolaSms constructor.
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * Send Request To Api and Send Message
     * @throws RenderException
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
            'apikey' => $config['apikey'],
            'sender' => $config['sender'],
            'msisdn' => $number,
            'smstext' => $text,
        ];

        if (is_array($number)) {
            $query['msisdn'] = implode(',', $number);
        }

        $headers = [
            'Content-Type' => 'application/json',
            'verify' => false,
        ];
        $requestObject = new Request($this->apiEndpoint, $query, $queue, [], $queueName, $tries, $backoff);
        $requestObject->setHeaders($headers)->setContentTypeJson(true);
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
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('apikey', $this->senderObject->getConfig())) {
            throw new ParameterException('apikey key is absent in configuration');
        }
        if (!array_key_exists('sender', $this->senderObject->getConfig())) {
            throw new ParameterException('sender key is absent in configuration');
        }

    }
}
