<?php
/*
 *  Last Modified: 12/06/23, 11:50 PM
 *  Copyright (c) 2023
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

/**
 * =====================================================================================================================================
 * SMS.NET.BD( Alpha ) SMS is an advanced SMS service provider in Bangladesh with multiple services such as Bulk SMS, OTP Notification,
 * SMS Gateway, Marketing, Masking SMS, etc. It provides a powerful and easy-to-use SMS API for developers that you can use
 * to integration with Software Applications & Websites.
 * ======================================================================================================================================
 */
class SmsNetBD extends AbstractProvider
{
    private string $apiEndpoint = 'https://api.sms.net.bd/sendsms';

    /**
     * Alpha SMS constructor.
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
            'api_key' => $config['api_key'],
            'msg' => $text,
            'to' => $mobile,
        ];

        /**
         * The schedule date and time to send your message. Date and time must be formatted as Y-m-d H:i:s(eg. 2023-10-05 01:36:03)
         */
        if (isset($config['schedule'])) {
            $query['schedule'] = $config['schedule'];
        }

        /**
         * If you have an approved Sender ID, you can use this parameter to set your Sender ID as from in you messages.
         */
        if (isset($config['sender_id'])) {
            $query['sender_id'] = $config['sender_id'];
        }
        if (is_array($mobile)) {
            $query['to'] =  implode(',', $mobile);
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
    public function errorException(): void
    {
        if (!array_key_exists('api_key', $this->senderObject->getConfig())) {
            throw new RenderException('api_key key is absent in configuration');
        }
    }
}
