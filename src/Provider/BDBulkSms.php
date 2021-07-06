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


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Handler\XenonException;
use Xenon\LaravelBDSms\Sender;

class BDBulkSms extends AbstractProvider
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
     * Send Request TO Server
     * @throws GuzzleException
     */
    public function sendRequest()
    {
        /*$config = $this->senderObject->getConfig();
        $token = $config['token'];
        $number = $this->formatNumber($this->senderObject->getMobile());
        $message = $this->senderObject->getMessage();

        $url = "http://api.greenweb.com.bd/api2.php";
        //https://api.greenweb.com.bd/api.php?json
        $data = [
            'number' => $number,
            'message' => $message
        ];

        $smsParams = array(
            'to' => "$number", //accept comma separate number
            'message' => "$message",
            'token' => "$token"
        ); // Add parameters in key value
        $ch = curl_init(); // Initialize cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($smsParams));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $smsResult = curl_exec($ch);
        curl_close($ch);
        return $this->generateReport($smsResult, $data);*/

        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        $client = new Client([
            'base_uri' => 'http://api.greenweb.com.bd/api2.php',
            'timeout' => 10.0,
        ]);

        $response = $client->request('GET', '', [
            'query' => [
                'token' => $config['token'],
                'to' => $number,
                'message' => $text,
            ]
        ]);
        $body = $response->getBody();
        $smsResult = $body->getContents();

        $data['number'] = $number;
        $data['message'] = $text;
        return $this->generateReport($smsResult, $data);


    }

    /**
     * For mobile number
     * @param $mobile
     * @return string
     */
    private function formatNumber($mobile): string
    {
        if (is_array($mobile)) {
            return implode(',', $mobile);
        } else {
            return $mobile;
        }
    }

    /**
     * @param $result
     * @param $data
     * @return mixed
     */
    public function generateReport($result, $data): array
    {
        return [
            'status' => 'response',
            'response' => $result,
            'provider' => self::class,
            'send_time' => date('Y-m-d H:i:s'),
            'mobile' => $data['number'],
            'message' => $data['message']
        ];
    }

    /**
     * @return mixed
     * @throws XenonException
     * @throws RenderException
     */
    public function errorException()
    {
        if (!is_array($this->senderObject->getConfig()))
            throw new RenderException('Configuration is not provided. Use setConfig() in method chain');

        if (!array_key_exists('token', $this->senderObject->getConfig()))
            throw new RenderException('token key is absent in configuration');

        if (is_array($this->senderObject->getMobile())) {
            $errorNumbers = [];
            foreach ($this->senderObject->getMobile() as $key => $mobile) {
                if (strlen($mobile) > 11 || strlen($mobile) < 11) {
                    $errorNumbers[] = $mobile;
                }
            }

            if (count($errorNumbers) > 0) {
                $mobile = $this->formatNumber($errorNumbers);
                throw new RenderException('Invalid mobile number. It should be 11 digit. Error Numbers are ' . $mobile);
            }
        } else {

            if (preg_match("/[a-z]/i", $this->senderObject->getMobile()))
                throw new RenderException('Number should not contain alphabets');

            if (strlen($this->senderObject->getMobile()) > 11 || strlen($this->senderObject->getMobile()) < 11)
                throw new RenderException('Invalid mobile number. It should be 11 digit');
        }


        if (empty($this->senderObject->getMessage()))
            throw new RenderException('Message should not be empty');

    }
}
