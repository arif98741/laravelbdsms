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

use GuzzleHttp\Exception\GuzzleException;
use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

/**
 * TwentyFourBulksSMS Class
 * api endpoint https://24bulksms.com/24bulksms/api/api-sms-send
 */
class Twenty4BulkSms extends AbstractProvider
{
    private string $apiEndpoint = 'https://24bulksms.com/24bulksms/api/api-sms-send';

    /**
     * Twenty4BulkSms constructor.
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
        $tries=$this->senderObject->getTries();
        $backoff=$this->senderObject->getBackoff();

        $query = [
            'api_key' => $config['api_key'],
            'type' => 'text',
            'number' => $number,
            'message' => $text,
            'sender_id' => $config['sender_id'],
            'user_email' => $config['user_email'],
            'mobile_no' => $number,

        ];

        $headers = [
            'Content-Type' => 'application/json',
        ];
        $requestObject = new Request($this->apiEndpoint, $query, $queue, [], $queueName,$tries,$backoff);
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
        if (!array_key_exists('api_key', $this->senderObject->getConfig())) {
            throw new ParameterException('api_key key is absent in configuration');
        }
        if (!array_key_exists('sender_id', $this->senderObject->getConfig())) {
            throw new ParameterException('sender_id key is absent in configuration');
        }
        if (!array_key_exists('user_email', $this->senderObject->getConfig())) {
            throw new ParameterException('user_email key is absent in configuration');
        }

    }
}
