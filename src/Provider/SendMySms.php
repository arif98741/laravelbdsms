<?php
/*
 *  Last Modified: 10/01/24, 03:10 AM
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
 * Sendmysms Class
 */
class SendMySms extends AbstractProvider
{
    private string $apiEndpoint = 'https://sendmysms.net/api.php';


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
            'user' => $config['user'],
            'key' => $config['key'],
            'to' => $number,
            'msg' => $text,
        ];

        $requestObject = $this->makeRequest($this->apiEndpoint, $query);
        $requestObject->setFormParams($query);
        return $this->respond($requestObject->post());
    }

    /**
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('user', $this->senderObject->getConfig())) {
            throw new ParameterException('user key is absent in configuration');
        }
        if (!array_key_exists('key', $this->senderObject->getConfig())) {
            throw new ParameterException('key is absent in configuration');
        }

    }
}
