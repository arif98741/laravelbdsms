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

class Mobirech extends AbstractProvider
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


       /* $ch = curl_init();
        $username =  $config['Username'];
        $password =  $config['Password'];
        $from =  $config['From'];

        curl_setopt($ch, CURLOPT_URL,"https://api.mobireach.com.bd/SendTextMessage");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            "Username=$username&Password=$password&From=hello&To=01750840217&Message=Hello");

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        dd(curl_error($ch));
        $server_output = curl_exec($ch);
        dd($server_output);

        curl_close ($ch);*/

        $client = new Client([
            'base_uri' => 'https://api.mobireach.com.bd/SendTextMessage',
            'timeout' => 10.0,
        ]);

        $res = $client->post('', [
            'form_params' => [
                'Username' => $config['Username'],
                'Password' => $config['Password'],
                'From' => $config['From'],
                'To' => $number,
                'Message' => $text,
            ],
            'verify' => false
        ]);
        exit;

        /*$response = $client->request('GET', '', [
            'query' => [
                'Username' => $config['Username'],
                'Password' => $config['Password'],
                'From' => $config['From'],
                'To' => $number,
                'Message' => $text,
            ],
            'verify' => false
        ]);*/

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

    }

}
