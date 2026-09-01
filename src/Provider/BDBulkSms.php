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


use Xenon\LaravelBDSms\Handler\ParameterException;
class BDBulkSms extends AbstractProvider
{
    private string $apiEndpoint = 'http://api.greenweb.com.bd/api2.php';

    /**
     * Send Request TO Server
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        $query = [
            'token' => $config['token'],
            'to' => $number,
            'message' => $text,
        ];
        $requestObject = $this->makeRequest($this->apiEndpoint, $query);

        return $this->respond($requestObject->get());

    }

    /**
     * @return void
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('token', $this->senderObject->getConfig())) {
            throw new ParameterException('token key is absent in configuration');
        }
    }
}
