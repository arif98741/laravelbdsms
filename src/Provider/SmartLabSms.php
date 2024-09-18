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
use Xenon\LaravelBDSms\Helper\Helper;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

class SmartLabSms extends AbstractProvider
{
    private string $apiEndpoint = 'https://labapi.smartlabsms.com';

    /**
     * SmartLabSMS constructor.
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * Send Request To Api and Send Message
     * @throws GuzzleException|RenderException
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
            'user' => $config['user'],
            'password' => $config['password'],
            'sender' => $config['sender'],
            'smstext' => $text,
        ];

        if (!is_array($mobile)) {
            $this->apiEndpoint .= '/smsapi';
            $query['msisdn'] = Helper::ensureNumberStartsWith88($mobile);
        } else {
            $this->apiEndpoint .= '/smsapiv2';
            foreach ($mobile as $element) {
                $tempMobile[] = Helper::ensureNumberStartsWith88($element);
            }
            $query['msisdn'] = implode(',', $tempMobile);
        }

        $requestObject = new Request($this->apiEndpoint, $query, $queue, [], $queueName, $tries, $backoff);
        $response = $requestObject->get();
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
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('user', $this->senderObject->getConfig())) {
            throw new ParameterException('user key is absent in configuration');
        }
        if (!array_key_exists('password', $this->senderObject->getConfig())) {
            throw new ParameterException('password key is absent in configuration');
        }

        if (!array_key_exists('sender', $this->senderObject->getConfig())) {
            throw new ParameterException('sender key is absent in configuration');
        }
    }
}
