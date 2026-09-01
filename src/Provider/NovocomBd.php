<?php
/*
 *  Last Modified: 6/16/22, 12:56 AM
 *  Copyright (c) 2022
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
class NovocomBd extends AbstractProvider
{
    private string $apiEndpoint = 'https://sms.novocom-bd.com/api/v2/SendSMS';


    /**
     * Send Request To Api and Send Message
     * @return bool|string
     * @throws GuzzleException
     * @throws RenderException
     */
    public function sendRequest()
    {
        $text = $this->senderObject->getMessage();
        $number = $this->senderObject->getMobile();
        $config = $this->senderObject->getConfig();

        $query = [
            'ApiKey' => $config['ApiKey'],
            'ClientId' => $config['ClientId'],
            'SenderId' => $config['SenderId'],
            'MobileNumbers' => $number,
            'Message' => $text,
            'Is_Unicode' => true,
        ];

        $requestObject = $this->makeRequest($this->apiEndpoint, $query);
        return $this->respond($requestObject->get());
    }

    /**
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('ApiKey', $this->senderObject->getConfig())) {
            throw new ParameterException('ApiKey is absent in configuration');
        }
        if (!array_key_exists('ClientId', $this->senderObject->getConfig())) {
            throw new ParameterException('ClientId key is absent in configuration');
        }
        if (!array_key_exists('SenderId', $this->senderObject->getConfig())) {
            throw new ParameterException('SenderId key is absent in configuration');
        }
    }

}
