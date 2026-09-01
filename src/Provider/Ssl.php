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
class Ssl extends AbstractProvider
{
    private string $apiEndpoint = 'https://smsplus.sslwireless.com/api/v3/send-sms';


    /**
     * Send Request To Api and Send Message
     * @throws RenderException
     */
    public function sendRequest()
    {
        $mobile = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        $query = [
            'api_token' => $config['api_token'],
            'sid' => $config['sid'],
            'msisdn' => $mobile,
            'csms_id' => $config['csms_id'],
            'sms' => $text,
            'batch_csms_id' => $config['batch_csms_id'] ?? null,
        ];

        $requestObject = $this->makeRequest($this->apiEndpoint . (is_array($mobile) ? '/bulk' : ''), $query);
        $requestObject->setHeaders([
            'Content-Type' => 'application/json',
        ])->setContentTypeJson(true);

        return $this->respond($requestObject->post());
    }

    /**
     * @throws RenderException
     */
    public function errorException()
    {
        if (!array_key_exists('api_token', $this->senderObject->getConfig())) {
            throw new RenderException('api_token key is absent in configuration');
        }

        if (!array_key_exists('sid', $this->senderObject->getConfig())) {
            throw new RenderException('sid key is absent in configuration');
        }

        if (!array_key_exists('csms_id', $this->senderObject->getConfig())) {
            throw new RenderException('csms_id key is absent in configuration');
        }

        if (is_array($this->senderObject->getMobile()) && !array_key_exists('batch_csms_id', $this->senderObject->getConfig())) {
            throw new RenderException('batch_csms_id key is absent in configuration. This is required if you send array of receivers');
        }

    }
}
