<?php
/*
 *  Last Modified: 6/29/21, 12:06 AM
 *  Copyright (c) 2021
 *  -created by Ariful Islam
 *  -All Rights Preserved By
 *  -If you have any query then knock me at
 *  arif98741@gmail.com
 *  See my profile @ https://github.com/arif98741
 */

namespace Xenon\LaravelBDSms\Provider;

use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

class GreenWeb extends AbstractProvider
{
    private string $apiEndpoint = 'https://api.greenweb.com.bd/api.php?json';

    /**
     * GreenWeb constructor.
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * Send Request To Api and Send Message
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();
        $queue = $this->senderObject->getQueue();
        $queueName = $this->senderObject->getQueueName();
        $tries=$this->senderObject->getTries();
        $backoff=$this->senderObject->getBackoff();

        $query = [
            'token' => $config['token'],
            'to' => $number,
            'message' => $text,
        ];

        $requestObject = new Request($this->apiEndpoint, $query, $queue, [], $queueName,$tries,$backoff);
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
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('token', $this->senderObject->getConfig())) {
            throw new ParameterException('token key is absent in configuration');
        }
    }
}
