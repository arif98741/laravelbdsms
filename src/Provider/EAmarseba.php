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
class EAmarseba extends AbstractProvider
{
    private string $apiEndpoint = 'https://e-amarseba.com/api/v1/http/services/bulk-sms/send-sms';


    /**
     * Send Request To Api and Send Message
     * @throws RenderException
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        if (!is_array($number)){
            $number = [$number];
        }

        $query = [
            'contacts' => $number,
            'text' => $text,
        ];
        if (array_key_exists('is_masking',$config)){
            $query['is_masking'] = $config['is_masking'];
        }
        if (array_key_exists('masking_name',$config)){
            $query['masking_name'] = $config['masking_name'];
        }

        $headers = [
            'x-app-key' => $config['x-app-key'],
            'x-app-secret' => $config['x-app-secret'],
        ];

        $requestObject = $this->makeRequest($this->apiEndpoint, $query);
        $requestObject->setHeaders($headers)->setContentTypeJson(true);
        return $this->respond($requestObject->post(), $number);
    }

    /**
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('x-app-key', $this->senderObject->getConfig())) {
            throw new ParameterException('x-app-key is absent in configuration');
        }

        if (!array_key_exists('x-app-secret', $this->senderObject->getConfig())) {
            throw new ParameterException('x-app-secret key is absent in configuration');
        }
    }

}
