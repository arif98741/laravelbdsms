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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

class Adn extends AbstractProvider
{
    private string $apiEndpoint = 'https://portal.adnsms.com';
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
        $tries=$this->senderObject->getTries();
        $backoff=$this->senderObject->getBackoff();
        $query = [];
        $requestObject = new Request($this->apiEndpoint, $query, $queue, [
            'Accept' => 'application/json'
        ], $queueName,$tries,$backoff);

        $requestObject->setFormParams([
            'api_key' => $config['api_key'],
            'type' => $config['type'],
            'senderid' => $config['senderid'],
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
        if (!array_key_exists('api_key', $this->senderObject->getConfig())) {
            throw new ParameterException('api_key is absent in configuration');
        }
        if (!array_key_exists('api_secret', $this->senderObject->getConfig())) {
            throw new ParameterException('api_secret key is absent in configuration');
        }
        if (!array_key_exists('request_type', $this->senderObject->getConfig())) {
            throw new ParameterException('request_type key is absent in configuration');
        }
        if (!array_key_exists('message_type', $this->senderObject->getConfig())) {
            throw new ParameterException('message_type key is absent in configuration');
        }

    }
}
