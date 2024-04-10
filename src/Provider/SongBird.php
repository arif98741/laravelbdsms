<?php
/*
 *  Last Modified: 04/10/24, 01:06 PM
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
 * Songbird Sms Gateway
 */
class SongBird extends AbstractProvider
{
    private string $apiEndpoint = 'http://103.53.84.15:8746/sendtext';

    /**
     * SongBird constructor.
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


        $formParams = [
            'apikey' => $config['apikey'],
            'secretkey' => $config['secretkey'],
            'callerID' => $config['callerID'],
            'toUser' => $number,
            'messageContent' => $text,
        ];

        $requestObject = new Request($this->apiEndpoint, $formParams, $queue, [], $queueName, $tries, $backoff);
        $requestObject->setContentTypeJson(true);
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

        if (!array_key_exists('secretkey', $this->senderObject->getConfig())) {
            throw new ParameterException('secretkey key is absent in configuration');
        }

        if (!array_key_exists('callerID', $this->senderObject->getConfig())) {
            throw new ParameterException('callerID key is absent in configuration.');
        }
    }
}
