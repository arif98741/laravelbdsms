<?php
/*
 *  Last Modified: 10/04/23, 11:50 PM
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

class Muthofun extends AbstractProvider
{
    private string $apiEndpoint = 'https://sysadmin.muthobarta.com/api/v1/send-sms';

    /**
     * Muthofun constructor.
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
            'sender_id' => $config['sender_id'],
            'remove_duplicate' => true,
            'receiver' => $mobile,
            'message' => $text,
        ];

        if (is_array($mobile)) {
            $query['receiver'] = implode(',', $mobile);
        }

        $requestObject = new Request($this->apiEndpoint, $query, $queue, [], $queueName, $tries, $backoff);

        if (!str_starts_with($config['api_key'], "Token ")) {
            $config['api_key'] = "Token " . $config['api_key'];
        }
        $requestObject->setHeaders(['Authorization' => $config['api_key']])->setContentTypeJson(true);

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
        if (!array_key_exists('sender_id', $this->senderObject->getConfig())) {
            throw new RenderException('sender_id key is absent in configuration');
        }
        if (!array_key_exists('api_key', $this->senderObject->getConfig())) {
            throw new RenderException('api_key key is absent in configuration');
        }

    }
}
