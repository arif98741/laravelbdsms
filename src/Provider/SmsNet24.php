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
use Xenon\LaravelBDSms\Helper\Helper;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

class SmsNet24 extends AbstractProvider
{
    private string $apiEndpoint = 'https://sms.apinet.club/sendSms';

    /**
     * SmsNet24 constructor.
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
        $tries=$this->senderObject->getTries();
        $backoff=$this->senderObject->getBackoff();

        $query = [
            'user_id' => $config['user_id'],
            'user_password' => $config['user_password'],
            'sms_text' => $text,
        ];

        if (is_array($mobile)) {
            $query['sms_receiver'] = Helper::getCommaSeperatedNumbers($mobile);
            $explodeMobileNumbers = explode(',', $query['sms_receiver']);
            foreach ($explodeMobileNumbers as $arrayData)
            {
                $newMobiles[] =  Helper::checkMobileNumberPrefixExistence($arrayData);
            }
            $query['sms_receiver'] = implode(',', $newMobiles);
        }else{
            $query['sms_receiver'] = Helper::checkMobileNumberPrefixExistence($mobile);
        }

        if (array_key_exists('route_id', $config)) {
            $query['route_id'] = $config['route_id'];
        }
        if (array_key_exists('sms_type_id', $config)) {
            $query['sms_type_id'] = $config['sms_type_id'];
        }

        $requestObject = new Request($this->apiEndpoint, $query, $queue, [], $queueName,$tries,$backoff);
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
        if (!array_key_exists('user_id', $this->senderObject->getConfig())) {
            throw new RenderException('user_id key is absent in configuration');
        }

        if (!array_key_exists('user_password', $this->senderObject->getConfig())) {
            throw new RenderException('user_password key is absent in configuration');
        }

    }
}
