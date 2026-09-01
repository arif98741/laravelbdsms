<?php
/*
 *  Last Modified: 05/24/24, 12:06 AM
 *  Copyright (c) 2023
 *  -created by Ariful Islam
 *  -All Rights Preserved By
 *  -If you have any query then knock me at
 *  arif98741@gmail.com
 *  See my profile @ https://github.com/arif98741
 */

namespace Xenon\LaravelBDSms\Provider;

use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;
class ZamanIt extends AbstractProvider
{
    private string $apiEndpoint = 'https://sms.zaman-it.com/api/sendsms';


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
            'api_key' => $config['api_key'],
            'type' => $config['type'],
            'senderid' => $config['senderid'],
            'phone' => $number,
            'message' => $text,
        ];

        $requestObject = $this->makeRequest($this->apiEndpoint, $query);

        return $this->respond($requestObject->post());
    }

    /**
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('api_key', $this->senderObject->getConfig())) {
            throw new ParameterException('api_key is absent in configuration');
        }

        if (!array_key_exists('type', $this->senderObject->getConfig())) {
            throw new ParameterException('type key is absent in configuration');
        }

        if (!array_key_exists('senderid', $this->senderObject->getConfig())) {
            throw new ParameterException('senderid key is absent in configuration');
        }

    }

}
