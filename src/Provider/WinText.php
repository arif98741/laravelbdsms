<?php

namespace Xenon\LaravelBDSms\Provider;

use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Helper\Helper;
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

class WinText extends AbstractProvider
{
    private string $apiEndpoint = 'https://api.wintextbd.com/SingleSms';

    /**
     * WinText Constructor
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
            "token" => $config['token'],
            "messagetype" => $config['messagetype'] ?? 1,
            "ismasking" => $config['ismasking'] ?? 'false',
            "masking" => $config['masking'] ?? 'null',
            "SMSText" => $text,
        ];

        if (!is_array($mobile)) {
            $formParams['mobileno'] = Helper::ensureNumberStartsWith88($mobile);
        } else {
            /*foreach ($mobile as $element) {
                $tempMobile[] = Helper::ensureNumberStartsWith88($element);
            }
            $formParams['mobileno'] = implode(',', $tempMobile);*/
        }

        //dd($this->apiEndpoint, $formParams);
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

        if (!array_key_exists('token', $config)) {
            throw new RenderException('token key is absent in configuration');
        }
    }
}
