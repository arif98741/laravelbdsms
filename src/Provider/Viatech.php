<?php

namespace Xenon\LaravelBDSms\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Sender;

class BoomCast extends AbstractProvider
{
    /**
     * BoomCast Constructor
     * @param Sender $sender
     * @version v1.0.32
     * @since v1.0.31
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * @return JsonResponse
     * @throws GuzzleException
     * @throws RenderException
     * @version v1.0.37
     * @since v1.0.31
     */
    public function sendRequest()
    {
        $mobile = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();


        $client = new Client([
            'base_uri' => 'https://api.boom-cast.com/boomcast/WebFramework/boomCastWebService/OTPMessage.php',
            'timeout' => 10.0,
            'verify' => false,
        ]);

        try {
            $response = $client->request('GET', '', [
                'query' => [
                    "masking" => $config['masking'],
                    "userName" => $config['username'],
                    "password" => $config['password'],
                    "MsgType" => "TEXT",
                    "receiver" => $mobile,
                    "message" => $text,
                ],
                'timeout' => 60,
                'read_timeout' => 60,
                'connect_timeout' => 60
            ]);
        } catch (ClientException|GuzzleException $e) {
            throw new RenderException($e->getMessage());
        }


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


        if (!array_key_exists('masking', $config))
            throw new RenderException('masking key is absent in configuration');

        if (!array_key_exists('username', $config))
            throw new RenderException('username key is absent in configuration');

        if (!array_key_exists('password', $config))
            throw new RenderException('password key is absent in configuration');
    }
}
