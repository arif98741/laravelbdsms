<?php
/*
 *  Last Modified: 6/16/22, 12:56 AM
 *  Copyright (c) 2022
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

class DurjoySoft extends AbstractProvider
{
    private string $apiEndpoint = 'https://smsp.durjoysoft.com/api/sms';

    /**
     * DurjoySoft constructor.
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * Send Request To Api and Send Message
     * @return bool|string
     * @throws GuzzleException
     * @throws RenderException
     */
    public function sendRequest()
    {
        $text = $this->senderObject->getMessage();
        $number = $this->senderObject->getMobile();
        $config = $this->senderObject->getConfig();
        $queue = $this->senderObject->getQueue();
        $queueName = $this->senderObject->getQueueName();
        $tries=$this->senderObject->getTries();
        $backoff=$this->senderObject->getBackoff();

        $query = [
            'ApiKey' => $config['ApiKey'],
            'SenderID' => $config['SenderID'],
            'number' => $number,
            'sms' => $text,
            'IsUnicode' => 2,
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
        if (!array_key_exists('ApiKey', $this->senderObject->getConfig())) {
            throw new ParameterException('ApiKey is absent in configuration');
        }
        if (!array_key_exists('SenderID', $this->senderObject->getConfig())) {
            throw new ParameterException('SenderID key is absent in configuration');
        }
    }

}
