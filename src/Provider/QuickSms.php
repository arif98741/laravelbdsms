<?php
/*
 *  Last Modified: 26/10/23, 10:40 PM
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

class QuickSms extends AbstractProvider
{
    private string $apiEndpoint = 'https://server1.quicksms.xyz/smsapi';

    /**
     * QuickSms constructor.
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
            'senderid' => $config['senderid'],
            'contacts' => $mobile,
            'msg' => $text,
        ];

        if (array_key_exists('type', $config)) {
            $query ['type'] = $config['type'];
        }

        if (array_key_exists('scheduledDateTime', $config)) {
            $query ['scheduledDateTime'] = $config['scheduledDateTime'];
        }

        if (is_array($mobile)) {
            $query['contacts'] =  implode(',', $mobile);
        }

        $requestObject = new Request($this->apiEndpoint, $query, $queue, [], $queueName,$tries,$backoff);
        $requestObject->setContentTypeJson(true);

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
        if (!array_key_exists('senderid', $this->senderObject->getConfig())) {
            throw new RenderException('senderid key is absent in configuration');
        }
    }
}
