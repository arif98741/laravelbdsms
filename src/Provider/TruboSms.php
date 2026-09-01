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

use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;
class TruboSms extends AbstractProvider
{
    private string $apiEndpoint = 'https://panel.trubosms.com/api/v3/sms/send';


    /**
     * Send Request To Api and Send Message
     * @throws RenderException
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        $query = [
            'recipient' => '+88'.$number,
            'sender_id' => $config['sender_id'],
            'message' => $text,
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $config['api_token'],
            'Content-Type' => 'application/json'
        ];

        $requestObject = $this->makeRequest($this->apiEndpoint, $query);
        $requestObject->setHeaders($headers)->setContentTypeJson(true);
        return $this->respond($requestObject->post());
    }

    /**
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('api_token', $this->senderObject->getConfig())) {
            throw new ParameterException('api_token is absent in configuration');
        }

        if (!array_key_exists('sender_id', $this->senderObject->getConfig())) {
            throw new ParameterException('sender_id key is absent in configuration');
        }
    }

}
