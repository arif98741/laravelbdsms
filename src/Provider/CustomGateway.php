<?php
/*
 *  Last Modified: 02/02/23, 11:50 PM
 *  Copyright (c) 2023
 *  -created by Ariful Islam
 *  -All Rights Preserved By
 *  -If you have any query then knock me at
 *  arif98741@gmail.com
 *  See my profile @https://github.com/arif98741
 */

namespace Xenon\LaravelBDSms\Provider;

use GuzzleHttp\Exception\GuzzleException;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

class CustomGateway extends AbstractProvider
{
    /**
     * Custom Gateway constructor.
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * Send Request To Api and Send Message
     * @throws RenderException|GuzzleException
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
        $query = $config;

        $requestObject = new Request($this->senderObject->url, $query, $queue, [], $queueName,$tries,$backoff);

        if (isset($this->senderObject->headers)) {
            $requestObject->setHeaders($this->senderObject->headers);
            $this->senderObject->contentTypeJson && $requestObject->setContentTypeJson(true);
        }

        $response = $this->senderObject->method === 'post' ? $requestObject->post() : $requestObject->get();

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
    final public function errorException(): void
    {
        if (!isset($this->senderObject->url)) {
            throw new RenderException("Url missing for custom gateway. Use setUrl() to set sms gateway endpoint ");
        }
    }
}
