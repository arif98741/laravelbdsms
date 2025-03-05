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
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

/**
 * Class MimSms
 * @package Xenon\LaravelBDSmsLog\Provider
 * @version v1.0.20
 * @since v1.0.20
 */
class MimSms extends AbstractProvider
{
    private string $apiEndpoint = 'https://api.mimsms.com/api/SmsSending/SMS';

    /**
     * Mimsms constructor.
     * @param Sender $sender
     * @version v1.0.20
     * @since v1.0.20
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * Send Request To Api and Send Message
     * @return bool|string
     * @throws GuzzleException
     * @throws RenderException
     * @version v1.0.20
     * @since v1.0.20
     */
    public function sendRequest()
    {
        $config = $this->senderObject->getConfig();

        $queue = $this->senderObject->getQueue();
        $queueName = $this->senderObject->getQueueName();
        $tries = $this->senderObject->getTries();
        $backoff = $this->senderObject->getBackoff();
        $text = $this->senderObject->getMessage();
        $number = $this->senderObject->getMobile();

        $queryArray = [
            'ApiKey' => $config['ApiKey'],
            'SenderName' => $config['SenderName'],
            'UserName' => $config['UserName'],
            'TransactionType' => $config['TransactionType'] ?? 'T',
            'CampaignId' => $config['CampaignId'] ?? 'null',
            'CampaignName' => $config['CampaignName'] ?? "",
            'MobileNumber' => $number,
            'Message' => $text,
        ];

        $requestObject = new Request($this->apiEndpoint, $queryArray, $queue, [], $queueName, $tries, $backoff);
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
     * @version v1.0.20
     * @since v1.0.20
     */
    public function errorException()
    {

        if (!array_key_exists('ApiKey', $this->senderObject->getConfig())) {
            throw new ParameterException('ApiKey is absent in configuration');
        }
        if (!array_key_exists('SenderName', $this->senderObject->getConfig())) {
            throw new ParameterException('SenderName key is absent in configuration');
        }
        if (!array_key_exists('UserName', $this->senderObject->getConfig())) {
            throw new ParameterException('UserName key is absent in configuration');
        }

    }

}
