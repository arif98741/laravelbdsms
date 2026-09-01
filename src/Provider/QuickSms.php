<?php
/*
 *  Last Modified: 26/10/23, 10:40 PM
 *  Copyright (c) 2023
 *  -created by Ariful Islam
 *  -All Rights Preserved By
 *  -If you have any query then knock me at
 *  arif98741@gmail.com
 *  See my profile @ https://github.com/arif98741
 */

namespace Xenon\LaravelBDSms\Provider;

use Xenon\LaravelBDSms\Handler\RenderException;
class QuickSms extends AbstractProvider
{
    private string $apiEndpoint = 'https://server1.quicksms.xyz/smsapi';


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
            'api_key' => $config['api_key'],
            'senderid' => $config['senderid'],
            'contacts' => $mobile,
            'msg' => $text,
        ];

        if (array_key_exists('type', $config)) {
            $query ['type'] = $config['type'];
        }

        if (array_key_exists('scheduledDateTime', $config)) {
            $query ['scheduledDateTime'] = $config['scheduledDateTime'];
        }

        if (is_array($mobile)) {
            $query['contacts'] =  implode(',', $mobile);
        }

        $requestObject = $this->makeRequest($this->apiEndpoint, $query);
        $requestObject->setContentTypeJson(true);

        return $this->respond($requestObject->post());
    }

    /**
     * @throws RenderException
     */
    public function errorException(): void
    {
        if (!array_key_exists('api_key', $this->senderObject->getConfig())) {
            throw new RenderException('api_key key is absent in configuration');
        }
        if (!array_key_exists('senderid', $this->senderObject->getConfig())) {
            throw new RenderException('senderid key is absent in configuration');
        }
    }
}
