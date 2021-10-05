<?php

namespace Xenon\LaravelBDSms\Provider;

use GuzzleHttp\Client;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Sender;

class BoomCast extends AbstractProvider
{
    /**
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    public function sendRequest()
    {
        $mobile = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        $client = new Client();
        $response = $client->get($config['url'], [
            'query' => [
                "masking" => $config['masking'],
                "userName" => $config['username'],
                "password" => $config['password'],
                "MsgType" => "TEXT",
                "receiver" => $mobile,
                "message" => urlencode($text),
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
     */
    public function errorException()
    {
        $config = $this->senderObject->getConfig();

        if (!array_key_exists('url', $config))
            throw new RenderException('url key is absent in configuration');

        if (!array_key_exists('masking', $config))
            throw new RenderException('masking key is absent in configuration');

        if (!array_key_exists('username', $config))
            throw new RenderException('username key is absent in configuration');

        if (!array_key_exists('password', $config))
            throw new RenderException('password key is absent in configuration');
    }
}
