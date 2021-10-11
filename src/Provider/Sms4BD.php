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

use Xenon\LaravelBDSms\Facades\Request;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Sender;

class Sms4BD extends AbstractProvider
{
    /**
     * SMS4BD constructor.
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

        /*$client = new Client([
            'base_uri' => 'http://www.sms4bd.net',
            'timeout' => 10.0,
        ]);

        try {
            $response = $client->request('GET', '', [
                'query' => [
                    'publickey' => $config['publickey'],
                    'privatekey' => $config['privatekey'],
                    'type' => $config['type'],
                    'sender' => $config['sender'],
                    'delay' => $config['delay'],
                    'receiver' => $number,
                    'message' => $text,
                ]
            ]);
        } catch (GuzzleException $e) {

            $data['number'] = $number;
            $data['message'] = $text;
            $report = $this->generateReport($e->getMessage(), $data);
            return $report->getContent();
        }
*/

        $query = [
            'publickey' => $config['publickey'],
            'privatekey' => $config['privatekey'],
            'type' => $config['type'],
            'sender' => $config['sender'],
            'delay' => $config['delay'],
            'receiver' => $number,
            'message' => $text,
        ];

        $response = Request::get('http://www.sms4bd.net', $query, false);

        $body = $response->getBody();
        $smsResult = $body->getContents();
        $data['number'] = $number;
        $data['message'] = $text;
        $report = $this->generateReport($smsResult, $data);
        return $report->getContent();
    }

    /**
     * @throws RenderException
     */
    public function errorException()
    {
        if (!array_key_exists('publickey', $this->senderObject->getConfig())) {
            throw new RenderException('publickey is absent in configuration');
        }
        if (!array_key_exists('privatekey', $this->senderObject->getConfig())) {
            throw new RenderException('privatekey is absent in configuration');
        }
        if (!array_key_exists('type', $this->senderObject->getConfig())) {
            throw new RenderException('type key is absent in configuration');
        }
        if (!array_key_exists('sender', $this->senderObject->getConfig())) {
            throw new RenderException('sender key is absent in configuration');
        }
        if (!array_key_exists('delay', $this->senderObject->getConfig())) {
            throw new RenderException('delay key is absent in configuration');
        }

    }
}
