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
use Illuminate\Http\JsonResponse;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Sender;

class Adn extends AbstractProvider
{
    /**
     * Adn constructor.
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

        $client = new Client([
            'timeout' => 10.0,
            'verify' => false
        ]);

        try {
            $response = $client->request('POST', 'https://portal.adnsms.com',
                [
                    'form_params' => [
                        'api_key' => $config['api_key'],
                        'type' => $config['type'],
                        'senderid' => $config['senderid'],
                        'mobile' => $number,
                        'message_body' => $text,
                    ],
                    'headers' => [
                        'Accept' => 'application/json'
                    ],
                    'debug' => false
                ]);
        } catch (GuzzleException $e) {
            return $e->getMessage();
        }

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

        if (!array_key_exists('api_key', $this->senderObject->getConfig())) {
            throw new RenderException('api_key is absent in configuration');
        }
        if (!array_key_exists('api_secret', $this->senderObject->getConfig())) {
            throw new RenderException('api_secret key is absent in configuration');
        }
        if (!array_key_exists('request_type', $this->senderObject->getConfig())) {
            throw new RenderException('request_type key is absent in configuration');
        }
        if (!array_key_exists('message_type', $this->senderObject->getConfig())) {
            throw new RenderException('message_type key is absent in configuration');
        }


        if (strlen($this->senderObject->getMobile()) > 11 || strlen($this->senderObject->getMobile()) < 11) {
            throw new RenderException('Invalid mobile number. It should be 11 digit');
        }
        if (empty($this->senderObject->getMessage())) {
            throw new RenderException('Message should not be empty');
        }
    }

    /**
     * @param $result
     * @param $data
     * @return JsonResponse
     */
    public function generateReport($result, $data)
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
}
