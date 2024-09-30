<?php
/*
 *  Last Modified: 09/16/24, 12:14 AM
 *  Copyright (c) 2024
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
 * Sendmysms Class
 * api endpoint https://sendmysms.net/api.php
 */
class SendMySms extends AbstractProvider
{
    private string $apiEndpoint = 'https://sendmysms.net/api.php';

    /**
     * Sendmysms constructor.
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
        $tries = $this->senderObject->getTries();
        $backoff = $this->senderObject->getBackoff();

        $query = [
            'user' => $config['user'],
            'key' => $config['key'],
            'to' => $number,
            'msg' => $text,
        ];

        if (is_array($number)) {
        //    $query['to'] = ['01733499574', '01750840217'];
        }



        $requestObject = new Request($this->apiEndpoint, $query, $queue, [], $queueName, $tries, $backoff);
        $requestObject->setFormParams($query);
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
        if (!array_key_exists('user', $this->senderObject->getConfig())) {
            throw new ParameterException('user key is absent in configuration');
        }
        if (!array_key_exists('key', $this->senderObject->getConfig())) {
            throw new ParameterException('key key is absent in configuration');
        }

    }
}
