<?php

namespace Xenon\LaravelBDSms\Provider;

use GuzzleHttp\Client;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Sender;

class ElitBuzz extends AbstractProvider
{
    /**
     * Elitbuzz Constructor
     * @param Sender $sender
     * @version v1.0.32
     * @since v1.0.31
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @version v1.0.32
     * @since v1.0.31
     */
    public function sendRequest()
    {
        $mobile = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        $client = new Client();
        $response = $client->post($config['url'] . "/smsapi", [
            'form_params' => [
                "api_key" => $config['api_key'],
                "type" => "text",
                "senderid" => $config['senderid'],
                "contacts" => $mobile,
                "msg" => urlencode($text),
            ],
            'timeout' => 60,
            'read_timeout' => 60,
            'connect_timeout' => 60
        ]);


        $response = $response->getBody()->getContents();

        $data['number'] = $mobile;
        $data['message'] = $text;
        return $this->generateReport($response, $data);
    }

    /**
     * @throws RenderException
     * @version v1.0.32
     * @since v1.0.31
     */
    public function errorException()
    {
        $config = $this->senderObject->getConfig();

        if (!array_key_exists('url', $config))
            throw new RenderException('url key is absent in configuration');

        if (!array_key_exists('api_key', $config))
            throw new RenderException('api_key key is absent in configuration');

        if (!array_key_exists('senderid', $config))
            throw new RenderException('senderid key is absent in configuration');
    }
}
