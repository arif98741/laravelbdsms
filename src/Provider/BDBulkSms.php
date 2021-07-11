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
use Illuminate\Http\JsonResponse;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Sender;

class BDBulkSms extends AbstractProvider
{
    /**
     * BDBulkSms constructor.
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
     * @return JsonResponse
     */
    public function generateReport($result, $data): JsonResponse
    {
        return response()->json([
            'status' => 'response',
            'response' => $result,
            'provider' => self::class,
            'send_time' => date('Y-m-d H:i:s'),
            'mobile' => $data['number'],
            'message' => $data['message']
        ]);
    }

    /**
     * @return void
     * @throws RenderException
     */
    public function errorException()
    {
        if (!array_key_exists('token', $this->senderObject->getConfig()))
            throw new RenderException('token key is absent in configuration');
    }
}
