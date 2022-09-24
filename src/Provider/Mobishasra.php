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
use Xenon\LaravelBDSms\Facades\Request;
use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Sender;

class Mobishasra extends AbstractProvider
{
    /**
     * BulkSmsBD constructor.
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

        /*$client = new Client([
            'base_uri' => 'https://mshastra.com/sendurlcomma.aspx',
            'timeout' => 10.0,
        ]);

        $response = $client->request('GET', '', [
            'query' => [
                'user' => $config['user'],
                'pwd' => $config['pwd'],
                'senderid' => $config['senderid'],
                'mobileno' => '88' . $number,
                'msgtext' => $text,
                'priority' => 'High',
                'CountryCode' => 'ALL',
            ],
            'verify' => false
        ]);*/

        $query  = [
            'user' => $config['user'],
            'pwd' => $config['pwd'],
            'senderid' => $config['senderid'],
            'mobileno' => '88' . $number,
            'msgtext' => $text,
            'priority' => 'High',
            'CountryCode' => 'ALL',
        ];
        $response = Request::get('https://mshastra.com/sendurlcomma.aspx', $query);
        $body = $response->getBody();

        $smsResult = $body->getContents();


        $data['number'] = $number;
        $data['message'] = $text;
        return $this->generateReport($smsResult, $data)->getContent();
    }

    /**
     * @return void
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('user', $this->senderObject->getConfig())) {
            throw new ParameterException('user key is absent in configuration');
        }
        if (!array_key_exists('pwd', $this->senderObject->getConfig())) {
            throw new ParameterException('pwd key is absent in configuration');
        }
        if (!array_key_exists('senderid', $this->senderObject->getConfig())) {
            throw new ParameterException('senderid key is absent in configuration');
        }
    }
}
