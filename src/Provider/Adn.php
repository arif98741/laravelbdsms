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

class Adn extends AbstractProvider
{
    private string $apiEndpoint = 'https://portal.adnsms.com/api/v1/secure/send-sms';

    /**
     * Adn constructor.
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
        $tries = $this->senderObject->getTries();
        $backoff = $this->senderObject->getBackoff();
        $query = [];
        $requestObject = new Request($this->apiEndpoint, $query, $queue, [
            'Accept' => 'application/json'
        ], $queueName, $tries, $backoff);

        $requestObject->setFormParams([
            'api_key' => $config['api_key'],
            'api_secret' => $config['api_secret'],
            'request_type' => $config['request_type'],
            'message_type' => $config['message_type'],
            'senderid' => $config['senderid'] ?? null,
            'mobile' => $number,
            'message_body' => $text,
        ]);

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
        $configArray = $this->senderObject->getConfig();

        if (!array_key_exists('api_key', $configArray)) {
            throw new ParameterException('api_key is absent in configuration');
        }
        if (!array_key_exists('api_secret', $configArray)) {
            throw new ParameterException('api_secret key is absent in configuration');
        }
        if (!array_key_exists('request_type', $configArray)) {
            throw new ParameterException('request_type key is absent in configuration');
        }
        if (!array_key_exists('message_type', $configArray)) {
            throw new ParameterException('message_type key is absent in configuration');
        }

        $allowedRequestTypes = ['SINGLE_SMS', 'OTP', 'GENERAL_CAMPAIGN', 'MULTIBODY_CAMPAIGN'];

        if (!in_array($configArray['request_type'], $allowedRequestTypes)) {
            throw new ParameterException('request_type key is invalid. Allowed request_type values are ' . implode(', ', $allowedRequestTypes));
        }
    }
}
