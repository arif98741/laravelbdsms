<?php
/*
 *  Last Modified: 02/02/23, 11:50 PM
 *  Copyright (c) 2023
 *  -created by Ariful Islam
 *  -All Rights Preserved By
 *  -If you have any query then knock me at
 *  arif98741@gmail.com
 *  See my profile @https://github.com/arif98741
 */

namespace Xenon\LaravelBDSms\Provider;

use GuzzleHttp\Exception\GuzzleException;
use Xenon\LaravelBDSms\Handler\RenderException;
class CustomGateway extends AbstractProvider
{

    /**
     * Send Request To Api and Send Message
     * @throws RenderException|GuzzleException
     */
    public function sendRequest()
    {
        $mobile = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();
        $query = $config;

        $requestObject = $this->makeRequest($this->senderObject->url, $query);

        $headers = $this->senderObject->getHeaders();
        if (!empty($headers)) {
            $requestObject->setHeaders($headers);
        }
        $this->senderObject->isContentTypeJson() && $requestObject->setContentTypeJson(true);

        return $this->respond(
            $this->senderObject->method === 'post' ? $requestObject->post() : $requestObject->get()
        );
    }

    /**
     * @throws RenderException
     */
    final public function errorException(): void
    {
        if (!isset($this->senderObject->url)) {
            throw new RenderException("Url missing for custom gateway. Use setUrl() to set sms gateway endpoint ");
        }
    }
}
