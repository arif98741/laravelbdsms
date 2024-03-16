<?php
/*
 *  Last Modified: 6/28/21, 11:18 PM
 *  Copyright (c) 2021
 *  -created by Ariful Islam
 *  -All Rights Preserved By
 *  -If you have any query then knock me at
 *  arif98741@gmail.com
 *  See my profile @ https://github.com/arif98741
 */

namespace Xenon\LaravelBDSms\Provider;

use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

class Ssl extends AbstractProvider
{
    private string $apiEndpoint = 'https://smsplus.sslwireless.com/api/v3/send-sms';

    /**
     * Ssl constructor.
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
        $mobile = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();
        $queue = $this->senderObject->getQueue();
        $queueName = $this->senderObject->getQueueName();
        $tries = $this->senderObject->getTries();
        $backoff = $this->senderObject->getBackoff();

        $query = [
            'api_token' => $config['api_token'],
            'sid' => $config['sid'],
            'csms_id' => $config['csms_id'],
            'msisdn' => $mobile,
            'sms' => $text,
        ];

        $requestObject = new Request($this->apiEndpoint . (is_array($mobile) ? '/bulk' : ''), $query, $queue, [], $queueName, $tries, $backoff);
        $response = $requestObject->post();
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
     */
    public function errorException()
    {
        if (!array_key_exists('api_token', $this->senderObject->getConfig())) {
            throw new RenderException('api_token key is absent in configuration');
        }

        if (!array_key_exists('sid', $this->senderObject->getConfig())) {
            throw new RenderException('sid key is absent in configuration');
        }

        if (!array_key_exists('csms_id', $this->senderObject->getConfig())) {
            throw new RenderException('csms_id key is absent in configuration');
        }

    }
}
