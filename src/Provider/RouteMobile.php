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
use Xenon\LaravelBDSms\Request;
use Xenon\LaravelBDSms\Sender;

class RouteMobile extends AbstractProvider
{
    /**
     * DianaHost constructor.
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * Send Request To Api and Send Message
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();
        $queue = $this->senderObject->getQueue();

        $query = [
            'username' => $config['username'],
            'password' => $config['password'],
            'source' => $config['source'],
            'destination' => $number,
            'message' => $text,
        ];


        $requestObject = new Request('https://api.rmlconnect.net/bulksms/bulksms', $query, $queue);
        $response = $requestObject->get();
        if ($queue) {
            return true;
        }

        $body = $response->getBody();
        $smsResult = $body->getContents();

        $data['number'] = $number;
        $data['message'] = $text;
        return $this->generateReport($smsResult, $data)->getContent();
    }

    /**
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('username', $this->senderObject->getConfig())) {
            throw new ParameterException('username is absent in configuration');
        }
        if (!array_key_exists('password', $this->senderObject->getConfig())) {
            throw new ParameterException('password is absent in configuration');
        }
        if (!array_key_exists('source', $this->senderObject->getConfig())) {
            throw new ParameterException('source is absent in configuration');
        }
    }

}
