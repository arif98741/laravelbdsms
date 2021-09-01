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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Sender;

class Mobireach extends AbstractProvider
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
     * @throws GuzzleException
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();



        $client = new Client([
            'base_uri' => 'https://api.mobireach.com.bd/SendTextMessage',
            'timeout' => 10.0,
        ]);

        $response = $client->request('GET', '', [
            'query' => [
                'Username' => $config['Username'],
                'Password' => $config['Password'],
                'From' => $config['From'],
                'To' => $number,
                'Message' => $text,
            ],
            'verify' => false
        ]);

        $body = $response->getBody();
        $smsResult = $body->getContents();

        $data['number'] = $number;
        $data['message'] = $text;
        $report = $this->generateReport($smsResult, $data);
        return $report->getContent();
    }

    /**
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('Username', $this->senderObject->getConfig())) {
            throw new ParameterException('Username is absent in configuration');
        }

        if (!array_key_exists('Password', $this->senderObject->getConfig())) {
            throw new ParameterException('Password is absent in configuration');
        }

        if (!array_key_exists('From', $this->senderObject->getConfig())) {
            throw new ParameterException('From is absent in configuration');
        }

    }

}
