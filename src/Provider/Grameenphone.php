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
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

/**
 * Grameenphone Corporate Bulk SMS
 * Grameenphone is introducing Corporate Bulk SMS for the first time in Bangladesh, a dynamic SMS based communication
 * solution targeted towards the Business Clients by which they can send SMS from their own location integrated with
 * their applications with high speed modality.
 */
class Grameenphone extends AbstractProvider
{
    private string $apiEndpoint = 'https://gpcmp.grameenphone.com/ecmapigw/webresources/ecmapigw.v2';

    /**
     * Grameenphone constructor.
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

        $formParams = [
            'username' => $config['username'],
            'password' => $config['password'],
            'apicode' => 1, //1 – For Sending, 2 – Check CLI, 3 - For check credit balance, 4 – For Delivery report status, 5 - for SMS submit with delivery request, 6 - for SMS submit without delivery
            'msisdn' => $number,
            'countrycode' => "880", //880 for Bangladesh
            'cli' => 2222, //2222 cli code
            'messagetype' => $config['messagetype'], //1 – for Text, 3 – for Unicode(Bangla), 2 – for Flash
            'messageid' => 0,
            'message' => $text,
        ];

        $requestObject = new Request($this->apiEndpoint, $formParams, $queue, [], $queueName,$tries,$backoff);
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
        if (!array_key_exists('username', $this->senderObject->getConfig())) {
            throw new ParameterException('username key is absent in configuration');
        }

        if (!array_key_exists('password', $this->senderObject->getConfig())) {
            throw new ParameterException('password key is absent in configuration');
        }

        if (!array_key_exists('messagetype', $this->senderObject->getConfig())) {
            throw new ParameterException('messagetype key is absent in configuration. 1 for Text, 2 for Flash, 3 for Unicode(Bangla)');
        }

        $configMessageType = $this->senderObject->getConfig()['messagetype'];
        $validMessageTypes = [1,2,3];

        if (!in_array($configMessageType, $validMessageTypes)) {
            throw new ParameterException('Invalid messagetype. Set value 1 for Text, 2 for Flash, 3 for Unicode(Bangla)');
        }

    }
}
