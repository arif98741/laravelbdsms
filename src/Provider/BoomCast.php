<?php

namespace Xenon\LaravelBDSms\Provider;

use GuzzleHttp\Exception\GuzzleException;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Request;
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
     * @return false|string
     * @throws GuzzleException
     * @throws RenderException
     * @version v1.0.37
     * @since v1.0.31
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();
        $queue = $this->senderObject->getQueue();

        $query = [
            "masking" => $config['masking'],
            "userName" => $config['username'],
            "password" => $config['password'],
            "MsgType" => "TEXT",
            "receiver" => $number,
            "message" => $text,
        ];

        $requestObject = new Request('https://api.boom-cast.com/boomcast/WebFramework/boomCastWebService/OTPMessage.php', $query, $queue);

        $response = $requestObject->get();
        if ($queue)
            return true;

        $body = $response->getBody();
        $smsResult = $body->getContents();

        $data['number'] = $number;
        $data['message'] = $text;
        return $this->generateReport($smsResult, $data)->getContent();
    }

    /**
     * @throws RenderException
     * @version v1.0.32
     * @since v1.0.31
     */
    public function errorException()
    {
        $config = $this->senderObject->getConfig();

        if (!array_key_exists('masking', $config)) {
            throw new RenderException('masking key is absent in configuration');
        }

        if (!array_key_exists('username', $config)) {
            throw new RenderException('username key is absent in configuration');
        }

        if (!array_key_exists('password', $config)) {
            throw new RenderException('password key is absent in configuration');
        }
    }
}
