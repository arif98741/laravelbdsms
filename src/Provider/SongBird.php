<?php
/*
 *  Last Modified: 04/10/24, 01:06 PM
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
/**
 * Songbird Sms Gateway
 */
class SongBird extends AbstractProvider
{
    private string $apiEndpoint = 'http://103.53.84.15:8746/sendtext';


    /**
     * Send Request To Api and Send Message
     * @throws RenderException
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();


        $formParams = [
            'apikey' => $config['apikey'],
            'secretkey' => $config['secretkey'],
            'callerID' => $config['callerID'],
            'toUser' => $number,
            'messageContent' => $text,
        ];

        $requestObject = $this->makeRequest($this->apiEndpoint, $formParams);
        $requestObject->setContentTypeJson(true);
        return $this->respond($requestObject->post());
    }

    /**
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('apikey', $this->senderObject->getConfig())) {
            throw new ParameterException('apikey key is absent in configuration');
        }

        if (!array_key_exists('secretkey', $this->senderObject->getConfig())) {
            throw new ParameterException('secretkey key is absent in configuration');
        }

        if (!array_key_exists('callerID', $this->senderObject->getConfig())) {
            throw new ParameterException('callerID key is absent in configuration.');
        }
    }
}
