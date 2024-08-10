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

class AjuraTech extends AbstractProvider
{
    private string $apiEndpoint = 'https://smpp.revesms.com:7790/sendtext?json';

    /**
     * Ajuratech constructor.
     * @param Sender $sender
     * @version v1.0.34
     * @since v1.0.34
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * Send Request To Api and Send Message
     * @return false|string
     * @throws GuzzleException
     * @throws RenderException
     * @since v1.0.34
     * @version v1.0.34
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
            'apikey' => $config['apikey'],
            'secretkey' => $config['secretkey'],
            'callerID' => $config['callerID'],
            'toUser' => $number,
            'messageContent' => $text,
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
     * @version v1.0.34
     * @since v1.0.34
     */
    public function errorException()
    {
        if (!array_key_exists('apikey', $this->senderObject->getConfig())) {
            throw new ParameterException('apikey is absent in configuration');
        }
        if (!array_key_exists('secretkey', $this->senderObject->getConfig())) {
            throw new ParameterException('secretkey is absent in configuration');
        }
        if (!array_key_exists('callerID', $this->senderObject->getConfig())) {
            throw new ParameterException('callerID is absent in configuration');
        }
    }
}
