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
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Helper\Helper;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

class Lpeek extends AbstractProvider
{
    private string $apiEndpoint = 'https://sms.lpeek.com/API/sendSMS';

    /**
     * Lpeek constructor.
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
        $number = Helper::ensureNumberStartsWith88($this->senderObject->getMobile());

        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();
        $queue = $this->senderObject->getQueue();
        $queueName = $this->senderObject->getQueueName();
        $tries=$this->senderObject->getTries();
        $backoff=$this->senderObject->getBackoff();

        $data = [
            'auth' => [
                'acode' => $config['acode'],
                'apiKey' => $config['apiKey'],
            ],
            'smsInfo' => [
                'requestID' => $config['requestID'],
                'message' => $text,
                'is_unicode' => $config['is_unicode'] ?? 0,
                'masking' => $config['masking'],
                'msisdn' => $number,
                'transactionType' => $config['transactionType'] ?? 'T',
            ],
        ];

        $requestObject = new Request($this->apiEndpoint, $data, $queue, [], $queueName,$tries,$backoff);
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
        if (!array_key_exists('acode', $this->senderObject->getConfig())) {
            throw new ParameterException('acode is absent in configuration');
        }

        if (!array_key_exists('apiKey', $this->senderObject->getConfig())) {
            throw new ParameterException('apiKey key is absent in configuration');
        }
        if (!array_key_exists('requestID', $this->senderObject->getConfig())) {
            throw new ParameterException('requestID key is absent in configuration');
        }
        if (!array_key_exists('masking', $this->senderObject->getConfig())) {
            throw new ParameterException('masking key is absent in configuration');
        }
    }

}
