<?php

namespace Xenon\LaravelBDSms\Provider;

use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Helper\Helper;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

class SmsBangladesh extends AbstractProvider
{
    private string $apiEndpoint = 'https://panel.smsbangladesh.com/api';

    /**
     * SmsBangladesh Constructor
     * @param Sender $sender
     * @version v1.0.32
     * @since v1.0.31
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * @return false|string
     * @throws RenderException
     * @version v1.0.32
     * @since v1.0.31
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

        $formParams = [
            "user" => $config['user'],
            "password" => $config['password'],
            "from" => $config['from'],
            "text" => urlencode($text),
        ];

        if (!is_array($mobile)) {
            $formParams['to'] = Helper::ensureNumberStartsWith88($mobile);
        } else {
            foreach ($mobile as $element) {
                $tempMobile[] = Helper::ensureNumberStartsWith88($element);
            }
            $formParams['to'] = implode(',', $tempMobile);
        }

        $requestObject = new Request($this->apiEndpoint, [], $queue, [], $queueName, $tries, $backoff);
        $requestObject->setFormParams($formParams);
        $response = $requestObject->post(false, 60);
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
     * @version v1.0.32
     * @since v1.0.31
     */
    public function errorException()
    {
        $config = $this->senderObject->getConfig();

        if (!array_key_exists('user', $config)) {
            throw new RenderException('user key is absent in configuration');
        }

        if (!array_key_exists('password', $config)) {
            throw new RenderException('password key is absent in configuration');
        }

        if (!array_key_exists('from', $config)) {
            throw new RenderException('from key is absent in configuration');
        }
    }
}
